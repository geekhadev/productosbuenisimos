<?php

namespace App\Jobs;

use App\Contracts\WhatsappDriver;
use App\Models\Whatsapp\WhatsappProcessedMessage;
use App\Whatsapp\WhatsappIncomingMessage;
use App\Whatsapp\WhatsappMessageProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessIncomingWhatsappMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public WhatsappIncomingMessage $message,
    ) {
        $this->onQueue('whatsapp');
    }

    public function handle(
        WhatsappDriver $driver,
        WhatsappMessageProcessor $processor,
    ): void {
        if (WhatsappProcessedMessage::query()->where('message_id', $this->message->messageId)->exists()) {
            return;
        }

        $result = $processor->process($this->message);

        Log::info('WhatsApp chatbot response', [
            'phone' => $this->message->phone,
            'message_id' => $this->message->messageId,
            'has_reply' => $result->reply !== '',
            'attachments_count' => count($result->attachments),
            'attachments' => array_map(fn ($a) => [
                'type' => $a['type'] ?? null,
                'url' => $a['url'] ?? null,
                'product_name' => $a['product_name'] ?? null,
            ], $result->attachments),
        ]);

        if ($result->reply !== '') {
            $driver->sendTextMessage($this->message->phone, $result->reply);

            foreach ($result->attachments as $attachment) {
                if (in_array($attachment['type'] ?? '', ['video', 'image'], true)) {
                    $driver->sendMediaMessage(
                        to: $this->message->phone,
                        mediaUrl: $attachment['url'],
                        caption: $attachment['product_name'] ?? '',
                    );
                }
            }
        }

        WhatsappProcessedMessage::query()->create([
            'message_id' => $this->message->messageId,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('WhatsApp message processing failed', [
            'phone' => $this->message->phone,
            'messageId' => $this->message->messageId,
            'error' => $exception->getMessage(),
        ]);
    }
}
