<?php

namespace App\Http\Requests\Sales;

use App\Enums\Sales\LeadSource;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('source')) {
            $this->merge(['source' => LeadSource::Manual->value]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:40'],
            'source' => ['required', 'string', Rule::in(LeadSource::values())],
        ];
    }

    /**
     * @return array{phone: string, source: string}
     */
    public function leadPayload(): array
    {
        $validated = $this->validated();

        return [
            'phone' => $validated['phone'],
            'source' => $validated['source'],
        ];
    }
}
