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

        // Déterminer l'opération basée sur la route
        $operation = $this->getOperationName($method, $path);

        // Log de l'action avant traitement
        Log::info('Action utilisateur', [
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? get_class($user) : 'anonymous',
            'operation' => $operation,
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
            'host' => $request->getHost(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        $response = $next($request);

        // Log de l'action après traitement
        $statusCode = $response->getStatusCode();
        $requestTime = $request->server('REQUEST_TIME_FLOAT');
        $duration = 0;
        if ($requestTime && is_numeric($requestTime)) {
            try {
                $startTime = \Carbon\Carbon::createFromTimestamp($requestTime);
                $duration = now()->diffInMilliseconds($startTime);
            } catch (\Exception $e) {
                $duration = 0;
            }
        }
        Log::info('Action terminée', [
            'user_id' => $user ? $user->id : null,
            'operation' => $operation,
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'duration' => $duration,
            'host' => $request->getHost(),
            'timestamp' => now()->toISOString()
        ]);

        return $response;
    }

    /**
     * Détermine le nom de l'opération basée sur la méthode et le chemin
     */
    private function getOperationName(string $method, string $path): string
    {
        // Pour les routes de comptes
        if (str_contains($path, '/comptes')) {
            if ($method === 'POST') {
                return 'Création de compte';
            } elseif ($method === 'GET') {
                if (str_contains($path, '/comptes/archived')) {
                    return 'Consultation comptes archivés';
                } elseif (preg_match('/\/comptes\/\w+$/', $path)) {
                    return 'Consultation détail compte';
                } else {
                    return 'Consultation liste comptes';
                }
            } elseif ($method === 'PUT' || $method === 'PATCH') {
                if (str_contains($path, '/archive')) {
                    return 'Archivage compte';
                } elseif (str_contains($path, '/unarchive')) {
                    return 'Désarchivage compte';
                } elseif (str_contains($path, '/block')) {
                    return 'Blocage compte';
                } elseif (str_contains($path, '/unblock')) {
                    return 'Déblocage compte';
                } else {
                    return 'Modification compte';
                }
            } elseif ($method === 'DELETE') {
                return 'Suppression compte';
            }
        }

        // Pour les routes d'authentification
        if (str_contains($path, '/auth')) {
            if (str_contains($path, '/login')) {
                return 'Connexion';
            } elseif (str_contains($path, '/logout')) {
                return 'Déconnexion';
            } elseif (str_contains($path, '/refresh')) {
                return 'Rafraîchissement token';
            }
        }

        // Opération par défaut
        return 'Action ' . $method;
    }
}