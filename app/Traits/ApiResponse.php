<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait ApiResponse
 *
 * Fournit des méthodes standardisées pour les réponses API JSON
 */
trait ApiResponse
{
    /**
     * Retourne une réponse de succès
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success($data = null, string $message = 'Opération réussie', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Retourne une réponse d'erreur
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function error(string $message = 'Une erreur est survenue', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retourne une réponse de succès avec pagination
     *
     * @param mixed $data
     * @param mixed $pagination
     * @param string $message
     * @return JsonResponse
     */
    protected function successWithPagination($data, $pagination, string $message = 'Données récupérées avec succès'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
            'timestamp' => now()->toISOString()
        ], 200);
    }

    /**
     * Retourne une réponse de validation échouée
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationError($errors, string $message = 'Erreur de validation'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Retourne une réponse d'erreur d'authentification
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorized(string $message = 'Non autorisé'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Retourne une réponse d'erreur d'autorisation
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbidden(string $message = 'Accès interdit'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Retourne une réponse de ressource non trouvée
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFound(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return $this->error($message, 404);
    }
}