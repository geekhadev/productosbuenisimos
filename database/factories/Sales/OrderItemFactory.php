<?php

namespace Database\Factories\Sales;

use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Stock\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => bcmul((string) $quantity, (string) $unitPrice, 2),
        ];
    }
}
