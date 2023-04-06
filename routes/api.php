<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('todos')->group(function () {
        Route::get('/', [TodoController::class, 'index']);
        Route::post('/', [TodoController::class, 'create']);
        Route::post('/order', [TodoController::class, 'order']);
        Route::prefix('{todo}')->group(function () {
            Route::get('/', [TodoController::class, 'show']);
            Route::patch('/', [TodoController::class, 'update']);
            Route::delete('/', [TodoController::class, 'delete']);
        });
    });
});

Route::middleware(['auth:api'])->group(function() {
    Route::prefix('lists')->group(function() {
        Route::get('/', [ListaController::class, 'index']);
        Route::post('/', [ListaController::class, 'create']);
        Route::prefix('{lista}')->group(function () {
            Route::get('/share', [ListaController::class, 'share']);
            Route::patch('/updateKey', [ListaController::class, 'updateKey']);
            Route::patch('/updateTipologia', [ListaController::class, 'updateTipologia']);
        });
    });
});

