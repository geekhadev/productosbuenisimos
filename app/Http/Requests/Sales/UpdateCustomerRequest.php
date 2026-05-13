<?php

namespace App\Http\Requests\Sales;

use App\Models\Sales\Customer;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);
        assert(is_string($companyId) && $companyId !== '');

        /** @var Customer $customer */
        $customer = $this->route('customer');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:40',
                Rule::unique('sales_customers', 'phone')
                    ->where(
                        fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at'),
                    )
                    ->ignore($customer->id),
            ],
            'addresses' => ['nullable', 'array', 'max:'.StoreCustomerRequest::MAX_ADDRESSES],
            'addresses.*.country_name' => ['nullable', 'string', 'max:120'],
            'addresses.*.state_name' => ['nullable', 'string', 'max:120'],
            'addresses.*.address' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{full_name: string, phone: string, addresses?: list<array{country_name: string, state_name: string, address: string}>}
     */
    public function customerPayload(): array
    {
        $validated = $this->validated();

        $payload = [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
        ];

        if ($this->has('addresses')) {
            $rows = isset($validated['addresses']) && is_array($validated['addresses'])
                ? $validated['addresses']
                : [];
            $addresses = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $addresses[] = [
                    'country_name' => (string) ($row['country_name'] ?? ''),
                    'state_name' => (string) ($row['state_name'] ?? ''),
                    'address' => (string) ($row['address'] ?? ''),
                ];
            }
            $payload['addresses'] = $addresses;
        }

        return $payload;
    }
}
