<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidad;
use App\Http\Requests\UnidadStoreRequest;
use App\Http\Resources\UnidadResource;

class UnidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 0);

        $query = Unidad::query()->orderBy('id', 'desc');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('matricula', 'like', "%$search%")
                    ->orWhere('marca', 'like', "%$search%")
                    ->orWhere('modelo', 'like', "%$search%")
                    ->orWhere('anio', 'like', "%$search%")
                    ->orWhere('observacion', 'like', "%$search%");
            });
        }

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            $items = collect($paginator->items());
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => UnidadResource::collection($items),
            ], 200);
        }

        $unidades = $query->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => UnidadResource::collection($unidades)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = (new UnidadStoreRequest())->merge(
            $request->all()
        )->validated();

        $unidad = Unidad::create($data);

        return response()->json([
            'success' => true,
            'code' => 201,
            'data' => new UnidadResource($unidad),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unidad = Unidad::findOrFail($id);

        return response()->json(['success' => true, 'code' => 200, 'data' => new UnidadResource($unidad)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unidad = Unidad::findOrFail($id);

        $data = (new UnidadStoreRequest())->merge($request->all())->validated();

        $unidad->update($data);

        return response()->json(['success' => true, 'code' => 200, 'data' => new UnidadResource($unidad)], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unidad = Unidad::findOrFail($id);

        $unidad->delete();

        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }
}
