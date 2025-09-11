<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e): Response
    {
        // For non-JSON requests, fallback to parent
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return parent::render($request, $e);
        }

        // Validation errors
        if ($e instanceof ValidationException) {
            $payload = [
                'status' => 422,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ];
            if (config('app.debug')) {
                $payload['raw'] = [
                    'exception' => class_basename($e),
                    'message' => $e->getMessage(),
                ];
            }
            return response()->json($payload, 422);
        }

        // Auth/Not found/HTTP
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => 401,
                'code' => 'UNAUTHENTICATED',
                'message' => 'No autenticado.',
            ], 401);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 404,
                'code' => 'NOT_FOUND',
                'message' => 'Recurso no encontrado.',
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => 405,
                'code' => 'METHOD_NOT_ALLOWED',
                'message' => 'Método no permitido.',
            ], 405);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            return response()->json([
                'status' => $status,
                'code' => $status,
                'message' => $e->getMessage() ?: 'Error de aplicación.',
            ], $status);
        }

        // Default
        $payload = [
            'status' => 500,
            'code' => 'SERVER_ERROR',
            'message' => 'Ocurrió un error inesperado.',
        ];
        if (config('app.debug')) {
            $payload['raw'] = [
                'exception' => class_basename($e),
                'message' => $e->getMessage(),
                'trace' => collect($e->getTrace())->take(5),
            ];
        }
        return response()->json($payload, 500);
    }
}
