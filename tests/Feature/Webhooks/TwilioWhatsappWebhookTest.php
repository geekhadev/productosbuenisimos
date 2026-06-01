<?php

use App\Ai\Agents\SalesAgent;
use App\Contracts\WhatsappDriver;
use App\Jobs\ProcessIncomingWhatsappMessage;
use App\Models\Company;
use App\Models\Whatsapp\WhatsappProcessedMessage;
use App\Support\ChatbotCompany;
use App\Whatsapp\WhatsappIncomingMessage;
use App\Whatsapp\WhatsappMessageProcessor;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Ai;
use Tests\Support\FakeWhatsappDriver;
use Twilio\Security\RequestValidator;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
    Ai::fakeAgent(SalesAgent::class, ['Respuesta del agente por WhatsApp']);
});

function twilioWebhookPayload(array $overrides = []): array
{
    return array_merge([
        'From' => 'whatsapp:+56912345678',
        'To' => 'whatsapp:+14155238886',
        'Body' => 'Hola, quiero información',
        'MessageSid' => 'SM'.fake()->uuid(),
    ], $overrides);
}

function signedTwilioWebhookRequest(array $payload, string $authToken = 'test-auth-token'): array
{
    config([
        'whatsapp.driver' => 'twilio',
        'whatsapp.twilio.account_sid' => 'ACtest',
        'whatsapp.twilio.auth_token' => $authToken,
        'whatsapp.twilio.from_number' => 'whatsapp:+14155238886',
        'whatsapp.verify_webhook' => true,
    ]);

    $url = route('webhook.whatsapp.twilio');
    $validator = new RequestValidator($authToken);

    return [
        'url' => $url,
        'payload' => $payload,
        'headers' => [
            'X-Twilio-Signature' => $validator->computeSignature($url, $payload),
        ],
    ];
}

test('valid webhook with correct signature dispatches job and returns 200', function () {
    Queue::fake();

    ['url' => $url, 'payload' => $payload, 'headers' => $headers] = signedTwilioWebhookRequest(
        twilioWebhookPayload(['MessageSid' => 'SM-valid-message']),
    );

    $this->post($url, $payload, $headers)
        ->assertSuccessful()
        ->assertSee('');

    Queue::assertPushed(ProcessIncomingWhatsappMessage::class, function (ProcessIncomingWhatsappMessage $job) {
        return $job->message->messageId === 'SM-valid-message'
            && $job->message->phone === '+56912345678'
            && $job->message->body === 'Hola, quiero información';
    });
});

test('invalid signature returns 403 and does not dispatch job', function () {
    Queue::fake();

    config([
        'whatsapp.driver' => 'twilio',
        'whatsapp.twilio.auth_token' => 'test-auth-token',
        'whatsapp.verify_webhook' => true,
    ]);

    $this->post(route('webhook.whatsapp.twilio'), twilioWebhookPayload(), [
        'X-Twilio-Signature' => 'invalid-signature',
    ])->assertForbidden();

    Queue::assertNothingPushed();
});

test('status update without from returns 200 without dispatching job', function () {
    Queue::fake();

    ['url' => $url, 'payload' => $payload, 'headers' => $headers] = signedTwilioWebhookRequest([
        'MessageSid' => 'SM-status-update',
        'MessageStatus' => 'delivered',
    ]);

    $this->post($url, $payload, $headers)
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('already processed message sid returns 200 without dispatching duplicate job', function () {
    Queue::fake();

    WhatsappProcessedMessage::query()->create([
        'message_id' => 'SM-already-processed',
    ]);

    ['url' => $url, 'payload' => $payload, 'headers' => $headers] = signedTwilioWebhookRequest(
        twilioWebhookPayload(['MessageSid' => 'SM-already-processed']),
    );

    $this->post($url, $payload, $headers)
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('empty body with media dispatches job that replies with text-only message', function () {
    $fakeDriver = new FakeWhatsappDriver;
    $this->app->instance(WhatsappDriver::class, $fakeDriver);

    $message = new WhatsappIncomingMessage(
        phone: '+56912345678',
        body: '',
        messageId: 'SM-empty-body',
        rawFrom: 'whatsapp:+56912345678',
        toNumber: '+14155238886',
    );

    (new ProcessIncomingWhatsappMessage($message))->handle(
        $fakeDriver,
        app(WhatsappMessageProcessor::class),
    );

    expect($fakeDriver->sentTextMessages)->toHaveCount(1)
        ->and($fakeDriver->sentTextMessages[0]['body'])->toBe(
            'Por el momento solo puedo atender mensajes de texto. Por favor escríbeme tu consulta.',
        );
});
