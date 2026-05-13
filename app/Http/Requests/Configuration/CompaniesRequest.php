<?php

namespace App\Http\Requests\Configuration;

use App\Models\Company;
use App\Support\Validation\CompanyPayloadValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompaniesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(CompanyPayloadValidationRules::mergeNormalizedNullableFields([
            'alias' => $this->input('alias'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
        ]));
    }

    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $company = $this->route('company');
        if ($company instanceof Company) {
            return $user->can('update', $company);
        }

        return $user->can('create', Company::class);
    }

    /**
     * @return array<string, ValidationRule|array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        /** @var Company|null $company */
        $company = $this->route('company');

        $rules = CompanyPayloadValidationRules::rules(
            $company,
            $this->all(),
            '',
        );

        if ($company === null) {
            $rules['owner_id'] = ['prohibited'];
        }

        return $rules;
    }

    /**
     * @return array{document_type: string, document_number: string, name: string, alias: string|null, email: string|null, phone: string|null, address: string|null}|array{name: string, alias: string|null, email: string|null, phone: string|null, address: string|null}
     */
    public function companyPayload(): array
    {
        /** @var Company|null $company */
        $company = $this->route('company');

        /** @var array{document_type: string, document_number: string, name: string, alias: string|null, email: string|null, phone: string|null, address: string|null} $all */
        $all = $this->safe()->only([
            'document_type',
            'document_number',
            'name',
            'alias',
            'email',
            'phone',
            'address',
        ]);

        if ($company instanceof Company) {
            /** @var array{name: string, alias: string|null, email: string|null, phone: string|null, address: string|null} */
            return [
                'name' => $all['name'],
                'alias' => $all['alias'],
                'email' => $all['email'],
                'phone' => $all['phone'],
                'address' => $all['address'],
            ];
        }

        return $all;
    }
}
