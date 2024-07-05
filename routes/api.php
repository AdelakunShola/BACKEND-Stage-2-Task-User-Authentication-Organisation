<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\UserController;
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

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('organisations', [OrganisationController::class, 'index']);
    Route::get('organisations/{orgId}', [OrganisationController::class, 'show']);
    Route::post('organisations', [OrganisationController::class, 'store']);
    Route::post('organisations/{orgId}/users', [OrganisationController::class, 'addUser']);
});