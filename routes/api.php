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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
// });

// Routes pour l'admin
Route::post('admin/login', [AdminController::class, 'login']);

Route::prefix('admin')->group(function () {
    Route::get('comptes', [AdminController::class, 'getComptes']);
    Route::get('comptes/{id}', [AdminController::class, 'getCompteDetails']);
    Route::put('comptes/{id}', [AdminController::class, 'updateCompte']);
    Route::delete('comptes/{id}', [AdminController::class, 'deleteCompte']);
});

// Routes pour le client
Route::post('client/login', [ClientController::class, 'login']);

Route::prefix('client')->group(function () {
    Route::get('comptes', [ClientController::class, 'getComptes']);
});
