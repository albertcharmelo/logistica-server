<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Http\Requests\SucursalStoreRequest;
use App\Http\Resources\SucursalResource;

class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sucursals = Sucursal::orderBy('id', 'desc')->get();

        return response()->json(['success' => true, 'code' => 200, 'data' => SucursalResource::collection($sucursals)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = (new SucursalStoreRequest())->merge($request->all())->validated();

        $sucursal = Sucursal::create($data);

        return response()->json(['success' => true, 'code' => 201, 'data' => new SucursalResource($sucursal)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'code' => 200, 'data' => new SucursalResource($sucursal)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        $data = (new SucursalStoreRequest())->merge($request->all())->validated();
        $sucursal->update($data);

        return response()->json(['success' => true, 'code' => 200, 'data' => new SucursalResource($sucursal)], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        $sucursal->delete();

        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }
}
