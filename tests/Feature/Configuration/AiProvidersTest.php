<?php

use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Configuration\AiProviderCredential;
use App\Models\User;
use App\Support\AiConfigurationBridge;
use App\Support\ChatbotAiConfiguration;
use Database\Seeders\Administration\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers configuration ai providers module', function () {
    expect(Permission::query()->where('slug', 'configuration.ai-providers.list')->exists())->toBeTrue()
        ->and(Permission::query()->where('slug', 'configuration.ai-providers.update')->exists())->toBeTrue();
});

test('edit page renders ai providers configuration', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.ai-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/ai-providers/edit')
            ->where('can.update', true)
            ->has('providers', count(AiProviderCredential::manageableProviderSlugs()))
            ->missing('defaultProvider')
            ->missing('defaultProviderOptions'));
});

test('update credential stores encrypted key metadata and applies config', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.ai-providers.credential.update', ['provider' => 'openai']), [
            'key' => 'sk-test-openai-key',
        ])
        ->assertRedirect(route('configuration.ai-providers.edit'));

    $credential = AiProviderCredential::findForProvider('openai');

    expect($credential)->not->toBeNull()
        ->and($credential->credentials)->toBe(['key' => 'sk-test-openai-key'])
        ->and($credential->key_last_chars)->toBe('···-key')
        ->and($credential->key_updated_at)->not->toBeNull();

    AiConfigurationBridge::apply();

    expect(config('ai.providers.openai.key'))->toBe('sk-test-openai-key')
        ->and(ChatbotAiConfiguration::isConfigured())->toBeTrue();
});

test('provider is not marked configured when only env has a key', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    config(['ai.providers.openai.key' => 'sk-env-only']);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.ai-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/ai-providers/edit')
            ->where('providers.0.slug', 'openai')
            ->where('providers.0.keyConfigured', false)
            ->where('providers.0.configuredViaEnvironment', true));
});

test('edit page exposes key hint and updated at after credential is stored', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    AiProviderCredential::query()->create([
        'provider' => 'openai',
        'credentials' => ['key' => 'sk-existing-token'],
        'key_last_chars' => AiProviderCredential::hintFromKey('sk-existing-token'),
        'key_updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.ai-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/ai-providers/edit')
            ->where('providers.0.slug', 'openai')
            ->where('providers.0.keyConfigured', true)
            ->where('providers.0.keyLastChars', '···oken')
            ->where('providers.0.configuredViaEnvironment', false)
            ->has('providers.0.keyUpdatedAt'));
});

test('update credential replaces previous key and refreshes metadata', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    AiProviderCredential::query()->create([
        'provider' => 'openai',
        'credentials' => ['key' => 'sk-old-key-value'],
        'key_last_chars' => '···alue',
        'key_updated_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.ai-providers.credential.update', ['provider' => 'openai']), [
            'key' => 'sk-brand-new-key',
        ])
        ->assertRedirect(route('configuration.ai-providers.edit'));

    $credential = AiProviderCredential::findForProvider('openai');

    expect($credential?->credentials)->toBe(['key' => 'sk-brand-new-key'])
        ->and($credential?->key_last_chars)->toBe('···-key')
        ->and($credential?->key_updated_at?->isToday())->toBeTrue();
});
