<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * Todas las respuestas de error de la API comparten el mismo envelope
     * { success: false, message } con el código HTTP correcto (principio L).
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Errores de dominio.
        $this->renderable(function (ApiException $e) {
            return $this->envelope($e->getMessage(), $e->status);
        });

        // Validación de Form Requests → 422 con el primer mensaje.
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($this->expectsApi($request)) {
                return $this->envelope($e->validator->errors()->first(), 422);
            }
        });

        // Modelo o ruta no encontrados → 404.
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($this->expectsApi($request)) {
                return $this->envelope('Recurso no encontrado', 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($this->expectsApi($request)) {
                return $this->envelope('Ruta no encontrada', 404);
            }
        });

        // No autenticado / no autorizado.
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($this->expectsApi($request)) {
                return $this->envelope('No autenticado', 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($this->expectsApi($request)) {
                return $this->envelope('Acceso denegado', 403);
            }
        });
    }

    private function expectsApi(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function envelope(string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
