<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Services\Fulfillment\ContraEntrega\ContraEntregaService;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaAddressData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaCustomerData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaOrderData;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaProductData;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ContraEntregaTestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            $service = new ContraEntregaService;
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        $order = new ContraEntregaOrderData(
            orderNumber: 'TEST-'.now()->format('YmdHis'),
            customer: new ContraEntregaCustomerData(
                names: 'Irwing Naranjo',
                phone: 937200000,
                phonePrefix: '+52',
            ),
            deliveryAddress: new ContraEntregaAddressData(
                country: 'Narnia',
                state: 'Archenland',
                city: 'Cair Paravel',
                district: 'Palacio',
                streetPreffix: 'Calle',
                streetName: 'Comercio',
                houseNumber: '123',
                zipCode: '0000',
                reference: 'Casa azul, portón negro',
            ),
            products: [
                new ContraEntregaProductData(
                    productSku: 'P1001',
                    quantity: 1,
                    productName: 'Producto default',
                ),
            ],
            deliveryDate: now()->addDays(3),
            totalPrice: 100.00,
            currency: 'USD',
            serviceType: 'full',
        );

        try {
            $service->createOrder($order);

            return response()->json([
                'success' => true,
                'order_number' => $order->orderNumber,
                'payload_sent' => $order->toArray(),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 502);
        }
    }
}
