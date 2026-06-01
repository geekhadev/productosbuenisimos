<?php

namespace App\Whatsapp\Drivers;

use App\Contracts\WhatsappDriver;
use App\Whatsapp\WhatsappIncomingMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

class TwilioWhatsappDriver implements WhatsappDriver
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $fromNumber,
        private bool $shouldVerifyWebhook = true,
    ) {
        $this->fromNumber = $this->formatWhatsappAddress($fromNumber);
    }

    public function verifyWebhook(Request $request): void
    {
        if (! $this->shouldVerifyWebhook) {
            Log::info('Twilio WhatsApp webhook signature verification skipped', [
                'verify_webhook' => false,
            ]);

            return;
        }

        $validator = new RequestValidator($this->authToken);
        $url = $request->fullUrl();
        $params = $request->post();
        $signature = $request->header('X-Twilio-Signature', '');

        if ($validator->validate($signature, $url, $params)) {
            Log::info('Twilio WhatsApp webhook signature verified', [
                'validation_url' => $url,
                'message_sid' => $request->input('MessageSid'),
            ]);

            return;
        }

        Log::warning('Invalid Twilio WhatsApp webhook signature', $this->webhookSignatureDiagnostics(
            request: $request,
            validator: $validator,
            validationUrl: $url,
            params: $params,
            signature: $signature,
        ));

        abort(403, 'Invalid Twilio signature');
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

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function webhookSignatureDiagnostics(
        Request $request,
        RequestValidator $validator,
        string $validationUrl,
        array $params,
        string $signature,
    ): array {
        $appUrl = rtrim((string) config('app.url'), '/');
        $httpValidationUrl = Str::startsWith($validationUrl, 'https://')
            ? 'http://'.Str::after($validationUrl, 'https://')
            : null;
        $appValidationUrl = $appUrl !== ''
            ? $appUrl.'/'.ltrim($request->path(), '/')
            : null;

        if ($appValidationUrl !== null && filled($request->getQueryString())) {
            $appValidationUrl .= '?'.$request->getQueryString();
        }

        return [
            'validation_url' => $validationUrl,
            'request_scheme' => $request->getScheme(),
            'request_host' => $request->getHost(),
            'request_path' => $request->path(),
            'request_url_without_query' => $request->url(),
            'query_string' => $request->getQueryString(),
            'forwarded_proto' => $request->header('X-Forwarded-Proto'),
            'forwarded_host' => $request->header('X-Forwarded-Host'),
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'cf_connecting_ip' => $request->header('CF-Connecting-IP'),
            'cf_visitor' => $request->header('CF-Visitor'),
            'signature_present' => $signature !== '',
            'signature_length' => strlen($signature),
            'post_param_keys' => array_keys($params),
            'message_sid' => $request->input('MessageSid'),
            'account_sid_suffix' => Str::substr($this->accountSid, -4),
            'auth_token_configured' => $this->authToken !== '',
            'auth_token_length' => strlen($this->authToken),
            'auth_token_suffix' => Str::substr($this->authToken, -4),
            'app_url' => config('app.url'),
            'would_validate_with_http_url' => $httpValidationUrl !== null
                && $validator->validate($signature, $httpValidationUrl, $params),
            'http_validation_url' => $httpValidationUrl,
            'would_validate_with_app_url' => $appValidationUrl !== null
                && $appValidationUrl !== $validationUrl
                && $validator->validate($signature, $appValidationUrl, $params),
            'app_validation_url' => $appValidationUrl !== $validationUrl ? $appValidationUrl : null,
        ];
    }
}
