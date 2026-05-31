<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetProductsAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProducts implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_products';
    }

    public function description(): Stringable|string
    {
        return 'Devuelve todos los productos activos del catálogo de la empresa '
            .'como JSON para que el agente pueda responder preguntas sobre disponibilidad, precios, imágenes y video.';
    }

    public function handle(Request $request): Stringable|string
    {
        $products = (new GetProductsAction)->execute($this->companyId);

        return json_encode($products, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
