<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class UpdateOrderAction
{
    public function __construct(
        private SyncOrderItemsAction $syncOrderItems,
    ) {}

    /**
     * @param  array{name: string, customer_id: string, address_id: string, items?: list<array{product_id: string, quantity: int, unit_price: string|float}>}  $attributes
     */
    public function execute(Order $order, array $attributes): Order
    {
        return DB::transaction(function () use ($order, $attributes): Order {
            $order->fill([
                'name' => $attributes['name'],
                'customer_id' => $attributes['customer_id'],
                'address_id' => $attributes['address_id'],
            ]);
            $order->save();

            if (array_key_exists('items', $attributes)) {
                $existingProductIds = $order->items()->pluck('product_id')->all();
                $lines = $this->syncOrderItems->normalizeLines(
                    $order->company_id,
                    $attributes['items'],
                    $existingProductIds,
                );
                $this->syncOrderItems->execute($order, $lines);
            }

            $order->refresh();
            $order->update([
                'total_amount' => $order->items()->sum('line_total'),
            ]);

            $order->load(['items.product', 'customer', 'address']);

            return $order;
        });
    }
}
