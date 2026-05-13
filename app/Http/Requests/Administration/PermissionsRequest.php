<?php

namespace App\Http\Requests\Administration;

use App\Models\Administration\Module;
use App\Models\Administration\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $permission = $this->route('permission');
        if ($permission instanceof Permission) {
            return $user->can('update', $permission);
        }

        return $user->can('create', Permission::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Permission|null $permission */
        $permission = $this->route('permission');
        $moduleId = $this->input('module_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('administration_permissions', 'name')
                    ->where('module_id', $moduleId)
                    ->ignore($permission),
            ],
            'slug' => [
                'required',
                'string',
                'max:191',
                'alpha_dash',
            ],
            'module_id' => ['required', 'uuid', Rule::exists('administration_module', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $moduleId = (string) $this->input('module_id');
            $segment = $this->string('slug')->toString();
            $module = Module::query()->find($moduleId);
            if ($module === null) {
                return;
            }

            $full = Permission::composeStoredSlug($module, $segment);

            /** @var Permission|null $permission */
            $permission = $this->route('permission');

            $exists = Permission::query()
                ->where('slug', $full)
                ->when($permission !== null, fn ($query) => $query->whereKeyNot($permission->id))
                ->exists();

            if ($exists) {
                $validator->errors()->add('slug', __('That slug is already taken.'));
            }

            if (strlen($full) > 255) {
                $validator->errors()->add(
                    'slug',
                    __('The slug is too long for the selected module.'),
                );
            }
        });
    }

    /**
     * @return array{name: string, slug: string, module_id: string}
     */
    public function permissionPayload(): array
    {
        /** @var array{name: string, slug: string, module_id: string} */
        return $this->safe()->only(['name', 'slug', 'module_id']);
    }
}
