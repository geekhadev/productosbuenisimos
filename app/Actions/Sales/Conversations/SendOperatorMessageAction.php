<?php

namespace App\Actions\Sales\Conversations;

use App\Contracts\WhatsappDriver;
use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Support\ChatbotPhone;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOperatorMessageAction
{
    public function __construct(private readonly WhatsappDriver $driver) {}

    public function execute(ChatbotConversation $conversation, string $message): void
    {
        Log::info('[OperatorMessage] Saving message', [
            'conversation_id' => $conversation->id,
            'phone' => $conversation->phone,
            'source' => $conversation->source->value,
            'agent_paused' => $conversation->agent_paused,
            'message_length' => strlen($message),
        ]);

        ChatbotMessage::query()->create([
            'chatbot_conversation_id' => $conversation->id,
            'role' => ChatbotMessageRole::Assistant,
            'source' => $conversation->source,
            'content' => $message,
            'created_at' => now(),
        ]);

        Log::info('[OperatorMessage] Message saved to DB');

        if ($conversation->source !== ChatbotSource::Whatsapp) {
            Log::info('[OperatorMessage] Source is not WhatsApp, skipping send', [
                'source' => $conversation->source->value,
            ]);

            return;
        }

        $e164 = ChatbotPhone::toE164($conversation->phone);

        Log::info('[OperatorMessage] Calling WhatsappDriver::sendTextMessage', [
            'stored_phone' => $conversation->phone,
            'e164_phone' => $e164,
        ]);

        try {
            $this->driver->sendTextMessage($e164, $message);
            Log::info('[OperatorMessage] sendTextMessage completed successfully');
        } catch (Throwable $e) {
            Log::error('[OperatorMessage] sendTextMessage threw an exception', [
                'error' => $e->getMessage(),
                'class' => $e::class,
            ]);
            throw $e;
        }
    }
}
