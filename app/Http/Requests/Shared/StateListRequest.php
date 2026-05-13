<?php

namespace App\Http\Requests\Shared;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Shared\State;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateListRequest extends FormRequest
{
    use InteractsWithPaginatedListQuery;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in([...State::SORTABLE_COLUMNS, 'country'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'country_id' => ['nullable', 'integer', Rule::exists('shared_countries', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        $countryRaw = $this->input('country_id');
        if ($countryRaw === '' || $countryRaw === null) {
            $this->merge(['country_id' => null]);
        } else {
            $this->merge(['country_id' => (int) $countryRaw]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return [
            ...$this->standardListFiltersForAction($validated),
            'country_id' => $validated['country_id'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        return [
            ...$this->standardListFiltersForFrontend(),
            'country_id' => $this->input('country_id') === null ? '' : (string) $this->input('country_id'),
        ];
    }
}
