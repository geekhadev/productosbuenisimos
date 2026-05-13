<?php

namespace App\Http\Requests\Configuration;

use App\Actions\Configuration\Users\ListAssignableRolesForCompanyAction;
use App\Enums\UserType;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttachUserCompanyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->type === UserType::Owner) {
            $cid = SelectedCompanySession::selectedCompanyId($this);

            if (is_string($cid) && $cid !== '') {
                $this->merge(['company_id' => $cid]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'uuid', 'exists:configuration_companies,id'],
            'role_id' => ['required', 'uuid', 'exists:configuration_roles,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $companyId = (string) $this->input('company_id');
            $roleId = (string) $this->input('role_id');
            $allowed = app(ListAssignableRolesForCompanyAction::class)
                ->execute($companyId)
                ->pluck('id')
                ->map(fn ($id): string => (string) $id)
                ->all();

            if (! in_array($roleId, $allowed, true)) {
                $v->errors()->add('role_id', 'El rol no es válido para la empresa seleccionada.');
            }
        });
    }

    public function companyId(): string
    {
        return (string) $this->validated('company_id');
    }

    public function roleId(): string
    {
        return (string) $this->validated('role_id');
    }
}
