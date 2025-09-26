<?php

namespace App\Http\Controllers;

use App\Events\ReclamoCommentCreated;
use App\Http\Requests\ReclamoCommentStoreRequest;
use App\Http\Resources\ReclamoCommentResource;
use App\Models\Reclamo;
use App\Models\ReclamoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\ReclamoCommentNotification;

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
    public function store(Request $request, Reclamo $reclamo)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $user = Auth::user(); // cualquiera logueado

        $comment = ReclamoComment::create([
            'reclamo_id'       => $reclamo->id,
            'sender_type'      => 'agente',
            'sender_user_id'   => $user->id,
            'creator_id'       => $user->id,
            'message'          => $request->string('message'),
        ]);

        // si tienes eventos/WS:
        // event(new ReclamoCommentCreated($comment));

        return new ReclamoCommentResource($comment->fresh(['agente']));
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
