<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('import_clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('fbib_club_id')->unique();
            $table->string('url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_club_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->unsignedBigInteger('fbib_team_id')->nullable();
            $table->string('category')->nullable();
            $table->string('sponsor')->nullable();
            $table->string('competition_group')->nullable();
            $table->string('gender')->nullable();
            $table->string('level')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_imported')->default(false);
            $table->string('url_fbib')->nullable();
            $table->json('import_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'fbib_team_id']);
        });

        Schema::create('locker_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('pavilion')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedBigInteger('fbib_match_id')->nullable()->unique();
            $table->string('home_team');
            $table->string('away_team');
            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();
            $table->dateTime('match_datetime')->nullable();
            $table->string('round')->nullable();
            $table->unsignedTinyInteger('import_month')->nullable();
            $table->string('status')->nullable();
            $table->string('pavilion')->nullable();
            $table->unsignedSmallInteger('score_home')->nullable();
            $table->unsignedSmallInteger('score_away')->nullable();
            $table->foreignId('locker_room_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_imported')->default(false);
            $table->json('import_data')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'home_team', 'away_team', 'match_datetime'], 'matches_unique_manual');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('action');
            $table->string('entity');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('ip')->nullable();
            $table->string('result')->default('success');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('import_executions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('import_club_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('result')->default('pending');
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->json('details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_executions');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('locker_rooms');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('import_clubs');
        Schema::dropIfExists('seasons');
    }
};
