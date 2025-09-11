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
        // Carpeta física directamente en public: public/documentos/{persona_id}/{tipo_archivo_id}
        $relativeDir = 'documentos/' . $personaId . '/' . $tipoId;
        $publicBase = public_path($relativeDir);
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

        // Asegurar carpeta física
        if (!is_dir($publicBase)) {
            mkdir($publicBase, 0775, true);
        }
        $filename = uniqid('f_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($publicBase, $filename);
        // Ruta relativa servible por el servidor web
        $rutaRelativa = $relativeDir . '/' . $filename; // documentos/.../file.ext
        $stored = Archivo::create([
            'persona_id' => $personaId,
            'tipo_archivo_id' => $tipoId,
            'carpeta' => $relativeDir,
            'ruta' => $rutaRelativa,
            'disk' => 'public_direct', // marcador custom
            'nombre_original' => $original,
            'mime' => mime_content_type(public_path($rutaRelativa)) ?: $file->getClientMimeType(),
            'size' => filesize(public_path($rutaRelativa)),
            'fecha_vencimiento' => $fechaVencimiento,
        ]);

        return response()->json(['success' => true, 'code' => 201, 'data' => new ArchivoResource($stored)], 201);
    }

    public function download(ArchivoDownloadRequest $request)
    {
        $ruta = $request->input('ruta');
        $archivo = Archivo::where('ruta', $ruta)->first();
        $fullPath = public_path($ruta);
        if (!$archivo && !is_file($fullPath)) {
            return response()->json([
                'status' => 404,
                'code' => 'FILE_NOT_FOUND',
                'message' => 'Archivo no encontrado.',
            ], 404);
        }
        $url = url($ruta); // url() apunta a public/
        return response()->json(['success' => true, 'code' => 200, 'data' => [
            'ruta' => $ruta,
            'url' => $url,
            'id' => $archivo?->id,
        ]], 200);
    }

    /**
     * Descargar por ID de archivo (alternativo cuando se tiene el ID y no la ruta directa)
     */
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

        $ruta = $archivo->ruta;
        $fullPath = public_path($ruta);
        $exists = is_file($fullPath);
        $publicUrl = url($ruta);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'id' => $archivo->id,
                'ruta' => $ruta,
                'url' => $publicUrl,
                'existe_fisicamente' => $exists,
            ],
        ], 200);
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
