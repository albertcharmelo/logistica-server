<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchivoDownloadRequest;
use App\Http\Requests\ArchivoUploadRequest;
use App\Http\Resources\ArchivoResource;
use App\Models\Archivo;
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
}
