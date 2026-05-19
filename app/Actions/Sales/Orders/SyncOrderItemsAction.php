<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Stock\Product;

class SyncOrderItemsAction
{
    /**
     * Reemplazo completo del detalle del pedido.
     *
     * @param  list<array{product_id: string, quantity: int, unit_price: string|float}>  $lines
     */
    public function execute(Order $order, array $lines): void
    {
        $order->items()->delete();

        foreach ($lines as $line) {
            $quantity = (int) $line['quantity'];
            $unitPrice = number_format((float) $line['unit_price'], 2, '.', '');
            $lineTotal = bcmul((string) $quantity, $unitPrice, 2);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $line['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }
    }

    /**
     * @param  list<array{product_id: string, quantity: int, unit_price?: string|float|null}>  $lines
     * @return list<array{product_id: string, quantity: int, unit_price: string}>
     */
    /**
     * @param  list<string>  $existingProductIds  Líneas históricas del pedido; no exigen producto activo.
     */
    public function normalizeLines(string $companyId, array $lines, array $existingProductIds = []): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            $productId = $line['product_id'];

            $query = Product::query()
                ->whereKey($productId)
                ->where('company_id', $companyId)
                ->whereNull('deleted_at');

            if (! in_array($productId, $existingProductIds, true)) {
                $query->where('is_active', true);
            }

            $product = $query->firstOrFail();

            $unitPrice = array_key_exists('unit_price', $line) && $line['unit_price'] !== null
                ? number_format((float) $line['unit_price'], 2, '.', '')
                : number_format((float) $product->price, 2, '.', '');

            $normalized[] = [
                'product_id' => $productId,
                'quantity' => (int) $line['quantity'],
                'unit_price' => $unitPrice,
            ];
        }

        return $normalized;
    }
}
