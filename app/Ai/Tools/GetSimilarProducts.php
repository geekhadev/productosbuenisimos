<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetSimilarProductsAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSimilarProducts implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_similar_products';
    }

    public function description(): Stringable|string
    {
        return 'Devuelve los productos similares asociados a los productos de un pedido. '
            .'Úsalo SOLO después de registrar el pedido principal con create_order (Paso 12). '
            .'Nunca lo invoques antes de cerrar el pedido. '
            .'Excluye automáticamente los productos que el cliente ya está comprando.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new GetSimilarProductsAction)->execute($this->companyId, $request->toArray());

        if (isset($result['error'])) {
            return json_encode($result, JSON_THROW_ON_ERROR);
        }

        if ($result === []) {
            return 'No hay productos similares configurados para los productos de este pedido.';
        }

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_ids' => $schema
                ->array()
                ->description('Lista de UUIDs de los productos que el cliente está comprando.')
                ->min(1)
                ->required()
                ->items($schema->string()->description('UUID del producto.')),
        ];
    }
}
