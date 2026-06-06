<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;
use App\Services\Fulfillment\ContraEntrega\ContraEntregaService;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaAddressData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaCustomerData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaOrderData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaProductData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class SendOrdersToContraEntregaAction
{
    public function __construct(
        private ContraEntregaService $service,
    ) {}

    /**
     * @param  list<string>  $orderIds
     * @return list<array{order_id: string, name: string, success: bool, error: string|null}>
     */
    public function execute(string $companyId, array $orderIds): array
    {
        /** @var Collection<int, Order> $orders */
        $orders = Order::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $orderIds)
            ->with(['customer', 'address', 'items.product'])
            ->get();

        $results = [];

        foreach ($orders as $order) {
            $results[] = $this->sendOrder($order);
        }

        return $results;
    }

    /**
     * @return array{order_id: string, name: string, success: bool, error: string|null}
     */
    private function sendOrder(Order $order): array
    {
        $base = ['order_id' => $order->id, 'name' => $order->name];

        if ($order->customer === null) {
            return [
                ...$base,
                'success' => false,
                'error' => 'El pedido no tiene cliente asignado.',
            ];
        }

        if ($order->items->isEmpty()) {
            return [
                ...$base,
                'success' => false,
                'error' => 'El pedido no tiene productos.',
            ];
        }

        try {
            $this->service->createOrder($this->buildOrderData($order));

            return [...$base, 'success' => true, 'error' => null];
        } catch (RuntimeException $e) {
            return [...$base, 'success' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildOrderData(Order $order): ContraEntregaOrderData
    {
        $customer = $order->customer;
        $address = $order->address;

        $products = $order->items
            ->map(fn ($item) => new ContraEntregaProductData(
                productSku: (string) ($item->product?->sku ?: $item->product?->code ?: $item->product?->name ?: 'SIN-SKU'),
                quantity: $item->quantity,
                productName: (string) ($item->product?->name ?? 'Producto'),
            ))
            ->values()
            ->all();

        return new ContraEntregaOrderData(
            orderNumber: $order->name,
            customer: new ContraEntregaCustomerData(
                names: $customer->full_name,
                phone: $this->digitsOnly($customer->phone),
                phonePrefix: '+52',
            ),
            deliveryAddress: new ContraEntregaAddressData(
                country: 'Mexico',
                state: 'Puebla',
                city: 'Puebla',
                district: '20 De Noviembre',
                streetPreffix: 'Calle',
                streetName: (string) ($address?->address ?? 'Sin dirección'),
                houseNumber: 'SN',
                zipCode: '72000',
                reference: '',
            ),
            products: $products,
            deliveryDate: Carbon::create(2030, 1, 1),
            totalPrice: (float) $order->total_amount,
            currency: 'MXN',
            serviceType: 'full',
        );
    }

    private function digitsOnly(string $phone): int
    {
        return (int) preg_replace('/\D/', '', $phone);
    }
}
