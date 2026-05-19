<?php

namespace App\Actions\AI\Tools;

use App\Actions\Sales\Customers\CreateCustomerAction as SalesCreateCustomerAction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCustomerAction
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function execute(string $companyId, array $parameters): array
    {
        $validator = Validator::make($parameters, [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:40',
                Rule::unique('sales_customers', 'phone')->where(
                    fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at'),
                ),
            ],
            'addresses' => ['nullable', 'array', 'max:20'],
            'addresses.*.country_name' => ['nullable', 'string', 'max:120'],
            'addresses.*.state_name' => ['nullable', 'string', 'max:120'],
            'addresses.*.address' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $customer = app(SalesCreateCustomerAction::class)->execute(
            $companyId,
            $this->customerPayload($validator->validated()),
        );

        return $customer->load('addresses')->toArray();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{full_name: string, phone: string, addresses: list<array{country_name: string, state_name: string, address: string}>}
     */
    private function customerPayload(array $validated): array
    {
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

        return [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'addresses' => $addresses,
        ];
    }
}
