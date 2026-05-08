<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockerRoom extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = ['name', 'description', 'pavilion', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function matches()
    {
        return $this->hasMany(ClubMatch::class, 'locker_room_id');
    }
}
