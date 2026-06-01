<?php

use App\Ai\Agents\SalesAgent;
use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Support\ChatbotCompany;
use App\Support\ChatbotPhone;
use App\Whatsapp\WhatsappIncomingMessage;
use App\Whatsapp\WhatsappMessageProcessor;
use Laravel\Ai\Ai;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
    Ai::fakeAgent(SalesAgent::class, ['Respuesta del agente por WhatsApp']);
});

test('new phone creates conversation and returns reply with whatsapp source', function () {
    $processor = app(WhatsappMessageProcessor::class);

    $result = $processor->process(new WhatsappIncomingMessage(
        phone: '+56912345678',
        body: 'Quiero comprar',
        messageId: 'SM-new-phone',
        rawFrom: 'whatsapp:+56912345678',
        toNumber: '+14155238886',
    ));

    expect($result->reply)->toBe('Respuesta del agente por WhatsApp')
        ->and($result->attachments)->toBe([]);

    $conversation = ChatbotConversation::query()
        ->where('phone', ChatbotPhone::normalize('+56912345678'))
        ->first();

    expect($conversation)->not->toBeNull()
        ->and($conversation->source)->toBe(ChatbotSource::Whatsapp)
        ->and($conversation->is_active)->toBeTrue();

    $messages = ChatbotMessage::query()
        ->where('chatbot_conversation_id', $conversation->id)
        ->get();

    expect($messages)->toHaveCount(2)
        ->and($messages->firstWhere('role', ChatbotMessageRole::User)?->source)->toBe(ChatbotSource::Whatsapp)
        ->and($messages->firstWhere('role', ChatbotMessageRole::Assistant)?->source)->toBe(ChatbotSource::Whatsapp);
});

test('existing active conversation reuses conversation without creating a new one', function () {
    $company = ChatbotCompany::findOrFail();
    $phone = ChatbotPhone::normalize('+56912345678');

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create([
            'phone' => $phone,
            'source' => ChatbotSource::Whatsapp,
            'is_active' => true,
        ]);

    app(WhatsappMessageProcessor::class)->process(new WhatsappIncomingMessage(
        phone: '+56912345678',
        body: 'Seguimiento',
        messageId: 'SM-existing-phone',
        rawFrom: 'whatsapp:+56912345678',
        toNumber: '+14155238886',
    ));

    expect(ChatbotConversation::query()->where('company_id', $company->id)->where('phone', $phone)->count())->toBe(1)
        ->and(ChatbotMessage::query()->where('chatbot_conversation_id', $conversation->id)->count())->toBe(2);
});

test('empty body returns text-only reply without calling the agent', function () {
    $processor = app(WhatsappMessageProcessor::class);

    $result = $processor->process(new WhatsappIncomingMessage(
        phone: '+56912345678',
        body: '',
        messageId: 'SM-empty-body',
        rawFrom: 'whatsapp:+56912345678',
        toNumber: '+14155238886',
    ));

    expect($result->reply)->toBe(
        'Por el momento solo puedo atender mensajes de texto. Por favor escríbeme tu consulta.',
    );

    expect(ChatbotMessage::query()->count())->toBe(0);
});
