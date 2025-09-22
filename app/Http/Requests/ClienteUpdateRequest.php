<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ClienteUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajusta a tu policy si la usas:
        // return $this->user()->can('update', $this->route('cliente'));
        return true;
    }

    public function rules(): array
    {
        return [
            // ---- Campos del cliente (ajusta a tu dominio) ----
            'codigo'            => ['nullable', 'string', 'max:64'],
            'nombre'            => ['required', 'string', 'max:255'],
            'direccion'         => ['nullable', 'string', 'max:500'],
            'documento_fiscal'  => ['nullable', 'string', 'max:191'],

            // ---- Sucursales (si se envía, se valida) ----
            'sucursales'                => ['sometimes', 'array'],
            'sucursales.*.id'           => ['nullable', 'integer', 'min:1'],
            'sucursales.*.nombre'       => ['required_without:sucursales.*.id', 'string', 'max:255'],
            'sucursales.*.direccion'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'sucursales.*.nombre.required_without' =>
            'Cada sucursal debe tener nombre cuando no se envía su id.',
        ];
    }

    /**
     * Normaliza entradas antes de validar:
     * - Trim de strings
     * - Colapsa espacios y baja a minúsculas para fingerprint interno
     * - Elimina sucursales totalmente vacías
     */
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        // Normaliza strings del cliente
        foreach (['codigo', 'nombre', 'direccion', 'documento_fiscal'] as $k) {
            if (isset($input[$k]) && is_string($input[$k])) {
                $input[$k] = trim($input[$k]);
            }
        }

        // Normaliza sucursales
        if (isset($input['sucursales']) && is_array($input['sucursales'])) {
            $input['sucursales'] = collect($input['sucursales'])
                ->filter(function ($row) {
                    // descarta filas completamente vacías
                    if (!is_array($row)) return false;
                    $nombre    = trim((string)($row['nombre'] ?? ''));
                    $direccion = trim((string)($row['direccion'] ?? ''));
                    $id        = $row['id'] ?? null;
                    return $id !== null || $nombre !== '' || $direccion !== '';
                })
                ->map(function ($row) {
                    $row['id']        = $row['id'] ?? null;
                    $row['nombre']    = isset($row['nombre']) ? trim((string)$row['nombre']) : null;
                    $row['direccion'] = isset($row['direccion']) ? trim((string)$row['direccion']) : null;

                    // Fingerprint interno (no se persiste; sólo para validar duplicados)
                    $nombreNorm    = Str::of((string)($row['nombre'] ?? ''))->lower()->squish()->value();
                    $direccionNorm = Str::of((string)($row['direccion'] ?? ''))->lower()->squish()->value();
                    $row['_fp']    = $nombreNorm . '|' . $direccionNorm;

                    return $row;
                })
                ->values()
                ->all();
        }

        $this->replace($input);
    }

    /**
     * Validaciones adicionales:
     * - Detecta duplicados de sucursales en el mismo payload por fingerprint (nombre|direccion normalizados)
     * - Exige que, si no hay id, haya al menos nombre (ya cubierto por rule) y permite direccion vacía
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $rows = $this->input('sucursales', []);
            if (!is_array($rows) || empty($rows)) {
                return;
            }

            $seen = [];
            foreach ($rows as $i => $row) {
                $fp = $row['_fp'] ?? null;
                if ($fp && isset($seen[$fp])) {
                    $v->errors()->add("sucursales.$i.nombre", "Sucursal duplicada en el payload (nombre/dirección repetidos).");
                } else {
                    $seen[$fp] = true;
                }

                // Si no viene id, al menos nombre debe venir no vacío (regla ya lo exige).
                if (empty($row['id'])) {
                    $nombre = trim((string)($row['nombre'] ?? ''));
                    if ($nombre === '') {
                        $v->errors()->add("sucursales.$i.nombre", "Falta el nombre de la sucursal (o envía un id válido).");
                    }
                }
            }
        });
    }
}
