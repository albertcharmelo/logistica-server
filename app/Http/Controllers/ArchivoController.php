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
        $personaId = (int) $request->input('origen_id');
        $tipoId = (int) $request->input('tipo_archivo_id');

        $relativeDir = "documentos/{$personaId}/{$tipoId}";
        $filename = uniqid('f_', true) . '.' . $file->getClientOriginalExtension();

        // Guarda en disco "public" (storage/app/public)
        $path = $file->storeAs($relativeDir, $filename, 'public');

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
            $fechaVencimiento = null;
        }

        $stored = Archivo::create([
            'persona_id'        => $personaId,
            'tipo_archivo_id'   => $tipoId,
            'carpeta'           => $relativeDir,
            'ruta'              => $path, // documentos/.../archivo.ext
            'disk'              => 'public',
            'nombre_original'   => $original,
            'mime'              => $file->getClientMimeType(),
            'size'              => $file->getSize(),
            'fecha_vencimiento' => $fechaVencimiento,
        ]);

        return response()->json([
            'success' => true,
            'code'    => 201,
            'data'    => [
                'archivo' => new ArchivoResource($stored),
                'url'     => Storage::url($path), // /storage/documentos/...
            ],
        ], 201);
    }

    public function download(ArchivoDownloadRequest $request)
    {
        $ruta = $request->input('ruta');
        $archivo = Archivo::where('ruta', $ruta)->first();
        $disk = $archivo?->disk ?? 'public';

        if (!$archivo || !Storage::disk($disk)->exists($ruta)) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado.',
            ], 404);
        }

        // // Opción 1: Devolver URL pública (recomendado)
        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'id'  => $archivo->id,
                'ruta' => $ruta,
                'url' => Storage::url($ruta),
            ],
        ], 200);

        // Opción 2: Forzar descarga con headers correctos
        // $filesystem = Storage::disk($disk);
        // $fullPath = method_exists($filesystem, 'path')
        //     ? $filesystem->path($ruta)
        //     : rtrim(config("filesystems.disks.$disk.root"), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $ruta;

        // return response()->download($fullPath, $archivo->nombre_original, [
        //     'Content-Type' => $archivo->mime,
        // ]);
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

        $disk = $archivo->disk ?? 'public';
        if (!Storage::disk($disk)->exists($archivo->ruta)) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado en disco.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'id' => $archivo->id,
                'ruta' => $archivo->ruta,
                'url' => Storage::url($archivo->ruta),
                'existe_fisicamente' => true,
            ],
        ], 200);
    }

    public function destroy(int $id)
    {
        $archivo = Archivo::findOrFail($id);
        Storage::disk($archivo->disk ?? 'public')->delete($archivo->ruta);
        $archivo->delete();

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
