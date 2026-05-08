<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportClub extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = ['name', 'fbib_club_id', 'url', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function importExecutions()
    {
        return $this->hasMany(ImportExecution::class);
    }
}
