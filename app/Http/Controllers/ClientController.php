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
 *     description="Endpoints d'authentification OAuth2 pour les clients"
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
     * @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=31536000),
     *                 @OA\Property(property="refresh_token", type="string", example="def50200..."),
     *                 @OA\Property(property="client", type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="nom_complet", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="client@example.com"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Connexion client réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Token OAuth2 créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=31536000),
     *                 @OA\Property(property="client", type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="nom_complet", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="client@example.com"),
     *                     @OA\Property(property="role", type="string", example="client")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Connexion client réussie")
     *         )
     *     ),
     * @OA\Response(
     *         response=401,
     *         description="Échec d'authentification",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects")
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

        // Recherche du client par email
        $client = Client::where('email', $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects',
                'error' => 'invalid_credentials',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Création du token OAuth avec Passport
        $tokenResult = $client->createToken('Client Access Token');

        // Formatage de la réponse avec access_token et refresh_token
        $data = [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 31536000, // 1 an en secondes
            'refresh_token' => $tokenResult->token->id, // Pour simplifier, on utilise l'ID du token comme refresh token
            'client' => [
                'id' => $client->id,
                'nom_complet' => $client->nom_complet,
                'email' => $client->email,
                'role' => 'client'
            ]
        ];

        return $this->success($data, 'Connexion client réussie');
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
     *     security={{"bearerAuth":{}}},
     * @OA\Response(
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
     *             ),
     *             @OA\Property(property="message", type="string", example="Comptes récupérés avec succès")
     *         )
     *     ),
     * @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès réservé aux clients")
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
