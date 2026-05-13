<?php

namespace App\Actions\Sales\Customers;

use App\Models\Sales\Customer;
use Illuminate\Support\Facades\DB;

class UpdateCustomerAction
{
    public function __construct(
        private SyncCustomerAddressesAction $syncCustomerAddresses,
    ) {}

    /**
     * @param  array{full_name: string, phone: string, addresses?: list<array{country_name: string, state_name: string, address: string}>}  $attributes
     */
    public function execute(Customer $customer, array $attributes): Customer
    {
        return DB::transaction(function () use ($customer, $attributes): Customer {
            $customer->fill([
                'full_name' => $attributes['full_name'],
                'phone' => $attributes['phone'],
            ]);
            $customer->save();

            if (array_key_exists('addresses', $attributes)) {
                $this->syncCustomerAddresses->execute($customer, $attributes['addresses']);
            }

            $customer->load('addresses');

            return $customer;
        });
    }
}
