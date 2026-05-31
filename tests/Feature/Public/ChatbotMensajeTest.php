<?php

use App\Ai\Agents\SalesAgent;
use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Enums\Stock\ProductMediaType;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\ChatbotCompany;
use Laravel\Ai\Ai;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
    Ai::fakeAgent(SalesAgent::class, ['¡Hola! ¿En qué te ayudo?']);
});

test('active conversation message returns reply and stores web messages', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create([
        'is_active' => true,
        'phone' => '912345678',
    ]);

    $response = $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Quiero un pedido',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'reply' => '¡Hola! ¿En qué te ayudo?',
            'attachments' => [],
        ]);

    Ai::assertAgentWasPrompted(SalesAgent::class, function ($prompt) use ($conversation) {
        return str_contains($prompt->prompt, '[Contexto del visitante — chatbot público]')
            && str_contains($prompt->prompt, $conversation->phone)
            && str_contains($prompt->prompt, 'NO vuelvas a pedir el número de teléfono')
            && str_contains($prompt->prompt, 'Mensaje del visitante: Quiero un pedido');
    });

    expect(ChatbotMessage::query()->where('chatbot_conversation_id', $conversation->id)->count())->toBe(2);

    $userMessage = ChatbotMessage::query()
        ->where('chatbot_conversation_id', $conversation->id)
        ->where('role', ChatbotMessageRole::User)
        ->first();

    expect($userMessage?->content)->toBe('Quiero un pedido')
        ->and($userMessage?->source)->toBe(ChatbotSource::Web);
});

test('product context is sent to agent but only user message is stored', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);
    $product = Product::factory()->for($company)->create([
        'name' => 'Imán XL',
        'code' => 'CODE-1',
        'sku' => 'SKU-1',
        'price' => 9990,
    ]);

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Hola',
        'product_context' => [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'sku' => $product->sku,
            'price' => 9990,
        ],
    ])->assertSuccessful();

    Ai::assertAgentWasPrompted(SalesAgent::class, function ($prompt) use ($conversation) {
        return str_contains($prompt->prompt, '[Contexto del visitante — chatbot público]')
            && str_contains($prompt->prompt, $conversation->phone)
            && str_contains($prompt->prompt, '[Contexto del producto consultado]')
            && str_contains($prompt->prompt, 'Imán XL')
            && str_contains($prompt->prompt, 'Mensaje del visitante: Hola');
    });

    $stored = ChatbotMessage::query()
        ->where('chatbot_conversation_id', $conversation->id)
        ->where('role', ChatbotMessageRole::User)
        ->value('content');

    expect($stored)->toBe('Hola');
});

test('assistant reply includes video attachment when presenting a matched product', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);
    $product = Product::factory()->for($company)->create([
        'name' => 'Imanes de Neodimio Autoadhesivos',
        'is_active' => true,
    ]);

    ProductMedia::factory()->create([
        'product_id' => $product->id,
        'type' => ProductMediaType::Video,
        'path' => 'products/'.$product->id.'/videos/demo.mp4',
        'mime_type' => 'video/mp4',
        'original_name' => 'demo.mp4',
    ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::User,
        'source' => ChatbotSource::Web,
        'content' => 'quiero 2 imanes',
    ]);

    Ai::fakeAgent(
        SalesAgent::class,
        ['Con gusto, Don Irwing. Le comparto el video de funcionamiento de los imanes. [Ver Video](#)'],
    );

    $response = $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'irwing',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('attachments.0.type', 'video')
        ->assertJsonPath('attachments.0.url', '/storage/products/'.$product->id.'/videos/demo.mp4');

    expect($response->json('reply'))->not->toContain('[Ver Video](#)');

    $assistantMessage = ChatbotMessage::query()
        ->where('chatbot_conversation_id', $conversation->id)
        ->where('role', ChatbotMessageRole::Assistant)
        ->latest('created_at')
        ->first();

    expect($assistantMessage?->attachments)->toHaveCount(1)
        ->and($assistantMessage?->content)->not->toContain('[Ver Video](#)');
});

test('message history preserves per-message source', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::User,
        'source' => ChatbotSource::Whatsapp,
        'content' => 'Desde WhatsApp',
    ]);

    $response = $this->postJson(route('chatbot.iniciar'), [
        'phone' => $conversation->phone,
        'source' => 'web',
    ]);

    $response->assertJsonPath('messages.0.source', 'whatsapp');
});

test('inactive conversation returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->inactive()->create();

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Hola',
    ])->assertUnprocessable();
});

test('conversation from another company returns 422', function () {
    $otherCompany = Company::factory()->create();
    $conversation = ChatbotConversation::factory()->for($otherCompany)->create(['is_active' => true]);

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Hola',
    ])->assertUnprocessable();
});

test('invalid source returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'invalid',
        'message' => 'Hola',
    ])->assertUnprocessable();
});

test('empty message returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => '',
    ])->assertUnprocessable();
});

test('message over 1000 characters returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => str_repeat('a', 1001),
    ])->assertUnprocessable();
});

test('partial product context returns 422', function () {
    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Hola',
        'product_context' => ['name' => 'Solo nombre'],
    ])->assertUnprocessable();
});

test('mensaje is throttled after twenty requests per minute', function () {
    $company = ChatbotCompany::findOrFail();

    for ($i = 0; $i < 20; $i++) {
        $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

        $this->postJson(route('chatbot.mensaje'), [
            'conversation_id' => $conversation->id,
            'source' => 'web',
            'message' => 'Mensaje '.$i,
        ])->assertSuccessful();
    }

    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Uno más',
    ])->assertStatus(429);
});
