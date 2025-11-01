<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Client;
use App\Traits\ApiResponse;

/**
 * Contrôleur unifié pour l'authentification des admins et clients
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Connexion unifiée administrateur/client
     */
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $email = $request->email;
        $password = $request->password;

        // Recherche d'abord dans les admins
        $admin = Admin::where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            // Création du token OAuth pour l'admin
            $tokenResult = $admin->createToken('Admin Access Token');

            $data = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 31536000,
                'refresh_token' => $tokenResult->token->id,
                'admin' => [
                    'id' => $admin->id,
                    'nom' => $admin->nom,
                    'email' => $admin->email,
                    'role' => 'admin'
                ]
            ];

            return $this->success($data, 'Connexion administrateur réussie');
        }

        // Recherche dans les clients si pas trouvé dans les admins
        $client = Client::where('email', $email)->first();
        if ($client && Hash::check($password, $client->password)) {
            // Création du token OAuth pour le client
            $tokenResult = $client->createToken('Client Access Token');

            $data = [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 31536000,
                'refresh_token' => $tokenResult->token->id,
                'client' => [
                    'id' => $client->id,
                    'nom_complet' => $client->nom_complet,
                    'email' => $client->email,
                    'role' => 'client'
                ]
            ];

            return $this->success($data, 'Connexion client réussie');
        }

        // Aucun utilisateur trouvé ou mot de passe incorrect
        return response()->json([
            'success' => false,
            'message' => 'Identifiants invalides',
            'error' => 'invalid_credentials',
            'timestamp' => now()->toISOString()
        ], 401);
    }

    /**
     * Rafraîchir le token d'accès
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        // Pour simplifier, on génère un nouveau token
        // Dans un vrai système, on vérifierait le refresh token
        $admin = Admin::first(); // Simulation

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token invalide',
                'error' => 'invalid_refresh_token',
                'timestamp' => now()->toISOString()
            ], 401);
        }

        // Création d'un nouveau token
        $tokenResult = $admin->createToken('Admin Access Token', ['read:users', 'create:accounts', 'delete:transactions']);

        $data = [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 31536000,
        ];

        return $this->success($data, 'Token rafraîchi avec succès');
    }

    /**
     * Déconnexion de l'utilisateur (admin ou client)
     */
    public function logout(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $user = $request->user();

        if ($user) {
            // Révoquer tous les tokens de l'utilisateur
            $user->tokens()->delete();
        }

        return $this->success(null, 'Déconnexion réussie');
    }
}