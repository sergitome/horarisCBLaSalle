<?php

namespace App\Http\Requests\LockerRooms;

use App\Models\LockerRoom;
use Illuminate\Foundation\Http\FormRequest;

class StoreLockerRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LockerRoom::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:locker_rooms,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'pavilion' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
