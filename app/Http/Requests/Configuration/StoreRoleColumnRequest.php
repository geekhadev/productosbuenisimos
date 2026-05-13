<?php

namespace App\Http\Requests\Configuration;

use App\Models\Configuration\Role;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoleColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('permission_ids', []);

        if (! is_array($ids)) {
            $ids = [];
        }

        $normalized = [];

        foreach ($ids as $id) {
            if (is_string($id) && Str::isUuid($id)) {
                $normalized[] = $id;
            }
        }

        $this->merge([
            'name' => $this->filled('name') ? $this->input('name') : 'Nuevo rol',
            'permission_ids' => array_values(array_unique($normalized)),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = SelectedCompanySession::matrixCompanyId($this);

        $nameRules = [
            'required',
            'string',
            'max:255',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                if (Role::query()->publicGlobal()->where('name', $value)->exists()) {
                    $fail('Este nombre coincide con un rol público del sistema.');
                }
            },
        ];

        if ($companyId !== null) {
            $nameRules[] = Rule::unique('configuration_roles', 'name')
                ->where(
                    fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->where('is_public', false)
                );
        }

        return [
            'name' => $nameRules,
            'permission_ids' => ['present', 'array'],
            'permission_ids.*' => ['uuid', 'distinct', Rule::exists('administration_permissions', 'id')],
        ];
    }

    /**
     * @return array{name: string, permission_ids: list<string>}
     */
    public function payload(): array
    {
        /** @var array{name: string, permission_ids: list<string>} */
        return $this->safe()->only(['name', 'permission_ids']);
    }
}
