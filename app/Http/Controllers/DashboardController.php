<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClubMatch;
use App\Models\ImportClub;
use App\Models\ImportExecution;
use App\Models\LockerRoom;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard.index', [
            'stats' => [
                'users' => User::count(),
                'seasons' => Season::count(),
                'clubs' => ImportClub::count(),
                'teams' => Team::count(),
                'matches' => ClubMatch::count(),
                'lockerRooms' => LockerRoom::count(),
            ],
            'upcomingMatches' => ClubMatch::with(['team', 'lockerRoom'])
                ->whereNotNull('match_datetime')
                ->orderBy('match_datetime')
                ->limit(8)
                ->get(),
            'latestImports' => ImportExecution::with('importClub')
                ->orderByDesc('started_at')
                ->limit(6)
                ->get(),
            'latestLogs' => AuditLog::orderByDesc('created_at')->limit(8)->get(),
        ]);
    }
}
