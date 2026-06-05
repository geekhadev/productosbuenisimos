<?php

namespace App\Whatsapp;

use App\Actions\Public\IniciarChatbot;
use App\Actions\Public\SendChatbotMessage;
use App\Actions\Whatsapp\TranscribeWhatsappAudio;
use App\Contracts\WhatsappDriver;
use App\Enums\ChatbotSource;
use App\Enums\WhatsappMessageType;

class WhatsappMessageProcessor
{
    private const TEXT_ONLY_REPLY = 'Por el momento solo puedo atender mensajes de texto. Por favor escríbeme tu consulta.';

    public function __construct(
        private IniciarChatbot $iniciarChatbot,
        private SendChatbotMessage $sendChatbotMessage,
        private WhatsappDriver $driver,
        private TranscribeWhatsappAudio $transcribe,
    ) {}

    public function process(WhatsappIncomingMessage $message): WhatsappProcessingResult
    {
        $chatbot = $this->iniciarChatbot->execute(
            phone: $message->phone,
            source: ChatbotSource::Whatsapp,
        );

        if ($message->type === WhatsappMessageType::Audio) {
            $audioContent = $this->driver->downloadMedia($message->audioUrl ?? '');
            $body = $this->transcribe->execute($audioContent, $message->audioMimeType ?? 'audio/ogg');

            $response = $this->sendChatbotMessage->execute(
                conversationId: $chatbot['conversation_id'],
                source: ChatbotSource::Whatsapp,
                message: $body,
                mediaType: 'audio',
            );

            return new WhatsappProcessingResult(
                reply: $response['reply'],
                attachments: $response['attachments'],
            );
        }

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
