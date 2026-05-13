<?php

namespace App\Http\Requests\Administration;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Administration\Module;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuleListRequest extends FormRequest
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
            'sort' => ['nullable', 'string', Rule::in(Module::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'system_id' => ['nullable', 'uuid', Rule::exists('administration_systems', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        $raw = $this->input('system_id');
        if ($raw === '' || $raw === null) {
            $this->merge(['system_id' => null]);
        } else {
            $this->merge(['system_id' => (string) $raw]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return array_merge(
            $this->standardListFiltersForAction($validated),
            [
                'system_id' => $validated['system_id'] ?? null,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $raw = $this->input('system_id');

        return array_merge($this->standardListFiltersForFrontend(), [
            'system_id' => $raw === '' || $raw === null ? null : $raw,
        ]);
    }
}
