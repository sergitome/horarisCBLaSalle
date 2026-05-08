<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportExecution extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'import_club_id',
        'started_at',
        'finished_at',
        'result',
        'created_count',
        'updated_count',
        'skipped_count',
        'error_count',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'details' => 'array',
        ];
    }

    public function importClub()
    {
        return $this->belongsTo(ImportClub::class);
    }
}
