<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::get('/users/{id}', [AuthController::class, 'getUser']);
    Route::get('/organisations', [AuthController::class, 'getUserOrganisations']);
    Route::get('/organisations/{id}', [AuthController::class, 'getOrganisation']);
    Route::post('/organisations', [AuthController::class, 'createOrganisation']);
    Route::post('/organisations/{id}/users', [AuthController::class, 'addUserToOrganisation']);
   });