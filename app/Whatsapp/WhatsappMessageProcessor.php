<?php

namespace App\Whatsapp;

use App\Actions\Public\IniciarChatbot;
use App\Actions\Public\SendChatbotMessage;
use App\Enums\ChatbotSource;

class WhatsappMessageProcessor
{
    private const TEXT_ONLY_REPLY = 'Por el momento solo puedo atender mensajes de texto. Por favor escríbeme tu consulta.';

    public function __construct(
        private IniciarChatbot $iniciarChatbot,
        private SendChatbotMessage $sendChatbotMessage,
    ) {}

    public function process(WhatsappIncomingMessage $message): WhatsappProcessingResult
    {
        $chatbot = $this->iniciarChatbot->execute(
            phone: $message->phone,
            source: ChatbotSource::Whatsapp,
        );

        if (trim($message->body) === '') {
            return new WhatsappProcessingResult(
                reply: self::TEXT_ONLY_REPLY,
                attachments: [],
            );
        }

        $response = $this->sendChatbotMessage->execute(
            conversationId: $chatbot['conversation_id'],
            source: ChatbotSource::Whatsapp,
            message: $message->body,
        );

        return new WhatsappProcessingResult(
            reply: $response['reply'],
            attachments: $response['attachments'],
        );
    }
}
