<?php

namespace App\Http\Requests\ImportClubs;

use App\Models\ImportClub;
use Illuminate\Foundation\Http\FormRequest;

class UpdateImportClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('import_club'));
    }

    public function rules(): array
    {
        /** @var ImportClub $club */
        $club = $this->route('import_club');

        return [
            'name' => ['required', 'string', 'max:255'],
            'fbib_club_id' => ['required', 'integer', 'unique:import_clubs,fbib_club_id,'.$club->id],
            'url' => ['nullable', 'url'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
