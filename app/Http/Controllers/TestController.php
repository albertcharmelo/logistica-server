<?php

namespace App\Http\Controllers;

use App\Events\ReclamoCommentNotification;
use App\Models\Reclamo;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * GET /api/test/reclamos/{id}/notify-comment?message=...&target=agente|creador|both
     * Emits websocket notifications to the reclamo's agente and/or creator without persisting a comment.
     * Blocked in production.
     */
    public function notifyComment(Request $request, int $id)
    {
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'code'    => 403,
                'message' => 'Test endpoint disabled in production',
            ], 403);
        }

        $message = (string) $request->query('message', 'Comentario de prueba');
        $target  = (string) $request->query('target', 'both'); // agente|creador|both

        $reclamo = Reclamo::find($id);
        if (!$reclamo) {
            return response()->json([
                'success' => false,
                'code'    => 404,
                'message' => 'Reclamo no encontrado',
            ], 404);
        }

        $sent = [];
        if (in_array($target, ['agente', 'both'], true) && $reclamo->agente_id) {
            event(new ReclamoCommentNotification(
                userId: (int) $reclamo->agente_id,
                reclamoId: (int) $reclamo->id,
                commentId: 0,
                message: $message,
                role: 'agente'
            ));
            $sent[] = 'agente';
        }
        $creatorId = $reclamo->creator_id ?? $reclamo->created_by ?? null;
        if (in_array($target, ['creador', 'both'], true) && $creatorId) {
            event(new ReclamoCommentNotification(
                userId: (int) $creatorId,
                reclamoId: (int) $reclamo->id,
                commentId: 0,
                message: $message,
                role: 'creador'
            ));
            $sent[] = 'creador';
        }

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => [
                'targets' => $sent,
                'reclamo_id' => $reclamo->id,
                'message' => $message,
            ],
        ], 200);
    }
}
