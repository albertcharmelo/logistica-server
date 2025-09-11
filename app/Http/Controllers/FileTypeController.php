<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FyleType;
use App\Http\Requests\FileTypeStoreRequest;
use App\Http\Resources\FileTypeResource;

class FileTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = FyleType::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => FileTypeResource::collection($types)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = (new FileTypeStoreRequest())->merge($request->all())->validated();

        $type = FyleType::create($data);

        return response()->json(['success' => true, 'code' => 201, 'data' => new FileTypeResource($type)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $type = FyleType::find($id);
        if (!$type) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'code' => 200, 'data' => new FileTypeResource($type)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $type = FyleType::find($id);
        if (!$type) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        $data = (new FileTypeStoreRequest())->merge($request->all())->validated();
        $type->update($data);

        return response()->json(['success' => true, 'code' => 200, 'data' => new FileTypeResource($type)], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $type = FyleType::find($id);
        if (!$type) {
            return response()->json(['success' => false, 'code' => 404, 'message' => 'Not found'], 404);
        }

        $type->delete();

        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }
}
