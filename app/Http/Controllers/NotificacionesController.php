<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notificacion;

class NotificacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notificacion::query()->where('user_id', $user->id)->latest();

        // Optional filters: read, type, entity_type
        if ($request->filled('read')) {
            $read = filter_var($request->get('read'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($read === true) {
                $query->whereNotNull('read_at');
            } elseif ($read === false) {
                $query->whereNull('read_at');
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => [
                'notifications' => $paginator->items(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = Notificacion::where('user_id', $user->id)->findOrFail($id);
        $notification->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = Notificacion::where('user_id', $user->id)->findOrFail($id);
        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }
        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notificacion::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    }
}
