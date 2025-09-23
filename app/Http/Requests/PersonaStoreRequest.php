<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'apellidos' => ['nullable', 'string', 'max:255'],
            'nombres' => ['required', 'string', 'max:255'],
            'cuil' => ['required', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'pago' => ['nullable', 'numeric'],
            'cbu_alias' => ['nullable', 'string', 'max:255'],
            'unidad_id' => ['nullable', 'integer', 'exists:unidades,id'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'sucursal_id' => ['nullable', 'integer', 'exists:sucursals,id'],
            'agente_id' => ['nullable', 'integer', 'exists:users,id'],
            'agente' => ['nullable', 'integer', 'exists:users,id'],
            'estado_id' => ['nullable', 'integer', 'exists:estados,id'],
            // Alias opcional para facilitar desde frontend
            'estado' => ['nullable', 'integer', 'exists:estados,id'],
            'tipo' => ['nullable', 'integer'],
            'combustible' => ['sometimes', 'boolean'],
            'observaciontarifa' => ['nullable', 'string'],
            'tarifaespecial' => ['nullable', 'integer', 'in:0,1'],
            'observaciones' => ['nullable', 'string'],
            'fecha_alta' => ['nullable', 'date'],

            'dueno' => ['nullable', 'array'],
            'dueno.fecha_nacimiento' => ['nullable', 'date'],
            'dueno.cuil' => ['nullable', 'string', 'max:50'],
            'dueno.cuil_cobrador' => ['nullable', 'string', 'max:50'],
            'dueno.cbu_alias' => ['nullable', 'string', 'max:255'],
            'dueno.email' => ['nullable', 'email', 'max:255'],
            'dueno.telefono' => ['nullable', 'string', 'max:50'],
            'dueno.observaciones' => ['nullable', 'string'],

            'transporte_temporal' => ['nullable', 'array'],
            'transporte_temporal.guia_remito' => ['nullable', 'string', 'max:255'],
            'transporte_temporal.valor_viaje' => ['nullable', 'numeric'],
            'transporte_temporal.origen' => ['nullable', 'string', 'max:255'],
            'transporte_temporal.destino' => ['nullable', 'string', 'max:255'],
            'transporte_temporal.estado_servicio_id' => ['nullable', 'integer'],
        ];
    }
}
