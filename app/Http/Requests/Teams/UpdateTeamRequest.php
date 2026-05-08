<?php

namespace App\Http\Requests\Teams;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('team'));
    }

    public function rules(): array
    {
        /** @var Team $team */
        $team = $this->route('team');

        return [
            'season_id' => ['required', 'exists:seasons,id'],
            'import_club_id' => ['nullable', 'exists:import_clubs,id'],
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'fbib_team_id' => [
                'nullable',
                'integer',
                Rule::unique('teams')
                    ->ignore($team->id)
                    ->where(fn ($query) => $query->where('season_id', $this->integer('season_id'))),
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'sponsor' => ['nullable', 'string', 'max:255'],
            'competition_group' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'is_imported' => ['nullable', 'boolean'],
            'url_fbib' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
