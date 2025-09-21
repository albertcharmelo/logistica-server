<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchivoDownloadRequest;
use App\Http\Requests\ArchivoUploadRequest;
use App\Http\Resources\ArchivoResource;
use App\Models\Archivo;
use App\Models\FyleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function upload(ArchivoUploadRequest $request)
    {


        $file = $request->file('archivo') ?? $request->file('file');

        $personaId = (int) $request->input('origen_id');
        $tipoId = (int) $request->input('tipo_archivo_id');
        $supabaseUrl = rtrim(env('SUPABASE_URL', 'https://notbvdymlxpwrzecgptp.supabase.co'), '/');
        $serviceKey = env('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im5vdGJ2ZHltbHhwd3J6ZWNncHRwIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NzYyNzk0NCwiZXhwIjoyMDczMjAzOTQ0fQ.MOYCWklLXtrie8sAWULx1Y-ovfTn4KCtoNp4gZtz_5Y');
        $bucket = env('SUPABASE_BUCKET', 'archivos');
        if (!$supabaseUrl || !$serviceKey) {
            return response()->json([
                'status' => 500,
                'code' => 'SUPABASE_CONFIG_INCOMPLETE',
                'message' => 'Faltan SUPABASE_URL o SUPABASE_SERVICE_KEY en .env',
            ], 500);
        }
        $filename = uniqid('f_', true) . '.' . $file->getClientOriginalExtension();
        $path = "public/documentos/{$filename}";


        $uploadEndpoint = $supabaseUrl . "/storage/v1/object/{$bucket}/{$path}";
        $downloadUrl = $supabaseUrl . "/storage/v1/object/public/{$bucket}/{$path}";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $serviceKey,
            'Content-Type' => $file->getMimeType() ?: 'application/octet-stream',
            'Cache-Control' => 'max-age=31536000, immutable',
        ])->send('POST', $uploadEndpoint, [
            'body' => $file->get(),
        ]);




        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'code' => $response->status(),
                'message' => 'Error al subir el archivo a Supabase.',
                'error_details' => $response->json(),
            ], $response->status());
        }
        $stored = Archivo::create([
            'persona_id' => $personaId,
            'tipo_archivo_id' => $tipoId,
            'carpeta' => $request->input('carpeta', 'documentos'),
            'ruta' => $path,
            'download_url' => $downloadUrl,
            'disk' => 'supabase',
            'nombre_original' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
        return response()->json([
            'success' => true,
            'code' => 201,
            'data' => [
                'archivo' => new ArchivoResource($stored),
                'url' => $downloadUrl,
            ],
        ], 201);
    }

    public function download(ArchivoDownloadRequest $request)
    {
        $ruta = $request->input('ruta');
        $archivo = Archivo::where('ruta', $ruta)->first();
        if (!$archivo) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado.',
            ], 404);
        }
        $downloadUrl = $archivo->download_url ?? $this->inferPublicUrlFromPath($archivo->ruta);
        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'id' => $archivo->id,
                'ruta' => $archivo->ruta,
                'url' => $downloadUrl,
            ],
        ], 200);
    }

    public function downloadById(int $id)
    {
        $archivo = Archivo::find($id);
        if (!$archivo) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado.',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'id' => $archivo->id,
                'ruta' => $archivo->ruta,
                'url' => $archivo->download_url ?? $this->inferPublicUrlFromPath($archivo->ruta),
                'existe_fisicamente' => null,
            ],
        ], 200);
    }

    public function destroy(int $id)
    {
        $archivo = Archivo::findOrFail($id);
        try {
            $this->supabaseDelete($archivo->ruta);
        } catch (\Throwable $e) {
            Log::warning('Supabase delete failed', ['e' => $e->getMessage()]);
        }
        $archivo->delete();

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => null,
        ], 200);
    }

    protected function inferPublicUrlFromPath(string $path): string
    {
        $supabaseUrl = rtrim(env('SUPABASE_URL', ''), '/');
        $bucket = env('SUPABASE_BUCKET', 'archivos');
        if ($supabaseUrl) {
            return $supabaseUrl . '/storage/v1/object/public/' . $bucket . '/' . ltrim($path, '/');
        }
        return $this->buildS3PublicUrl($path);
    }

    protected function supabaseDelete(string $path): void
    {
        $supabaseUrl = rtrim(env('SUPABASE_URL', ''), '/');
        $serviceKey = env('SUPABASE_SERVICE_KEY');
        $bucket = env('SUPABASE_BUCKET', 'archivos');
        if (!$supabaseUrl || !$serviceKey) {
            return;
        }
        $endpoint = $supabaseUrl . '/storage/v1/object/' . $bucket . '/' . ltrim($path, '/');
        Http::withHeaders(['Authorization' => 'Bearer ' . $serviceKey])->delete($endpoint);
    }

    /**
     * Construye URL pública para objeto S3 (incluye compatibilidad con Supabase storage si AWS_URL no definido).
     */
    protected function buildS3PublicUrl(string $path): string
    {
        $diskConfig = config('filesystems.disks.s3');
        $base = rtrim($diskConfig['url'] ?? '', '/');
        if ($base) {
            return $base . '/' . ltrim($path, '/');
        }
        // Fallback: https://{bucket}.s3.{region}.amazonaws.com/{path}
        $bucket = $diskConfig['bucket'] ?? '';
        $region = $diskConfig['region'] ?? 'us-east-1';
        if ($bucket) {
            return "https://{$bucket}.s3.{$region}.amazonaws.com/" . ltrim($path, '/');
        }
        return $path; // última opción (dev)
    }

    public function byPersona(Request $request, int $id)
    {
        $perPage = (int) $request->query('per_page', 0);
        $query = Archivo::where('persona_id', $id)->orderByDesc('id');

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => [
                    'archivos' => ArchivoResource::collection($paginator->items()),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ]);
        }

        $items = $query->get();
        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => ArchivoResource::collection($items),
        ]);
    }
}
