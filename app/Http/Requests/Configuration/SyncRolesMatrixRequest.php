<?php

namespace App\Http\Requests\Configuration;

use App\Enums\UserType;
use App\Models\Configuration\Role;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SyncRolesMatrixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        if (! is_array($roles)) {
            $this->merge(['roles' => []]);

            return;
        }

        $normalized = [];

        foreach ($roles as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $ids = $row['permission_ids'] ?? [];

            if (! is_array($ids)) {
                $ids = [];
            }

            $permIds = [];

            foreach ($ids as $id) {
                if (is_string($id) && Str::isUuid($id)) {
                    $permIds[] = $id;
                }
            }

            $roleId = $row['id'] ?? null;
            $normalizedRoleId = is_string($roleId) && Str::isUuid($roleId)
                ? $roleId
                : null;

            $normalized[] = [
                'id' => $normalizedRoleId,
                'name' => isset($row['name']) && is_string($row['name']) ? $row['name'] : '',
                'permission_ids' => array_values(array_unique($permIds)),
            ];
        }

        $this->merge(['roles' => $normalized]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = SelectedCompanySession::matrixCompanyId($this);
        $user = $this->user();

        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*.id' => [
                'required',
                'uuid',
                Rule::exists('configuration_roles', 'id')->where(function ($query) use ($companyId, $user): void {
                    $query->where(function ($q) use ($companyId): void {
                        $q->where('company_id', $companyId)
                            ->where('is_public', false);
                    });

                    if ($user?->type === UserType::Root) {
                        $query->orWhere(function ($q): void {
                            Role::constraintPublicGlobalEditable($q);
                        });
                    }
                }),
            ],
            'roles.*.name' => ['required', 'string', 'max:255'],
            'roles.*.permission_ids' => ['present', 'array'],
            // Sin `distinct` en `permission_ids.*`: en Laravel esa regla se aplica al conjunto
            // de todos los `roles.*.permission_ids.*` y prohibiría el mismo permiso en dos roles.
            'roles.*.permission_ids.*' => ['uuid', Rule::exists('administration_permissions', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var list<array{id: string, name: string, permission_ids: list<string>}> $rows */
            $rows = $this->input('roles', []);
            $names = [];

            foreach ($rows as $i => $row) {
                $key = mb_strtolower($row['name']);

                if (isset($names[$key])) {
                    $validator->errors()->add(
                        "roles.{$i}.name",
                        'Los nombres de rol deben ser únicos en la matriz.'
                    );

                    return;
                }

                $names[$key] = true;

                if (Role::query()
                    ->publicGlobal()
                    ->where('name', $row['name'])
                    ->where('id', '<>', $row['id'])
                    ->exists()) {
                    $validator->errors()->add(
                        "roles.{$i}.name",
                        'Este nombre coincide con otro rol público del sistema.'
                    );

                    return;
                }
            }

            $companyId = SelectedCompanySession::matrixCompanyId($this);

            if ($companyId === null) {
                return;
            }

            foreach ($rows as $i => $row) {
                $dupe = Role::query()
                    ->where('company_id', $companyId)
                    ->where('is_public', false)
                    ->where('name', $row['name'])
                    ->where('id', '<>', $row['id'])
                    ->exists();

                if ($dupe) {
                    $validator->errors()->add(
                        "roles.{$i}.name",
                        'Ya existe otro rol con este nombre en tu empresa.'
                    );

                    return;
                }
            }
        });
    }

    /**
     * @return list<array{id: string, name: string, permission_ids: list<string>}>
     */
    public function matrixRows(): array
    {
        /** @var list<array{id: string, name: string, permission_ids: list<string>}> */
        return $this->validated('roles');
    }
}
