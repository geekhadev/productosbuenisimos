<?php

namespace Database\Seeders\Sales;

use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::query()->first();

        if ($company === null) {
            return;
        }

        $rows = [
            ['full_name' => 'María Pérez', 'phone' => '+56911112222'],
            ['full_name' => 'Juan Soto', 'phone' => '+56922223333'],
            ['full_name' => 'Ana Contreras', 'phone' => '+56933334444'],
        ];

        foreach ($rows as $index => $row) {
            $customer = Customer::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'phone' => $row['phone'],
                ],
                [
                    'full_name' => $row['full_name'],
                ],
            );

            $customer->addresses()->delete();

            CustomerAddress::query()->create([
                'customer_id' => $customer->id,
                'sort_order' => 0,
                'country_name' => 'Chile',
                'state_name' => 'Región Metropolitana',
                'address' => 'Dirección principal '.($index + 1).', Santiago',
            ]);

            if ($index === 0) {
                CustomerAddress::query()->create([
                    'customer_id' => $customer->id,
                    'sort_order' => 1,
                    'country_name' => 'Chile',
                    'state_name' => 'Valparaíso',
                    'address' => 'Sucursal Viña del Mar 45',
                ]);
            }
        }
    }
}
