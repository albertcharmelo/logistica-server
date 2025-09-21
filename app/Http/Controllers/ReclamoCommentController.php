<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReclamoCommentStoreRequest;
use App\Http\Resources\ReclamoCommentResource;
use App\Models\Reclamo;
use App\Models\ReclamoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReclamoCommentController extends Controller
{
    /**
     * Listar comentarios de un reclamo (paginado como ArchivoController)
     * GET /api/reclamos/{reclamo}/comments?per_page=10
     */
    public function index(Request $request, int $reclamoId)
    {
        $perPage = (int) $request->query('per_page', 0);

        $query = ReclamoComment::with(['persona', 'agente', 'creator'])
            ->where('reclamo_id', $reclamoId)
            ->orderBy('id'); // cronológico asc (cambia a desc si prefieres)

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'code'    => 200,
                'data'    => [
                    'comentarios' => ReclamoCommentResource::collection($paginator->items()),
                    'pagination'  => [
                        'total'         => $paginator->total(),
                        'per_page'      => $paginator->perPage(),
                        'current_page'  => $paginator->currentPage(),
                        'last_page'     => $paginator->lastPage(),
                    ],
                ],
            ], 200);
        }

        $items = $query->get();

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => ReclamoCommentResource::collection($items),
        ], 200);
    }

    /**
     * Crear comentario
     * POST /api/reclamos/{reclamo}/comments
     */
    public function store(ReclamoCommentStoreRequest $request, int $reclamoId)
    {
        $payload = $request->validated();
        $payload['reclamo_id'] = $reclamoId;

        // 1) intentar leer del reclamo
        $reclamo = Reclamo::findOrFail($reclamoId);

        // ajusta el nombre del campo según tu tabla de reclamos:
        $reclamoCreator = $reclamo->creator_id
            ?? $reclamo->created_by
            ?? null;

        // 2) fallback: usuario autenticado (si aplica)
        $payload['creator_id'] = $reclamoCreator ?? (Auth::user()->id() ?: null);

        $comment = \App\Models\ReclamoComment::create($payload);
        $comment->load(['persona', 'agente', 'creator']);

        return response()->json([
            'success' => true,
            'code'    => 201,
            'data'    => new \App\Http\Resources\ReclamoCommentResource($comment),
        ], 201);
    }
    /**
     * Borrado lógico del comentario
     * DELETE /api/reclamos/{reclamo}/comments/{id}
     */
    public function destroy(int $reclamoId, int $id)
    {
        $comment = ReclamoComment::where('reclamo_id', $reclamoId)->findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => null,
        ], 200);
    }
}
