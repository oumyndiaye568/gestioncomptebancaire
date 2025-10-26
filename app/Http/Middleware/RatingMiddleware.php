<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

/**
 * Middleware pour vérifier le rating/utilisation d'un utilisateur
 * Bloque les actions si le rating dépasse la limite autorisée
 */
class RatingMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier si l'utilisateur a un rating
        if ($user && isset($user->rating)) {
            $maxRating = config('app.max_rating', 100); // Limite configurable

            if ($user->rating >= $maxRating) {
                return $this->error(
                    'Votre limite d\'utilisation a été atteinte. Veuillez contacter le support.',
                    429
                );
            }
        }

        return $next($request);
    }
}
