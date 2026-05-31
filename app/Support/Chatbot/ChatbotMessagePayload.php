<?php

namespace App\Support\Chatbot;

use App\Models\Public\ChatbotMessage;

class ChatbotMessagePayload
{
    /**
     * @return array{
     *     role: string,
     *     source: string,
     *     content: string,
     *     attachments: list<array<string, mixed>>,
     *     created_at: string
     * }
     */
    public static function format(ChatbotMessage $message): array
    {
        return [
            'role' => $message->role->value,
            'source' => $message->source->value,
            'content' => $message->content,
            'attachments' => $message->attachments ?? [],
            'created_at' => $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
