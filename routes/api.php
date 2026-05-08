<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\ImportExecutionApiController;
use App\Http\Controllers\Api\MatchApiController;
use App\Http\Controllers\Api\SeasonApiController;
use App\Http\Controllers\Api\TeamApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens/create', [AuthTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/seasons', [SeasonApiController::class, 'index']);
    Route::get('/teams', [TeamApiController::class, 'index']);
    Route::get('/matches', [MatchApiController::class, 'index']);
    Route::get('/imports', [ImportExecutionApiController::class, 'index']);
});
