<?php

namespace App\Http\Requests\Seasons;

use App\Models\Season;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('season'));
    }

    public function rules(): array
    {
        /** @var Season $season */
        $season = $this->route('season');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:seasons,name,'.$season->id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
