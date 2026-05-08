<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE matches
            INNER JOIN teams ON teams.id = matches.team_id
            SET matches.competition = teams.competition_group
            WHERE matches.competition IS NULL
              AND teams.competition_group IS NOT NULL
        ");
    }

    public function down(): void
    {
    }
};
