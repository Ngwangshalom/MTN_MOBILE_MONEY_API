<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MomoController;

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


Route::post('/initiate-payment', [MomoController::class, 'initiatePayment']);

// Route::get('/test-uuid', [MomoController::class, 'testUuid']);
// Route::get('/test-create-user', [MomoController::class, 'testCreateUser']);
// Route::get('/test-request-user/{userId}', [MomoController::class, 'testRequestUser']);
// Route::get('/test-generate-api-key/{userId}', [MomoController::class, 'testGenerateApiKey']);
// Route::get('/generate-token', [MomoController::class, 'generateToken']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
