<?php

namespace Database\Seeders\Sales;

use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Stock\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->first();

        if ($company === null) {
            return;
        }

        $customer = Customer::query()
            ->where('company_id', $company->id)
            ->with('addresses')
            ->first();

        $product = Product::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->first();

        if ($customer === null || $product === null) {
            return;
        }

        $address = $customer->addresses->first()
            ?? CustomerAddress::query()->where('customer_id', $customer->id)->first();

        if ($address === null) {
            return;
        }

        $quantity = 2;
        $unitPrice = number_format((float) $product->price, 2, '.', '');
        $lineTotal = bcmul((string) $quantity, $unitPrice, 2);

        $order = Order::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'PED-DEMO-001',
            ],
            [
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'total_amount' => $lineTotal,
            ],
        );

        $order->items()->delete();

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ]);
    }
}
