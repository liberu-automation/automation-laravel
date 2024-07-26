<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ControlPanelController;

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/control-panel/create-account', [ControlPanelController::class, 'createAccount']);
    Route::post('/control-panel/suspend-account', [ControlPanelController::class, 'suspendAccount']);
    Route::delete('/control-panel/delete-account', [ControlPanelController::class, 'deleteAccount']);
});