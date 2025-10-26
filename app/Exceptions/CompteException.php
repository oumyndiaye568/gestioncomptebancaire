<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

/**
 * Exception personnalisée pour les erreurs liées aux comptes
 */
class CompteException extends Exception
{
    use ApiResponse;

    /**
     * Code HTTP associé à l'exception
     */
    protected int $statusCode = 400;

    /**
     * Données supplémentaires à retourner
     */
    protected array $data = [];

    /**
     * Créer une nouvelle instance d'exception
     *
     * @param string $message
     * @param int $statusCode
     * @param array $data
     */
    public function __construct(string $message = 'Erreur liée au compte', int $statusCode = 400, array $data = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * Convertir l'exception en réponse JSON
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return $this->error(
            $this->getMessage(),
            $this->statusCode,
            $this->data
        );
    }

    /**
     * Créer une exception pour compte non trouvé
     *
     * @param string $numero
     * @return static
     */
    public static function compteNotFound(string $numero = ''): self
    {
        $message = $numero
            ? "Le compte numéro '{$numero}' n'a pas été trouvé."
            : "Le compte demandé n'a pas été trouvé.";

        return new static($message, 404);
    }

    /**
     * Créer une exception pour compte bloqué
     *
     * @param string $motif
     * @return static
     */
    public static function compteBloque(string $motif = ''): self
    {
        $message = $motif
            ? "Ce compte est bloqué : {$motif}"
            : "Ce compte est bloqué.";

        return new static($message, 403);
    }

    /**
     * Créer une exception pour solde insuffisant
     *
     * @param float $soldeRequis
     * @param float $soldeDisponible
     * @return static
     */
    public static function soldeInsuffisant(float $soldeRequis, float $soldeDisponible): self
    {
        $message = "Solde insuffisant. Requis: {$soldeRequis} FCFA, Disponible: {$soldeDisponible} FCFA";

        return new static($message, 400, [
            'solde_requis' => $soldeRequis,
            'solde_disponible' => $soldeDisponible
        ]);
    }
}
