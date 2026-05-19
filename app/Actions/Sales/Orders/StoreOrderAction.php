<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class StoreOrderAction
{
    public function __construct(
        private SyncOrderItemsAction $syncOrderItems,
    ) {}

    /**
     * @param  array{name: string, customer_id: string, address_id: string, items: list<array{product_id: string, quantity: int, unit_price?: string|float|null}>}  $attributes
     */
    public function execute(string $companyId, array $attributes): Order
    {
        return DB::transaction(function () use ($companyId, $attributes): Order {
            $order = Order::query()->create([
                'company_id' => $companyId,
                'name' => $attributes['name'],
                'customer_id' => $attributes['customer_id'],
                'address_id' => $attributes['address_id'],
                'total_amount' => 0,
            ]);

            $lines = $this->syncOrderItems->normalizeLines($companyId, $attributes['items']);
            $this->syncOrderItems->execute($order, $lines);

            $order->update([
                'total_amount' => $order->items()->sum('line_total'),
            ]);

            $order->load(['items.product', 'customer', 'address']);

            return $order;
        });
    }
}
