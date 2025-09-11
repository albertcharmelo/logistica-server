<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchivoDownloadRequest;
use App\Http\Requests\ArchivoUploadRequest;
use App\Http\Resources\ArchivoResource;
use App\Models\Archivo;
use App\Models\FyleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function upload(ArchivoUploadRequest $request)
    {
        $file = $request->file('archivo');
        $disk = 'public';
        $carpeta = trim($request->input('carpeta'), '/');
        $personaId = (int) $request->input('origen_id');
        $tipoId = (int) $request->input('tipo_archivo_id');
        $original = $request->input('nombre_original') ?: $file->getClientOriginalName();
        $fechaVencimiento = $request->input('fecha_vencimiento');

        $tipo = FyleType::find($tipoId);
        if ($tipo && $tipo->vence) {
            if (empty($fechaVencimiento)) {
                return response()->json([
                    'status' => 422,
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'El campo fecha_vencimiento es obligatorio para este tipo de archivo.',
                    'errors' => [
                        'fecha_vencimiento' => ['El campo fecha_vencimiento es obligatorio cuando el tipo de archivo vence.']
                    ]
                ], 422);
            }
        } else {
            // No vence: ignorar cualquier valor enviado nulo
            if (!$tipo || !$tipo->vence) {
                $fechaVencimiento = null;
            }
        }

        // Build a namespaced folder e.g. documentos/identificaciones
        $path = $file->store($carpeta, $disk);
        $stored = Archivo::create([
            'persona_id' => $personaId,
            'tipo_archivo_id' => $tipoId,
            'carpeta' => $carpeta,
            'ruta' => $path,
            'disk' => $disk,
            'nombre_original' => $original,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'fecha_vencimiento' => $fechaVencimiento,
        ]);

        return response()->json(['success' => true, 'code' => 201, 'data' => new ArchivoResource($stored)], 201);
    }

    public function download(ArchivoDownloadRequest $request)
    {
        $ruta = $request->input('ruta');
        $disk = 'public';
        if (!Storage::disk($disk)->exists($ruta)) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        // Build public URL for local/public disk
        $base = rtrim(config('filesystems.disks.public.url', asset('storage')), '/');
        $publicUrl = $base . '/' . ltrim($ruta, '/');

        return response()->json(['success' => true, 'code' => 200, 'data' => [
            'ruta' => $ruta,
            'url' => $publicUrl,
        ]], 200);
    }

    public function destroy(int $id)
    {
        $archivo = Archivo::findOrFail($id);
        $archivo->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => null,
        ], 200);
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
