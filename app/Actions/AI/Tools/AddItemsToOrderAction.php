<?php

namespace App\Actions\AI\Tools;

use App\Actions\Sales\Orders\SyncOrderItemsAction;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AddItemsToOrderAction
{
    public function __construct(
        private SyncOrderItemsAction $syncOrderItems,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function execute(string $companyId, array $parameters): array
    {
        $validator = Validator::make($parameters, [
            'order_id' => [
                'required',
                'uuid',
                Rule::exists('sales_orders', 'id')
                    ->where(fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->whereNull('fulfillment_sent_at')),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'uuid',
                'distinct',
                Rule::exists('stock_products', 'id')
                    ->where(fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at')),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $validated = $validator->validated();

        /** @var Order $order */
        $order = Order::query()
            ->where('company_id', $companyId)
            ->with(['items.product'])
            ->findOrFail($validated['order_id']);

        return DB::transaction(function () use ($companyId, $order, $validated): array {
            $existingProductIds = $order->items->pluck('product_id')->all();

            // Build a map of existing lines keyed by product_id
            $mergedMap = [];
            foreach ($order->items as $item) {
                $mergedMap[$item->product_id] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ];
            }

            // Merge incoming items: sum quantities for existing products, append new ones
            foreach ($validated['items'] as $incoming) {
                $pid = $incoming['product_id'];
                if (isset($mergedMap[$pid])) {
                    $mergedMap[$pid]['quantity'] += (int) $incoming['quantity'];
                } else {
                    $mergedMap[$pid] = [
                        'product_id' => $pid,
                        'quantity' => (int) $incoming['quantity'],
                        'unit_price' => isset($incoming['unit_price']) ? (float) $incoming['unit_price'] : null,
                    ];
                }
            }

            $lines = $this->syncOrderItems->normalizeLines($companyId, array_values($mergedMap), $existingProductIds);
            $this->syncOrderItems->execute($order, $lines);

            $order->update([
                'total_amount' => $order->items()->sum('line_total'),
            ]);

            return $order->load(['items.product', 'customer', 'address'])->toArray();
        });
    }
}
