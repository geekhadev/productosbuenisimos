<?php

namespace App\Http\Requests\Administration;

use App\Http\Requests\Concerns\InteractsWithPaginatedListQuery;
use App\Models\Administration\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionListRequest extends FormRequest
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
            'sort' => ['nullable', 'string', Rule::in(Permission::SORTABLE_COLUMNS)],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
            'system_id' => ['nullable', 'uuid', Rule::exists('administration_systems', 'id')],
            'module_id' => [
                'nullable',
                'uuid',
                Rule::exists('administration_module', 'id')->when(
                    $this->filled('system_id'),
                    fn ($rule) => $rule->where('system_id', (string) $this->input('system_id')),
                ),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareStandardListQuery();

        $systemRaw = $this->input('system_id');
        if ($systemRaw === '' || $systemRaw === null) {
            $this->merge(['system_id' => null]);
        } else {
            $this->merge(['system_id' => (string) $systemRaw]);
        }

        $moduleRaw = $this->input('module_id');
        if ($moduleRaw === '' || $moduleRaw === null) {
            $this->merge(['module_id' => null]);
        } else {
            $this->merge(['module_id' => (string) $moduleRaw]);
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
                'module_id' => $validated['module_id'] ?? null,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersForFrontend(): array
    {
        $systemRaw = $this->input('system_id');
        $moduleRaw = $this->input('module_id');

        return array_merge($this->standardListFiltersForFrontend(), [
            'system_id' => $systemRaw === '' || $systemRaw === null ? null : $systemRaw,
            'module_id' => $moduleRaw === '' || $moduleRaw === null ? null : $moduleRaw,
        ]);
    }
}
