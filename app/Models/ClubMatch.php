<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMatch extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'season_id',
        'team_id',
        'fbib_match_id',
        'home_team',
        'away_team',
        'competition',
        'match_date',
        'match_time',
        'match_datetime',
        'round',
        'import_month',
        'status',
        'pavilion',
        'score_home',
        'score_away',
        'locker_room_id',
        'notes',
        'is_imported',
        'import_data',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
            'match_datetime' => 'datetime',
            'is_imported' => 'boolean',
            'import_data' => 'array',
        ];
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function lockerRoom()
    {
        return $this->belongsTo(LockerRoom::class);
    }

    public function getFormattedMatchTimeAttribute(): ?string
    {
        $time = $this->getAttribute('match_time');

        if ($time === null || $time === '') {
            return null;
        }

        return substr((string) $time, 0, 5);
    }
}
