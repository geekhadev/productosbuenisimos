<?php

use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Support\ChatbotCompany;

beforeEach(function () {
    Company::factory()->create(['name' => ChatbotCompany::NAME]);
});

test('mensaje returns 503 when openai api key is not configured', function () {
    config(['ai.providers.openai.key' => null]);

    $company = ChatbotCompany::findOrFail();
    $conversation = ChatbotConversation::factory()->for($company)->create(['is_active' => true]);

    $this->postJson(route('chatbot.mensaje'), [
        'conversation_id' => $conversation->id,
        'source' => 'web',
        'message' => 'Hola',
    ])
        ->assertStatus(503)
        ->assertJsonPath('message', 'El asistente de ventas no está configurado. Contacta al administrador.');
});
