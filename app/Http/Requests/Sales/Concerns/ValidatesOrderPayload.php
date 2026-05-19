<?php

namespace App\Http\Requests\Sales\Concerns;

use App\Models\Sales\Order;
use App\Models\Stock\Product;
use App\Support\SelectedCompanySession;
use Closure;
use Illuminate\Validation\Rule;

trait ValidatesOrderPayload
{
    /**
     * @return array<string, mixed>
     */
    protected function orderHeaderRules(?Order $order = null): array
    {
        $companyId = SelectedCompanySession::selectedCompanyId($this);
        assert(is_string($companyId) && $companyId !== '');

        $nameRule = Rule::unique('sales_orders', 'name')
            ->where(fn ($query) => $query->where('company_id', $companyId));

        if ($order !== null) {
            $nameRule = $nameRule->ignore($order->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', $nameRule],
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
                    ->where(fn ($query) => $query->where('customer_id', $this->input('customer_id'))),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  list<string>  $existingProductIds  Productos ya en el pedido (update); permiten inactivos.
     */
    protected function orderItemsRules(
        bool $required,
        bool $unitPriceRequired = false,
        array $existingProductIds = [],
    ): array {
        $companyId = SelectedCompanySession::selectedCompanyId($this);
        assert(is_string($companyId) && $companyId !== '');

        $itemsRules = $required ? ['required', 'array', 'min:1'] : ['sometimes', 'array', 'min:1'];

        $requireActiveOnly = $existingProductIds === [];

        $productExists = Rule::exists('stock_products', 'id')
            ->where(function ($query) use ($companyId, $requireActiveOnly): void {
                $query->where('company_id', $companyId)->whereNull('deleted_at');

                if ($requireActiveOnly) {
                    $query->where('is_active', true);
                }
            });

        $unitPriceRules = $unitPriceRequired
            ? ['required', 'numeric', 'min:0']
            : ['nullable', 'numeric', 'min:0'];

        $rules = [
            'items' => $itemsRules,
            'items.*.product_id' => ['required', 'uuid', 'distinct', $productExists],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => $unitPriceRules,
        ];

        if ($existingProductIds !== []) {
            $rules['items.*.product_id'][] = function (string $attribute, mixed $value, Closure $fail) use ($companyId, $existingProductIds): void {
                if (! is_string($value) || in_array($value, $existingProductIds, true)) {
                    return;
                }

                $active = Product::query()
                    ->whereKey($value)
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $active) {
                    $fail('El producto seleccionado no está activo.');
                }
            };
        }

        return $rules;
    }
}
