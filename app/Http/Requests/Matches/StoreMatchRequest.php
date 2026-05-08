<?php

namespace App\Http\Requests\Matches;

use App\Models\ClubMatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ClubMatch::class);
    }

    public function rules(): array
    {
        return [
            'season_id' => ['required', 'exists:seasons,id'],
            'team_id' => ['required', 'exists:teams,id'],
            'fbib_match_id' => ['nullable', 'integer', 'unique:matches,fbib_match_id'],
            'home_team' => ['required', 'string', 'max:255'],
            'away_team' => ['required', 'string', 'max:255'],
            'competition' => ['nullable', 'string', 'max:255'],
            'match_date' => ['nullable', 'date'],
            'match_time' => ['nullable', 'date_format:H:i'],
            'match_datetime' => [
                'nullable',
                'date',
                Rule::unique('matches', 'match_datetime')->where(fn ($query) => $query
                    ->where('team_id', $this->integer('team_id'))
                    ->where('home_team', $this->string('home_team')->toString())
                    ->where('away_team', $this->string('away_team')->toString())),
            ],
            'round' => ['nullable', 'string', 'max:255'],
            'import_month' => ['nullable', 'integer', 'between:1,12'],
            'status' => ['nullable', 'string', 'max:255'],
            'pavilion' => ['nullable', 'string', 'max:255'],
            'score_home' => ['nullable', 'integer', 'min:0'],
            'score_away' => ['nullable', 'integer', 'min:0'],
            'locker_room_id' => ['nullable', 'exists:locker_rooms,id'],
            'notes' => ['nullable', 'string'],
            'is_imported' => ['nullable', 'boolean'],
        ];
    }
}
