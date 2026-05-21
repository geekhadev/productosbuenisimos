<?php

use App\Enums\ChatbotSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Sales\Lead;
use App\Support\ChatbotCompany;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
});

test('active conversation is deactivated and new conversation is created', function () {
    $company = ChatbotCompany::findOrFail();

    $current = ChatbotConversation::factory()
        ->for($company)
        ->create([
            'phone' => '912345678',
            'source' => ChatbotSource::Web,
            'is_active' => true,
        ]);

    $response = $this->postJson(route('chatbot.nueva-conversacion'), [
        'conversation_id' => $current->id,
        'source' => 'web',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'is_new' => true,
            'messages' => [],
        ]);

    $newId = $response->json('conversation_id');

    expect($newId)->not->toBe($current->id)
        ->and($current->fresh()->is_active)->toBeFalse()
        ->and(ChatbotConversation::query()->findOrFail($newId)->is_active)->toBeTrue();

    $lead = Lead::query()
        ->where('company_id', $company->id)
        ->where('phone', $current->phone)
        ->first();

    expect($lead)->not->toBeNull()
        ->and($lead->status)->toBe(LeadStatus::Nuevo);
});

test('inactive conversation returns 422', function () {
    $company = ChatbotCompany::findOrFail();

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->inactive()
        ->create();

    $this->postJson(route('chatbot.nueva-conversacion'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
    ])->assertUnprocessable();
});

test('conversation from another company returns 422', function () {
    $otherCompany = Company::factory()->create();
    $conversation = ChatbotConversation::factory()
        ->for($otherCompany)
        ->create(['is_active' => true]);

    $this->postJson(route('chatbot.nueva-conversacion'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
    ])->assertUnprocessable();
});

test('invalid source returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $this->postJson(route('chatbot.nueva-conversacion'), [
        'conversation_id' => $conversation->id,
        'source' => 'invalid',
    ])->assertUnprocessable();
});

test('nueva conversacion is throttled after five requests per minute', function () {
    $company = ChatbotCompany::findOrFail();

    for ($i = 0; $i < 5; $i++) {
        $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

        $this->postJson(route('chatbot.nueva-conversacion'), [
            'conversation_id' => $conversation->id,
            'source' => 'web',
        ])->assertSuccessful();
    }

    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

    $this->postJson(route('chatbot.nueva-conversacion'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
    ])->assertStatus(429);
});
