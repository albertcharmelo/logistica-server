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
    public function store(ReclamoCommentStoreRequest $request, int $reclamoId)
    {
        $payload = $request->validated();
        $payload['reclamo_id'] = $reclamoId;

        // Reclamo vinculado
        $reclamo = Reclamo::findOrFail($reclamoId);

        // Derivar el autor autenticado
        $authUserId = (int) ($request->user()?->id ?? 0);

        // Respetar el sender_type que deriva el FormRequest. Si el emisor es 'creador',
        // forzamos creator_id = usuario autenticado. Si es 'agente', no tocar creator_id.
        // Si es 'persona', no tocar creator_id (persona no es user).
        $senderType = $payload['sender_type'] ?? null;
        if ($senderType === 'creador' && $authUserId > 0) {
            $payload['creator_id'] = $authUserId;
            // aseguramos no marcar agente_id al mismo tiempo
            unset($payload['agente_id']);
        }

        // Crear comentario con los IDs resultantes
        $comment = \App\Models\ReclamoComment::create($payload);
        $comment->load(['persona', 'agente', 'creator']);
        event(new ReclamoCommentCreated($comment)); // Disparar el evento de comentario creado

        // Notificar por websocket al responsable/creador si corresponde
        // Autor: si fue creador, es el auth user; si fue agente, es el agente; si persona, ningún user
        $authorUserId = null;
        if (($payload['sender_type'] ?? null) === 'creador') {
            $authorUserId = $authUserId ?: null;
        } elseif (($payload['sender_type'] ?? null) === 'agente') {
            $authorUserId = $payload['agente_id'] ?? null;
        }

        // Si el responsable (agente) existe y no es el autor
        if ($reclamo->agente_id && $reclamo->agente_id !== $authorUserId) {
            event(new ReclamoCommentNotification(
                userId: (int) $reclamo->agente_id,
                reclamoId: (int) $reclamo->id,
                commentId: (int) $comment->id,
                message: 'Nuevo comentario en tu reclamo asignado',
                role: 'agente',
            ));
        }
        // Si el creador existe y no es el autor
        $creatorUser = $reclamo->creator_id ?? $reclamo->created_by ?? null;
        if ($creatorUser && $creatorUser !== $authorUserId) {
            event(new ReclamoCommentNotification(
                userId: (int) $creatorUser,
                reclamoId: (int) $reclamo->id,
                commentId: (int) $comment->id,
                message: 'Nuevo comentario en el reclamo que creaste',
                role: 'creador',
            ));
        }
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
