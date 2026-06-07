<?php

namespace App\Actions\AI\Tools;

use App\Actions\Sales\Orders\StoreOrderAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateOrderAction
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function execute(string $companyId, array $parameters): array
    {
        $validator = Validator::make($parameters, [
            'customer_id' => [
                'required',
                'uuid',
                Rule::exists('sales_customers', 'id')
                    ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'address_id' => [
                'required',
                'uuid',
                Rule::exists('sales_customer_addresses', 'id')
                    ->where(fn ($query) => $query->where('customer_id', $parameters['customer_id'] ?? null)),
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('sales_orders', 'name')
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
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
            'delivery_date' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $validated = $validator->validated();
        $name = $validated['name'] ?? 'Pedido-'.Carbon::now()->format('Ymd-His');

        $order = app(StoreOrderAction::class)->execute($companyId, [
            'name' => $name,
            'customer_id' => $validated['customer_id'],
            'address_id' => $validated['address_id'],
            'delivery_date' => $validated['delivery_date'],
            'items' => $this->orderItems($validated['items']),
        ]);

        return $order->load(['customer', 'address', 'items.product'])->toArray();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{product_id: string, quantity: int, unit_price?: float|string}>
     */
    private function orderItems(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $item = [
                'product_id' => (string) $row['product_id'],
                'quantity' => (int) $row['quantity'],
            ];

            if (array_key_exists('unit_price', $row) && $row['unit_price'] !== null) {
                $item['unit_price'] = $row['unit_price'];
            }

            $items[] = $item;
        }

        return $items;
    }
}
