<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\UpdateConversationTagAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateConversationTag implements Tool
{
    public function __construct(
        private readonly string $companyId,
        private readonly string $chatbotConversationId,
    ) {}

    public function name(): string
    {
        return 'update_conversation_tag';
    }

    public function description(): Stringable|string
    {
        return 'Registra en qué etapa del embudo de ventas se encuentra el cliente en esta conversación. '
            .'Invoca este tool cada vez que el cliente avance a una nueva etapa. '
            .'Usa el id de la etiqueta obtenido de get_conversation_tags.';
    }

    public function handle(Request $request): Stringable|string
    {
        $tagId = (string) $request->get('tag_id');

        $updated = (new UpdateConversationTagAction)->execute(
            chatbotConversationId: $this->chatbotConversationId,
            tagId: $tagId,
            companyId: $this->companyId,
        );

        return $updated ? 'ok' : 'error: etiqueta no encontrada';
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tag_id' => $schema
                ->string()
                ->description('UUID de la etiqueta de conversión a asignar. Obtenlo de get_conversation_tags.')
                ->required(),
        ];
    }
}
