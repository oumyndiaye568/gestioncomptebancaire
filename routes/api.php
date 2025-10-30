<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;

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

// Routes pour l'admin
Route::prefix('oumy/v1/auth')->group(function () {
    Route::post('', [AdminController::class, 'login']);
    Route::post('refresh', [AdminController::class, 'refresh']);
    Route::post('logout', [AdminController::class, 'logout'])->middleware('auth:api');
});

Route::middleware(['auth:api', 'role:admin', 'logging'])->prefix('oumy/v1/admin')->group(function () {
    // Test route pour vérifier l'authentification
    Route::get('test-auth', function() {
        return response()->json([
            'success' => true,
            'message' => 'Authentification réussie',
            'user' => request()->user(),
            'timestamp' => now()->toISOString()
        ]);
    });

    Route::get('comptes', [AdminController::class, 'getComptes']);
    Route::post('comptes', [AdminController::class, 'createCompte']);
    Route::get('comptes/archived', [AdminController::class, 'getComptesArchived']);
    Route::get('comptes/{id}', [AdminController::class, 'getCompteDetails']);
    Route::put('comptes/{id}', [AdminController::class, 'updateCompte']);
    Route::patch('comptes/{id}/archive', [AdminController::class, 'archiveCompte']);
    Route::patch('comptes/{id}/unarchive', [AdminController::class, 'unarchiveCompte']);
    Route::patch('comptes/{id}/block', [AdminController::class, 'blockCompte']);
    Route::patch('comptes/{id}/unblock', [AdminController::class, 'unblockCompte']);
    Route::delete('comptes/{id}', [AdminController::class, 'deleteCompte']);
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

// Routes pour le client
Route::post('client/login', [ClientController::class, 'login']);

Route::middleware('auth:api')->prefix('client')->group(function () {
    Route::get('comptes', [ClientController::class, 'getComptes']);
});
