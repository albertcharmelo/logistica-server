<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/* ------------------------------------------ USUARIOS ------------------------------------------ */
Route::prefix('auth')->group(function () {
    Route::apiResource('users', \App\Http\Controllers\UserController::class)->middleware('auth:sanctum');

    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/reset-password', [\App\Http\Controllers\AuthController::class, 'resetPassword']);
});

/* ------------------------------------------ UNIDADES ------------------------------------------ */
Route::apiResource('unidades', \App\Http\Controllers\UnidadController::class)->middleware('auth:sanctum');
/* ----------------------------------------- SUCURSALES ----------------------------------------- */
Route::apiResource('sucursals', \App\Http\Controllers\SucursalController::class)->middleware('auth:sanctum');
/* -------------------------------------- TIPOS DE ARCHIVO -------------------------------------- */
Route::apiResource('tipo-archivos', \App\Http\Controllers\FileTypeController::class)->middleware('auth:sanctum');
/* ----------------------------------------- CLIENTES ----------------------------------------- */
Route::apiResource('clientes', \App\Http\Controllers\ClientesController::class)->middleware('auth:sanctum');
/* ----------------------------------------- PERSONAL ----------------------------------------- */
Route::apiResource('personal', \App\Http\Controllers\PersonalController::class);

/* ----------------------------------------- ARCHIVOS (PERSONAL) ----------------------------------------- */
Route::middleware('auth:sanctum')->prefix('archivos')->group(function () {
    Route::post('upload', [\App\Http\Controllers\ArchivoController::class, 'upload']);
    Route::post('download', [\App\Http\Controllers\ArchivoController::class, 'download']);
    Route::delete('{id}', [\App\Http\Controllers\ArchivoController::class, 'destroy']);
    Route::get('persona/{id}', [\App\Http\Controllers\ArchivoController::class, 'byPersona']);
});
