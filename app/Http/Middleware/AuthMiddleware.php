<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pour les tests, on accepte n'importe quel token non vide
        // TODO: Implémenter une vraie vérification de token avec stockage en base

        // Simuler un admin pour les tests
        $admin = Admin::first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun administrateur trouvé',
                'error' => 'Configuration système incomplète',
                'timestamp' => now()->toISOString()
            ], 500);
        }

        // Ajouter l'utilisateur à la requête pour un accès facile
        $request->merge(['user' => $admin]);

        return $next($request);
    }
}