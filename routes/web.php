<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportClubController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LockerRoomController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SeasonStatisticsController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('seasons', SeasonController::class)->except('show');
    Route::resource('clubs', ImportClubController::class)->except('show')->parameters(['clubs' => 'import_club']);
    Route::resource('teams', TeamController::class)->except('show');
    Route::get('/statistics', [SeasonStatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/matches/between-dates', [MatchController::class, 'betweenDates'])->name('matches.between-dates');
    Route::patch('/matches/{match}/schedule', [MatchController::class, 'updateSchedule'])->name('matches.update-schedule');
    Route::resource('matches', MatchController::class)->except('show');
    Route::resource('locker-rooms', LockerRoomController::class)->except('show')->parameters(['locker-rooms' => 'locker_room']);
    Route::get('/logs', [AuditLogController::class, 'index'])->name('logs.index');
    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('/imports/{club}/teams', [ImportController::class, 'importTeams'])->name('imports.teams');
    Route::post('/imports/{club}/matches', [ImportController::class, 'importMatches'])->name('imports.matches');
    Route::get('/imports/executions/{execution}/status', [ImportController::class, 'status'])->name('imports.status');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
