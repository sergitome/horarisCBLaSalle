<?php

namespace App\Http\Requests\LockerRooms;

use App\Models\LockerRoom;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLockerRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('locker_room'));
    }

    public function rules(): array
    {
        /** @var LockerRoom $lockerRoom */
        $lockerRoom = $this->route('locker_room');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:locker_rooms,name,'.$lockerRoom->id],
            'description' => ['nullable', 'string', 'max:255'],
            'pavilion' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
