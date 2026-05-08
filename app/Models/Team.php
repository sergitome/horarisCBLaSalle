<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'season_id',
        'import_club_id',
        'name',
        'display_name',
        'fbib_team_id',
        'category',
        'sponsor',
        'competition_group',
        'gender',
        'level',
        'active',
        'is_imported',
        'url_fbib',
        'import_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_imported' => 'boolean',
            'import_data' => 'array',
        ];
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function importClub()
    {
        return $this->belongsTo(ImportClub::class);
    }

    public function matches()
    {
        return $this->hasMany(ClubMatch::class, 'team_id');
    }
}
