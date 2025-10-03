<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Reclamo;
use App\Models\ReclamoComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Simula la inserción de un comentario y crea notificaciones para el reclamo dado.
     * GET /api/test/reclamos/{id}/notify-comment?user_id=6
     */
    public function notifyComment(Request $request, int $reclamoId): JsonResponse
    {
        // Autor del comentario (por defecto 6 si no se envía y no hay auth)
        $authorId = (int) $request->query('user_id', $request->user()?->id ?? 6);
        $mode = (string) $request->query('to', 'counterpart'); // 'self' | 'counterpart'
        $reclamo = Reclamo::findOrFail($reclamoId);

        // Crear comentario simulado como si lo hiciera el userId
        $comment = ReclamoComment::create([
            'reclamo_id'       => $reclamo->id,
            'sender_type'      => 'agente',
            'sender_user_id'   => $authorId,
            'creator_id'       => $authorId,
            'message'          => 'Comentario de prueba para notificación',
        ]);

        $recipientId = null;
        if ($mode === 'self') {
            // Notificar al propio usuario (auth o fallback al authorId)
            $recipientId = (int) ($request->user()?->id ?? $authorId);
        } else {
            // Regla de negocio normal: notificar a la contraparte
            $creatorId = $reclamo->creator_id ?? $reclamo->created_by ?? null;
            $agentId   = $reclamo->agente_id ?? null;
            if ($agentId && $authorId === (int)$agentId) {
                $recipientId = $creatorId;
            } elseif ($creatorId && $authorId === (int)$creatorId) {
                $recipientId = $agentId;
            }
        }

        if ($recipientId) {
            Notificacion::create([
                'user_id'     => (int) $recipientId,
                'entity_type' => 'reclamo',
                'entity_id'   => $reclamo->id,
                'type'        => 'comentario',
                'description' => 'Nuevo comentario en el reclamo #' . $reclamo->id,
            ]);
        }

        return response()->json([
            'message' => 'Comentario y notificación simulados',
            'comment_id' => $comment->id,
            'author_id' => $authorId,
            'recipient_id' => $recipientId,
            'mode' => $mode,
        ]);
    }
}
