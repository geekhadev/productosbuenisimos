<?php

namespace App\Http\Requests\Shared;

use App\Models\Shared\State;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var State $state */
        $state = $this->route('state');

        return [
            'country_id' => ['required', 'integer', Rule::exists('shared_countries', 'id')],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shared_states', 'name')
                    ->where(fn ($query) => $query->where('country_id', (int) $this->input('country_id')))
                    ->ignore($state),
            ],
        ];
    }

    /**
     * @return array{country_id: int, name: string}
     */
    public function statePayload(): array
    {
        /** @var array{country_id: int, name: string} */
        return [
            'country_id' => (int) $this->validated('country_id'),
            'name' => (string) $this->validated('name'),
        ];
    }
}
