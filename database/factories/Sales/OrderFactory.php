<?php

namespace Database\Factories\Sales;

use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::factory();
        $customer = Customer::factory()->for($company);

        return [
            'company_id' => $company,
            'name' => 'PED-'.fake()->unique()->numerify('######'),
            'customer_id' => $customer,
            'address_id' => CustomerAddress::factory()->for($customer),
            'total_amount' => 0,
        ];
    }
}
