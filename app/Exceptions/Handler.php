<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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

        $this->renderable(function (Throwable $e, $request) {
            // Pour les requêtes API, retourner du JSON même en cas d'erreur
            if ($request->is('api/*') || $request->expectsJson()) {
                // Log détaillé de l'erreur pour le debugging
                \Log::error('API Error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                    'request_data' => $request->all(),
                    'headers' => $request->headers->all()
                ]);

                // En production, retourner plus de détails pour le debugging
                $errorDetails = app()->environment('local') ? $e->getMessage() : 'Internal Server Error';

                // Si c'est une erreur de base de données, donner plus d'infos
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $errorDetails = app()->environment('local') ? 'Database Error: ' . $e->getMessage() : 'Database connection error';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Server Error',
                    'error' => $errorDetails,
                    'timestamp' => now()->toISOString(),
                    'request_id' => uniqid('req_', true)
                ], 500);
            }
        });
    }
}
