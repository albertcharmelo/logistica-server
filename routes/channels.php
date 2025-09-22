<?php

use App\Models\Reclamo;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('users.{id}', function ($user, int $id) {
    return (int)$user->id === (int)$id ? ['id' => $user->id, 'name' => $user->name] : false;
});

Broadcast::channel('reclamos.{id}', function ($user, int $id) {
    $reclamo = Reclamo::find($id);
    if (!$reclamo) return false;

    $isCreator = (int)$reclamo->creator_id === (int)$user->id;
    $isAgente  = (int)$reclamo->agente_id  === (int)$user->id;
    $isAdmin   = method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

    return ($isCreator || $isAgente || $isAdmin) ? ['id' => $user->id, 'name' => $user->name] : false;
});

// Opcional presencia
Broadcast::channel('presence.reclamos.{id}', function ($user, int $id) {
    return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
});
