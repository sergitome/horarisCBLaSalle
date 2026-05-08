<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\ClubMatch;
use App\Models\ImportClub;
use App\Models\ImportExecution;
use App\Models\LockerRoom;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\ClubMatchPolicy;
use App\Policies\ImportClubPolicy;
use App\Policies\ImportExecutionPolicy;
use App\Policies\LockerRoomPolicy;
use App\Policies\SeasonPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        \Illuminate\Support\Facades\Gate::policy(User::class, UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(Season::class, SeasonPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(ImportClub::class, ImportClubPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(Team::class, TeamPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(ClubMatch::class, ClubMatchPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(LockerRoom::class, LockerRoomPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(AuditLog::class, AuditLogPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(ImportExecution::class, ImportExecutionPolicy::class);
    }
}
