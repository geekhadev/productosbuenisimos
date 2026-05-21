<?php

namespace App\Http\Requests\Sales;

use App\Enums\Sales\LeadStatus;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    protected function prepareForValidation(): void
    {
        $customerId = $this->input('customer_id');

        if ($customerId === '' || $customerId === null) {
            $this->merge(['customer_id' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);
        assert(is_string($companyId) && $companyId !== '');

        return [
            'status' => ['required', 'string', Rule::in(LeadStatus::values())],
            'customer_id' => [
                'nullable',
                'uuid',
                Rule::exists('sales_customers', 'id')
                    ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
        ];
    }

    /**
     * @return array{status: string, customer_id: ?string}
     */
    public function leadPayload(): array
    {
        $validated = $this->validated();

        return [
            'status' => $validated['status'],
            'customer_id' => $validated['customer_id'] ?? null,
        ];
    }
}
