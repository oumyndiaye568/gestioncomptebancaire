<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:passport')->get('/user', function (Request $request) {
//    return $request->user();
// });

// Routes OAuth2 Passport - Supprimé car Passport gère automatiquement cette route
// Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')
//     ->middleware(['throttle'])
//     ->name('passport.token');

// Routes d'authentification unifiées
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

// Routes centralisées pour les comptes (RESTful)
Route::middleware(['auth:api', 'logging'])->prefix('v1')->group(function () {
    // Test route pour vérifier l'authentification
    Route::get('test-auth', function() {
        $user = request()->user();
        $role = $user instanceof \App\Models\Admin ? 'admin' : 'client';
        return response()->json([
            'success' => true,
            'message' => 'Authentification réussie',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $role,
                'nom' => $user instanceof \App\Models\Admin ? $user->nom : $user->nom_complet
            ],
            'timestamp' => now()->toISOString()
        ]);
    });

    // Route de test pour Twilio SMS
    Route::get('test-sms', function() {
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $message = $twilio->messages->create(
                '+221771234567', // Numéro de test - à remplacer
                [
                    'from' => config('services.twilio.from'),
                    'body' => 'Test SMS - API Gestion Comptes fonctionne!'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'SMS envoyé avec succès',
                'sid' => $message->sid,
                'status' => $message->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    });

    // Routes pour les comptes bancaires
    Route::prefix('comptes')->group(function () {
        // Création de compte (réservé aux admins)
        Route::post('/', [AdminController::class, 'store'])->middleware('role:admin');

        // Liste des comptes (accessible aux admins et clients)
        Route::get('/', [AdminController::class, 'index']);

        // Détail d'un compte
        Route::get('/{id}', [AdminController::class, 'getCompteDetails']);

        // Mise à jour d'un compte (réservé aux admins)
        Route::put('/{id}', [AdminController::class, 'updateCompte'])->middleware('role:admin');

        // Suppression logique d'un compte (réservé aux admins)
        Route::delete('/{id}', [AdminController::class, 'deleteCompte'])->middleware('role:admin');

        // Routes supplémentaires pour les comptes (réservées aux admins)
        Route::middleware('role:admin')->group(function () {
            Route::get('/archived', [AdminController::class, 'getComptesArchived']);
            Route::patch('/{id}/archive', [AdminController::class, 'archiveCompte']);
            Route::patch('/{id}/unarchive', [AdminController::class, 'unarchiveCompte']);
            Route::patch('/{id}/block', [AdminController::class, 'blockCompte']);
            Route::patch('/{id}/unblock', [AdminController::class, 'unblockCompte']);
        });
    });
});

// Route de test
Route::get('test', function() {
    return response()->json([
        'status' => 'OK',
        'message' => 'API Laravel fonctionne correctement',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment()
    ]);
});
