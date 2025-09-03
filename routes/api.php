<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FaqController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// FAQ API Routes (sans CSRF pour permettre les requêtes depuis l'interface HTML)
Route::prefix('faq')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::post('/', [FaqController::class, 'store']);
    Route::get('/search', [FaqController::class, 'search']);
    Route::get('/{id}', [FaqController::class, 'show']);
    Route::put('/{id}', [FaqController::class, 'update']);
    Route::delete('/{id}', [FaqController::class, 'destroy']);
});
