<?php

namespace App\Http\Requests\Administration;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Administration\System;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemListRequest extends FormRequest
{
    use InteractsWithPaginatedListQuery;

    /**
     * La autorización de listados se delega a las Policies vía controlador (`$this->authorize(...)`).
     */
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
            'sort' => ['nullable', 'string', Rule::in(System::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return $this->standardListFiltersForAction($validated);
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        return $this->standardListFiltersForFrontend();
    }
}
