<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateOrderAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateOrder implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_order';
    }

    public function description(): Stringable|string
    {
        return 'Crea un nuevo pedido de venta para la empresa en sesión. '
            .'Recibe el cliente, la dirección de entrega y la lista de productos con cantidades. '
            .'Devuelve el pedido creado como JSON.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new CreateOrderAction)->execute($this->companyId, $request->toArray());

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
                ->description('UUID del cliente al que pertenece el pedido.')
                ->required(),
            'address_id' => $schema
                ->string()
                ->description('UUID de la dirección de entrega del cliente.')
                ->required(),
            'items' => $schema
                ->array()
                ->description('Lista de productos del pedido. Debe contener al menos un elemento.')
                ->min(1)
                ->required()
                ->items($schema->object([
                    'product_id' => $schema->string()->description('UUID del producto.')->required(),
                    'quantity' => $schema->integer()->description('Cantidad del producto. Mínimo 1.')->min(1)->required(),
                    'unit_price' => $schema->number()->description('Precio unitario. Si se omite se usa el precio vigente del producto.')->nullable()->required(),
                ])),
            'name' => $schema
                ->string()
                ->description('Nombre del pedido. Envía null para generar automáticamente como "Pedido-{YYYYMMDD-HHmmss}".')
                ->max(255)
                ->nullable()
                ->required(),
        ];
    }
}
