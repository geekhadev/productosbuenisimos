<?php

namespace App\Whatsapp\Drivers;

use App\Contracts\WhatsappDriver;
use App\Whatsapp\WhatsappIncomingMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

class TwilioWhatsappDriver implements WhatsappDriver
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $fromNumber,
        private bool $shouldVerifyWebhook = true,
    ) {}

    public function verifyWebhook(Request $request): void
    {
        if (! $this->shouldVerifyWebhook) {
            return;
        }

        $validator = new RequestValidator($this->authToken);
        $url = $request->fullUrl();
        $params = $request->post();
        $signature = $request->header('X-Twilio-Signature', '');

        if (! $validator->validate($signature, $url, $params)) {
            Log::warning('Invalid Twilio WhatsApp webhook signature', [
                'url' => $url,
            ]);

            abort(403, 'Invalid Twilio signature');
        }
    }

    public function parseIncomingMessage(Request $request): ?WhatsappIncomingMessage
    {
        if (! $request->has('From') || ! $request->has('MessageSid')) {
            return null;
        }

        $rawFrom = (string) $request->input('From');
        $phone = str_replace('whatsapp:', '', $rawFrom);
        $rawTo = (string) $request->input('To', '');
        $toNumber = str_replace('whatsapp:', '', $rawTo);

        return new WhatsappIncomingMessage(
            phone: $phone,
            body: (string) $request->input('Body', ''),
            messageId: (string) $request->input('MessageSid'),
            rawFrom: $rawFrom,
            toNumber: $toNumber,
        );
    }

    public function sendTextMessage(string $to, string $body): void
    {
        $client = new Client($this->accountSid, $this->authToken);

        foreach ($this->splitMessage($body, 4096) as $chunk) {
            $client->messages->create($this->formatWhatsappAddress($to), [
                'from' => $this->fromNumber,
                'body' => $chunk,
            ]);
        }
    }

    public function sendMediaMessage(string $to, string $mediaUrl, string $caption = ''): void
    {
        $client = new Client($this->accountSid, $this->authToken);

        $client->messages->create($this->formatWhatsappAddress($to), [
            'from' => $this->fromNumber,
            'body' => $caption,
            'mediaUrl' => [$mediaUrl],
        ]);
    }

    /**
     * @return list<string>
     */
    private function splitMessage(string $body, int $maxLength): array
    {
        if (mb_strlen($body) <= $maxLength) {
            return [$body];
        }

        $chunks = [];
        $remaining = $body;

        while (mb_strlen($remaining) > $maxLength) {
            $segment = mb_substr($remaining, 0, $maxLength);
            $breakAt = mb_strrpos($segment, "\n\n");

            if ($breakAt === false || $breakAt < (int) ($maxLength * 0.5)) {
                $breakAt = mb_strrpos($segment, ' ');
            }

            if ($breakAt === false || $breakAt < (int) ($maxLength * 0.5)) {
                $breakAt = $maxLength;
            }

            $chunks[] = trim(mb_substr($remaining, 0, $breakAt));
            $remaining = ltrim(mb_substr($remaining, $breakAt));
        }

        if ($remaining !== '') {
            $chunks[] = $remaining;
        }

        return $chunks;
    }

    private function formatWhatsappAddress(string $to): string
    {
        if (str_starts_with($to, 'whatsapp:')) {
            return $to;
        }

        return "whatsapp:{$to}";
    }
}
