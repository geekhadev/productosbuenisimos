<?php

namespace App\Actions\Sales\Customers;

use App\Models\Sales\Customer;
use Illuminate\Support\Facades\DB;

class DeleteCustomerAction
{
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer): void {
            $customer->addresses()->delete();
            $customer->delete();
        });
    }
}
