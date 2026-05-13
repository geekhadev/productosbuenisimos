<?php

namespace App\Http\Requests\Administration;

use App\Models\Administration\Module;
use App\Models\Administration\System;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ModulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $module = $this->route('module');
        if ($module instanceof Module) {
            return $user->can('update', $module);
        }

        return $user->can('create', Module::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Module|null $module */
        $module = $this->route('module');
        $systemId = $this->input('system_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('administration_module', 'name')
                    ->where('system_id', $systemId)
                    ->ignore($module),
            ],
            'slug' => [
                'required',
                'string',
                'max:191',
                'alpha_dash',
            ],
            'system_id' => ['required', 'uuid', Rule::exists('administration_systems', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $systemId = $this->input('system_id');
            $segment = $this->string('slug')->toString();
            $system = is_string($systemId) ? System::query()->find($systemId) : null;
            if ($system === null) {
                return;
            }

            $full = Module::composeStoredSlug($system, $segment);

            /** @var Module|null $module */
            $module = $this->route('module');

            $exists = Module::query()
                ->where('slug', $full)
                ->when($module !== null, fn ($query) => $query->whereKeyNot($module->id))
                ->exists();

            if ($exists) {
                $validator->errors()->add('slug', __('That slug is already taken.'));
            }

            if (strlen($full) > 255) {
                $validator->errors()->add(
                    'slug',
                    __('The slug is too long for the selected system.'),
                );
            }
        });
    }

    /**
     * @return array{name: string, slug: string, system_id: string}
     */
    public function modulePayload(): array
    {
        /** @var array{name: string, slug: string, system_id: string} */
        return $this->safe()->only(['name', 'slug', 'system_id']);
    }
}
