<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetConversationTagsAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetConversationTags implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_conversation_tags';
    }

    public function description(): Stringable|string
    {
        return 'Devuelve las etiquetas de etapa de conversión configuradas por la empresa, '
            .'ordenadas por su orden de ejecución. Úsalas para identificar en qué etapa del embudo de ventas se encuentra el cliente.';
    }

    public function handle(Request $request): Stringable|string
    {
        $tags = (new GetConversationTagsAction)->execute($this->companyId);

        return json_encode($tags, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
