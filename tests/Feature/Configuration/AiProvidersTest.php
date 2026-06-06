<?php

use App\Models\Configuration\AiProvider;
use App\Models\Configuration\AiProviderCredential;
use App\Models\User;
use App\Support\AiConfigurationBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai providers are loaded from the database catalog', function () {
    expect(AiProvider::manageableSlugs())->toHaveCount(9)
        ->and(AiConfigurationBridge::providersForFrontend())->toHaveCount(9)
        ->and(AiConfigurationBridge::providersForFrontend()[0]['keyConfigured'])->toBeFalse();
});

test('root user can store ai provider credentials in the database', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.ai-providers.credential.update', 'openai'), [
            'key' => 'sk-test-openai-key',
        ])
        ->assertRedirect(route('configuration.ai-providers.edit'));

    $credential = AiProviderCredential::findForProvider('openai');

    expect($credential)->not->toBeNull()
        ->and($credential->hasStoredKey())->toBeTrue()
        ->and($credential->credentials['key'])->toBe('sk-test-openai-key')
        ->and(AiConfigurationBridge::providerHasAvailableKey('openai'))->toBeTrue();
});

test('unknown ai providers cannot be updated', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.ai-providers.credential.update', 'unknown-provider'), [
            'key' => 'sk-test-key',
        ])
        ->assertNotFound();
});
