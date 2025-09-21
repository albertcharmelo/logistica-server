<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReclamoArchivoUploadRequest;
use App\Models\Archivo;
use App\Models\Reclamo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReclamoArchivoController extends Controller
{
    protected function diskName(): string
    {
        return config('filesystems.default', 'public'); // 'supabase' en Cloud si así lo definiste
    }

    public function upload(ReclamoArchivoUploadRequest $request)
    {
        $reclamo = Reclamo::with('transportista')->findOrFail($request->reclamo_id);

        $file      = $request->file('archivo');
        $disk      = $this->diskName();
        $dir       = "reclamo/{$reclamo->id}";
        $ext       = $file->getClientOriginalExtension() ?: 'bin';
        $filename  = Str::uuid()->toString() . '.' . $ext;

        // Guardar en el disco activo
        $path = Storage::disk($disk)->putFileAs($dir, $file, $filename, [
            'visibility'   => 'public',
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        // Generar URL pública o firmada (según tu driver)
        $publicUrl = null;
        try {
            $publicUrl = Storage::disk($disk)->url($path);
        } catch (\Throwable $e) {
            // Si el disco no soporta url(), puedes setear null aquí o manejar temporaryUrl
            if (method_exists(Storage::disk($disk), 'temporaryUrl')) {
                $publicUrl = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(10));
            }
        }

        // Crear registro en 'archivos' (usa persona del reclamo)
        $stored = Archivo::create([
            'persona_id'       => $reclamo->persona_id,                 // requerido por tu tabla
            'tipo_archivo_id'  => (int) $request->tipo_archivo_id,      // requerido por tu tabla
            'carpeta'          => "reclamo",                            // carpeta base lógica
            'ruta'             => $path,                                 // reclamo/{id}/file.ext
            'download_url'     => $publicUrl,                            // si tu driver soporta
            'disk'             => $disk,                                 // 'supabase' o 'public'
            'nombre_original'  => $request->input('nombre_original') ?: $file->getClientOriginalName(),
            'mime'             => $file->getClientMimeType(),
            'size'             => $file->getSize(),
            // 'fecha_vencimiento' => null,  // no aplica para reclamo
        ]);

        // Asociar al reclamo (pivot)
        $reclamo->archivos()->syncWithoutDetaching([$stored->id]);

        return response()->json([
            'success' => true,
            'code'    => 201,
            'data'    => [
                'archivo' => $stored,
                'url'     => $publicUrl,
                'reclamo' => $reclamo->only(['id', 'persona_id', 'reclamo_type_id', 'status']),
            ],
        ], 201);
    }
}
