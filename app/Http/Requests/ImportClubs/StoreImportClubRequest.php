<?php

namespace App\Http\Requests\ImportClubs;

use App\Models\ImportClub;
use Illuminate\Foundation\Http\FormRequest;

class StoreImportClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ImportClub::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'fbib_club_id' => ['required', 'integer', 'unique:import_clubs,fbib_club_id'],
            'url' => ['nullable', 'url'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
