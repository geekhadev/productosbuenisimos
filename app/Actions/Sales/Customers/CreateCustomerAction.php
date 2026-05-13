<?php

namespace App\Actions\Sales\Customers;

use App\Models\Sales\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomerAction
{
    public function __construct(
        private SyncCustomerAddressesAction $syncCustomerAddresses,
    ) {}

    /**
     * @param  array{full_name: string, phone: string, addresses: list<array{country_name: string, state_name: string, address: string}>}  $attributes
     */
    public function execute(string $companyId, array $attributes): Customer
    {
        return DB::transaction(function () use ($companyId, $attributes): Customer {
            $customer = Customer::query()->create([
                'company_id' => $companyId,
                'full_name' => $attributes['full_name'],
                'phone' => $attributes['phone'],
            ]);

            $this->syncCustomerAddresses->execute($customer, $attributes['addresses']);

            $customer->load('addresses');

            return $customer;
        });
    }
}
