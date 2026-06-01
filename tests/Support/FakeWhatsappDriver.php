<?php

namespace Tests\Support;

use App\Contracts\WhatsappDriver;
use App\Whatsapp\WhatsappIncomingMessage;
use Illuminate\Http\Request;

class FakeWhatsappDriver implements WhatsappDriver
{
    public bool $verifyShouldFail = false;

    public ?WhatsappIncomingMessage $messageToParse = null;

    /** @var list<array{to: string, body: string}> */
    public array $sentTextMessages = [];

    /** @var list<array{to: string, mediaUrl: string, caption: string}> */
    public array $sentMediaMessages = [];

    public function verifyWebhook(Request $request): void
    {
        if ($this->verifyShouldFail) {
            abort(403, 'Invalid Twilio signature');
        }
    }

    public function parseIncomingMessage(Request $request): ?WhatsappIncomingMessage
    {
        return $this->messageToParse;
    }

    public function sendTextMessage(string $to, string $body): void
    {
        $this->sentTextMessages[] = [
            'to' => $to,
            'body' => $body,
        ];
    }

    public function sendMediaMessage(string $to, string $mediaUrl, string $caption = ''): void
    {
        $this->sentMediaMessages[] = [
            'to' => $to,
            'mediaUrl' => $mediaUrl,
            'caption' => $caption,
        ];
    }
}
