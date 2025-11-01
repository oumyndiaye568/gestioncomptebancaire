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

    // Routes RESTful pour les comptes
    Route::apiResource('comptes', AdminController::class, [
        'parameters' => ['comptes' => 'id']
    ]);

    // Routes supplémentaires pour les comptes
    Route::middleware('role:admin')->group(function () {
        Route::get('comptes/archived', [AdminController::class, 'getComptesArchived']);
        Route::patch('comptes/{id}/archive', [AdminController::class, 'archiveCompte']);
        Route::patch('comptes/{id}/unarchive', [AdminController::class, 'unarchiveCompte']);
        Route::patch('comptes/{id}/block', [AdminController::class, 'blockCompte']);
        Route::patch('comptes/{id}/unblock', [AdminController::class, 'unblockCompte']);
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
