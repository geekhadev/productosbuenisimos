<?php

namespace App\Http\Requests\Sales;

use App\Http\Requests\Sales\Concerns\ValidatesOrderPayload;
use App\Models\Sales\Order;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    use ValidatesOrderPayload;

    public function authorize(): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);

        return $companyId !== null && $companyId !== '';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');

        $existingProductIds = $order->items()->pluck('product_id')->all();

        return array_merge(
            $this->orderHeaderRules($order),
            $this->orderItemsRules(
                required: true,
                unitPriceRequired: true,
                existingProductIds: $existingProductIds,
            ),
        );
    }

    /**
     * @return array{name: string, customer_id: string, address_id: string, items?: list<array{product_id: string, quantity: int, unit_price?: float|string|null}>}
     */
    public function orderPayload(): array
    {
        $validated = $this->validated();

        $payload = [
            'name' => $validated['name'],
            'customer_id' => $validated['customer_id'],
            'address_id' => $validated['address_id'],
        ];

        $payload['items'] = $this->normalizeItemsFromValidated($validated['items'] ?? []);

        return $payload;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{product_id: string, quantity: int, unit_price?: float|string|null}>
     */
    private function normalizeItemsFromValidated(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

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
