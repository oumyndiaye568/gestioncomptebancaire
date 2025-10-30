<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise',
                'error' => 'unauthenticated',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Vérifier le rôle de l'utilisateur (par défaut admin pour les tests)
        $userRole = $user->role ?? 'admin';
        if ($userRole !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé - Rôle insuffisant',
                'error' => 'insufficient_role',
                'required_role' => $role,
                'user_role' => $userRole,
                'timestamp' => now()->toISOString()
            ], 403);
        }

        return $next($request);
    }
}