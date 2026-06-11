<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetPendingOrdersForCustomerAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetPendingOrdersForCustomer implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_pending_orders_for_customer';
    }

    public function description(): Stringable|string
    {
        return 'Devuelve los pedidos del cliente que aún no han sido enviados a fulfillment (logística). '
            .'Úsalo para verificar si existe un pedido abierto al que se puedan agregar productos de venta cruzada.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new GetPendingOrdersForCustomerAction)->execute($this->companyId, $request->toArray());

        if (isset($result['error'])) {
            return json_encode($result, JSON_THROW_ON_ERROR);
        }

        if ($result === []) {
            return 'El cliente no tiene pedidos pendientes de envío a fulfillment.';
        }

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_id' => $schema
                ->string()
                ->description('UUID del cliente cuyos pedidos pendientes se desean consultar.')
                ->required(),
        ];
    }
}
