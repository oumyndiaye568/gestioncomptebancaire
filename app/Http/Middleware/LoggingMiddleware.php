<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $method = $request->method();
        $path = $request->path();
        $ip = $request->ip();

        // Log de l'action avant traitement
        Log::info('Action utilisateur', [
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? get_class($user) : 'anonymous',
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        $response = $next($request);

        // Log de l'action après traitement
        $statusCode = $response->getStatusCode();
        Log::info('Action terminée', [
            'user_id' => $user ? $user->id : null,
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'duration' => now()->diffInMilliseconds($request->server('REQUEST_TIME_FLOAT')),
            'timestamp' => now()->toISOString()
        ]);

        return $response;
    }
}