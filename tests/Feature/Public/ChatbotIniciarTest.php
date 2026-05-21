<?php

use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Sales\Lead;
use App\Support\ChatbotCompany;
use App\Support\ChatbotPhone;
use Illuminate\Support\Str;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
});

test('new phone with web source creates active conversation with is_new true', function () {
    $response = $this->postJson(route('chatbot.iniciar'), [
        'phone' => '+56912345678',
        'source' => 'web',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'is_new' => true,
            'messages' => [],
        ]);

    $conversationId = $response->json('conversation_id');

    $conversation = ChatbotConversation::query()->findOrFail($conversationId);

    expect($conversation->phone)->toBe(ChatbotPhone::normalize('+56912345678'))
        ->and($conversation->source)->toBe(ChatbotSource::Web)
        ->and($conversation->is_active)->toBeTrue();

    $lead = Lead::query()
        ->where('company_id', ChatbotCompany::findOrFail()->id)
        ->where('phone', $conversation->phone)
        ->first();

    expect($lead)->not->toBeNull()
        ->and($lead->source->value)->toBe('web')
        ->and($lead->status)->toBe(LeadStatus::Nuevo);
});

test('resuming active conversation ensures lead exists without duplicating', function () {
    $company = ChatbotCompany::findOrFail();
    $phone = '912345678';
    $normalized = ChatbotPhone::normalize($phone);

    Lead::factory()->for($company)->create([
        'phone' => $normalized,
        'source' => LeadSource::Web,
    ]);

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create([
            'phone' => $normalized,
            'source' => ChatbotSource::Web,
            'is_active' => true,
        ]);

    $this->postJson(route('chatbot.iniciar'), [
        'phone' => $phone,
        'source' => 'web',
    ])->assertSuccessful();

    expect(Lead::query()->where('company_id', $company->id)->where('phone', $normalized)->count())->toBe(1);
});

test('same phone returns same conversation id and message history', function () {
    $company = ChatbotCompany::findOrFail();
    $phone = '912345678';

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create([
            'phone' => ChatbotPhone::normalize($phone),
            'source' => ChatbotSource::Web,
            'is_active' => true,
        ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::User,
        'source' => ChatbotSource::Web,
        'content' => 'Hola',
    ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::Assistant,
        'source' => ChatbotSource::Whatsapp,
        'content' => 'Respuesta previa',
    ]);

    $response = $this->postJson(route('chatbot.iniciar'), [
        'phone' => $phone,
        'source' => 'web',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'conversation_id' => $conversation->id,
            'is_new' => false,
        ])
        ->assertJsonCount(2, 'messages')
        ->assertJsonPath('messages.0.content', 'Hola')
        ->assertJsonPath('messages.0.source', 'web')
        ->assertJsonPath('messages.1.content', 'Respuesta previa')
        ->assertJsonPath('messages.1.source', 'whatsapp');
});

test('empty phone returns 422', function () {
    $this->postJson(route('chatbot.iniciar'), [
        'phone' => '',
        'source' => 'web',
    ])->assertUnprocessable();
});

test('invalid source returns 422', function () {
    $this->postJson(route('chatbot.iniciar'), [
        'phone' => '912345678',
        'source' => 'telegram',
    ])->assertUnprocessable();
});

test('iniciar is throttled after ten requests per minute', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson(route('chatbot.iniciar'), [
            'phone' => '9'.Str::padLeft((string) $i, 8, '0'),
            'source' => 'web',
        ])->assertSuccessful();
    }

    $this->postJson(route('chatbot.iniciar'), [
        'phone' => '999999999',
        'source' => 'web',
    ])->assertStatus(429);
});
