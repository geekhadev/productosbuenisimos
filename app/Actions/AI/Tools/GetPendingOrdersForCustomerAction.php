<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Order;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class GetPendingOrdersForCustomerAction
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return array<int, array<string, mixed>>|array{error: mixed}
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
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $orders = Order::query()
            ->where('company_id', $companyId)
            ->where('customer_id', $parameters['customer_id'])
            ->whereNull('fulfillment_sent_at')
            ->with(['address', 'items.product'])
            ->orderByDesc('created_at')
            ->get();

        return $orders->map(fn (Order $order) => $order->toArray())->all();
    }
}
