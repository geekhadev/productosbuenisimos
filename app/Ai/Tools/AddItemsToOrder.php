<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\AddItemsToOrderAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AddItemsToOrder implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'add_items_to_order';
    }

    public function description(): Stringable|string
    {
        return 'Agrega productos a un pedido existente que aún no ha sido enviado a fulfillment. '
            .'Si el producto ya existe en el pedido, suma la cantidad. '
            .'Devuelve el pedido actualizado con el nuevo total como JSON.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = app(AddItemsToOrderAction::class)->execute($this->companyId, $request->toArray());

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema
                ->string()
                ->description('UUID del pedido al que se agregarán los productos. Debe estar pendiente de fulfillment.')
                ->required(),
            'items' => $schema
                ->array()
                ->description('Productos a agregar. Si el producto ya existe en el pedido se suma la cantidad.')
                ->min(1)
                ->required()
                ->items($schema->object([
                    'product_id' => $schema->string()->description('UUID del producto.')->required(),
                    'quantity' => $schema->integer()->description('Cantidad a agregar. Mínimo 1.')->min(1)->required(),
                    'unit_price' => $schema->number()->description('Precio unitario. Si se omite se usa el precio vigente del producto.')->nullable()->required(),
                ])),
        ];
    }
}
