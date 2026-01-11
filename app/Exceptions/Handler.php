<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle specific HTTP exceptions
        if ($e instanceof AccessDeniedHttpException) {
            return response()->view('errors.403', [
                'title' => 'Acceso Denegado',
                'message' => 'No tiene permisos para acceder a esta página.',
                'code' => 403,
            ], 403);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [
                'title' => 'Página No Encontrada',
                'message' => 'La página que busca no existe o ha sido movida.',
                'code' => 404,
            ], 404);
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return response()->view('errors.429', [
                'title' => 'Demasiadas Solicitudes',
                'message' => 'Ha realizado demasiadas solicitudes. Por favor, inténtelo de nuevo más tarde.',
                'code' => 429,
            ], 429);
        }

        if ($e instanceof ValidationException) {
            // Let Laravel handle validation exceptions normally
            return parent::render($request, $e);
        }

        // Handle 419 CSRF token mismatch
        if ($e->getStatusCode() === 419) {
            return response()->view('errors.419', [
                'title' => 'Sesión Expirada',
                'message' => 'Su sesión ha expirado. Por favor, recargue la página e inténtelo nuevamente.',
                'code' => 419,
            ], 419);
        }

        // Handle server errors
        if (app()->environment('production')) {
            return response()->view('errors.500', [
                'title' => 'Error del Servidor',
                'message' => 'Ha ocurrido un error interno. Por favor, contacte al administrador.',
                'code' => 500,
            ], 500);
        }

        // In development, let Laravel handle it
        return parent::render($request, $e);
    }
}
