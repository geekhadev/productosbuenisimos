<?php

namespace App\Actions\Sales\Conversations;

use App\Contracts\WhatsappDriver;
use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;

class SendOperatorMessageAction
{
    public function __construct(private readonly WhatsappDriver $driver) {}

    public function execute(ChatbotConversation $conversation, string $message): void
    {
        ChatbotMessage::query()->create([
            'chatbot_conversation_id' => $conversation->id,
            'role' => ChatbotMessageRole::Assistant,
            'source' => $conversation->source,
            'content' => $message,
            'created_at' => now(),
        ]);

        if ($conversation->source === ChatbotSource::Whatsapp) {
            $this->driver->sendTextMessage($conversation->phone, $message);
        }
    }
}
