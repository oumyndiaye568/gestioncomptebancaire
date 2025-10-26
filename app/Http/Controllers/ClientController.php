<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compte;
use App\Models\Client;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Authentification Client",
 *     description="Endpoints d'authentification pour les clients"
 * )
 *
 * @OA\Tag(
 *     name="Comptes Client",
 *     description="Gestion des comptes pour les clients"
 * )
 */
class ClientController extends Controller
{
    use ApiResponse;
    /**
     * Authentification du client et génération du token
     *
     * @OA\Post(
     *     path="/api/client/login",
     *     summary="Connexion client",
     *     description="Authentifie un client et retourne un token d'accès",
     *     operationId="clientLogin",
     *     tags={"Authentification Client"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="client@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="nom_complet", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="client@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Échec d'authentification",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('client')->attempt($request->only('email', 'password'))) {
            $client = Auth::guard('client')->user();
            $token = $client->createToken('client-token')->plainTextToken;

            return $this->success([
                'client' => $client,
                'token' => $token,
            ], 'Connexion client réussie');
        }

        return $this->unauthorized('Identifiants incorrects');
    }

    /**
     * Récupérer les comptes du client connecté
     * Liste des comptes non supprimés, type cheque ou épargne, actifs
     *
     * @OA\Get(
     *     path="/api/client/comptes",
     *     summary="Lister les comptes du client",
     *     description="Récupère la liste des comptes du client connecté (comptes actifs de type cheque ou épargne)",
     *     operationId="getClientComptes",
     *     tags={"Comptes Client"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="numeroCompte", type="string", example="COMP-20251025-EZ6TJPOP"),
     *                     @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="epargne"),
     *                     @OA\Property(property="solde", type="number", format="float", example="452420.00"),
     *                     @OA\Property(property="devise", type="string", example="FCFA"),
     *                     @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-25T19:21:35+00:00"),
     *                     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque"}, example="actif"),
     *                     @OA\Property(property="motifBlocage", type="string", nullable=true, example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getComptes(Request $request)
    {
        // Vérifier que l'utilisateur connecté est un Client
        $user = $request->user();
        if (!$user instanceof Client) {
            return $this->forbidden('Accès réservé aux clients');
        }

        // Récupérer les comptes du client
        // - Non supprimés (pas de soft delete)
        // - Type cheque ou epargne
        // - Statut actif
        $comptes = Compte::where('client_id', $user->id)
            ->whereIn('type_compte', ['cheque', 'epargne'])
            ->where('etat_compte', 'actif')
            ->get();

        // Formatage des données de réponse
        $data = $comptes->map(function($compte) {
            return [
                'id' => $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'type' => $compte->type_compte,
                'solde' => $compte->solde ?? 0,
                'devise' => 'FCFA',
                'dateCreation' => $compte->created_at->toIso8601String(),
                'statut' => $compte->etat_compte,
                'motifBlocage' => $compte->motif_blocage ?? null,
            ];
        });

        // Retour avec le trait ApiResponse
        return $this->success($data, 'Comptes récupérés avec succès');
    }
}
