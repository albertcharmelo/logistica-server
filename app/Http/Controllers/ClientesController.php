<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Http\Requests\ClienteStoreRequest;
use App\Http\Resources\ClienteResource;

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
    public function update(ClienteStoreRequest $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $data = $request->validated();
        $sucursales = $data['sucursales'] ?? null;
        unset($data['sucursales']);

        $cliente->update($data);

        if (is_array($sucursales)) {
            // For simplicity: delete existing and recreate from payload
            $cliente->sucursales()->delete();
            foreach ($sucursales as $s) {
                $cliente->sucursales()->create($s);
            }
        }

        $cliente->load('sucursales');

        return response()->json(['success' => true, 'code' => 200, 'data' => new ClienteResource($cliente)], 200);
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
