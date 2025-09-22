<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Requests\ClienteUpdateRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Persona;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 0);

        $query = Cliente::with('sucursales')->orderBy('id', 'desc');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%$search%")
                    ->orWhere('nombre', 'like', "%$search%")
                    ->orWhere('direccion', 'like', "%$search%")
                    ->orWhere('documento_fiscal', 'like', "%$search%");
            });
        }

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => [
                    'clientes' => ClienteResource::collection($paginator->items()),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ], 200);
        }

        $clientes = $query->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => ClienteResource::collection($clientes)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClienteStoreRequest $request)
    {
        $data = $request->validated();
        $sucursales = $data['sucursales'] ?? [];
        unset($data['sucursales']);

        $cliente = Cliente::create($data);

        foreach ($sucursales as $s) {
            $sucursal = new Sucursal($s);
            $cliente->sucursales()->save($sucursal);
        }

        $cliente->load('sucursales');

        return response()->json(['success' => true, 'code' => 201, 'data' => new ClienteResource($cliente)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::with('sucursales')->findOrFail($id);

        return response()->json(['success' => true, 'code' => 200, 'data' => new ClienteResource($cliente)], 200);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(ClienteUpdateRequest $request, Cliente $cliente)
    {
        $data = $request->validated();
        $items = $data['sucursales'] ?? [];
        unset($data['sucursales']);

        return DB::transaction(function () use ($cliente, $data, $items) {
            $cliente->update($data);

            $keepIds = [];

            // 1) normalizar y deduplicar en el propio payload (por nombre+dirección)
            $normPayload = collect($items)->map(function ($s) {
                $nombre    = trim((string)($s['nombre'] ?? ''));
                $direccion = trim((string)($s['direccion'] ?? ''));

                $nombreNorm    = Str::of($nombre)->lower()->squish()->value();
                $direccionNorm = Str::of($direccion)->lower()->squish()->value();

                return [
                    'id'             => $s['id'] ?? null,
                    'nombre'         => $nombre,
                    'direccion'      => $direccion,
                    // solo para matching en memoria
                    '_nombre_norm'    => $nombreNorm,
                    '_direccion_norm' => $direccionNorm,
                ];
            })->unique(fn($s) => $s['_nombre_norm'] . '|' . $s['_direccion_norm']);

            foreach ($normPayload as $s) {
                // Si viene id, validamos pertenencia y actualizamos por id (no crea)
                if (!empty($s['id'])) {
                    $suc = $cliente->sucursales()->whereKey($s['id'])->first();
                    if (!$suc) {
                        // id ajeno → 422 explícito
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'sucursales' => ["La sucursal {$s['id']} no pertenece al cliente {$cliente->id}."]
                        ]);
                    }
                    $suc->fill(Arr::only($s, ['nombre', 'direccion']))->save();
                    $keepIds[] = $suc->id;
                    continue;
                }

                // 2) Sin id → match por cliente + nombre + direccion normalizados
                //    Hacemos un “find” previo para evitar crear si ya existe en BD.
                $yaExiste = $cliente->sucursales()
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [$s['_nombre_norm']])
                    ->whereRaw('LOWER(TRIM(direccion)) = ?', [$s['_direccion_norm']])
                    ->first();

                if ($yaExiste) {
                    // ya existe: si hay algo distinto, actualizamos; si no, lo dejamos igual
                    $yaExiste->fill(Arr::only($s, ['nombre', 'direccion']))->save();
                    $keepIds[] = $yaExiste->id;
                } else {
                    $created = $cliente->sucursales()->create(Arr::only($s, ['nombre', 'direccion']));
                    $keepIds[] = $created->id;
                }
            }

            // 3) Si tu operación es estilo PUT (reemplazo), borra las que no vinieron
            if (!empty($items)) {
                $cliente->sucursales()
                    ->whereNotIn('id', $keepIds)
                    ->doesntHave('personas')   // evita romper referencias
                    ->delete();
            }

            return ClienteResource::make($cliente->fresh('sucursales'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->delete();

        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }
}
