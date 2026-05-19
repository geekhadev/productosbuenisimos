<?php

namespace App\Http\Requests\Sales;

use App\Http\Requests\Sales\Concerns\ValidatesOrderPayload;
use App\Support\SelectedCompanySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
        return array_merge(
            $this->orderHeaderRules(),
            $this->orderItemsRules(required: true),
        );
    }

    /**
     * @return array{name: string, customer_id: string, address_id: string, items: list<array{product_id: string, quantity: int, unit_price?: float|string|null}>}
     */
    public function orderPayload(): array
    {
        $validated = $this->validated();

        $items = [];
        foreach ($validated['items'] as $row) {
            $item = [
                'product_id' => (string) $row['product_id'],
                'quantity' => (int) $row['quantity'],
            ];

            if (array_key_exists('unit_price', $row) && $row['unit_price'] !== null) {
                $item['unit_price'] = $row['unit_price'];
            }

            $items[] = $item;
        }

        return [
            'name' => $validated['name'],
            'customer_id' => $validated['customer_id'],
            'address_id' => $validated['address_id'],
            'items' => $items,
        ];
    }
}
