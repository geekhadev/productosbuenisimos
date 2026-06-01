<?php

use App\Ai\Agents\SalesAgent;
use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\GetProducts;
use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Configuration\AiProviderCredential;
use App\Models\Sales\SalesAgentConfig;
use App\Models\User;
use App\Support\AiConfigurationBridge;
use Database\Seeders\Administration\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    seedOpenAiProviderCredential();
});

test('permissions seeder registers sales agent module', function () {
    expect(Permission::query()->where('slug', 'sales.agent.list')->exists())->toBeTrue()
        ->and(Permission::query()->where('slug', 'sales.agent.update')->exists())->toBeTrue();
});

test('edit page lists every sales agent provider that has a stored credential', function () {
    AiProviderCredential::query()->create([
        'provider' => 'gemini',
        'credentials' => ['key' => 'gemini-key-12345678'],
        'key_last_chars' => AiProviderCredential::hintFromKey('gemini-key-12345678'),
        'key_updated_at' => now(),
    ]);

    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.agent.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sales/agent/edit')
            ->where('hasConfiguredProviders', true)
            ->has('providers', 2)
            ->where('providers', fn ($providers) => collect($providers)
                ->pluck('value')
                ->sort()
                ->values()
                ->all() === ['gemini', 'openai']));
});

test('edit page only lists providers with stored credentials', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    config(['ai.providers.openai.key' => 'sk-from-env-only']);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.agent.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sales/agent/edit')
            ->where('hasConfiguredProviders', true)
            ->has('providers', 1)
            ->where('providers.0.value', 'openai'));
});

test('edit page shows no providers when none have stored credentials', function () {
    AiProviderCredential::query()->delete();
    AiConfigurationBridge::apply();

    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.agent.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sales/agent/edit')
            ->where('hasConfiguredProviders', false)
            ->has('providers', 0));
});

test('edit page shows default tools and prompt when no config exists', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.agent.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sales/agent/edit')
            ->where('enabledTools', SalesAgentConfig::defaultEnabledTools())
            ->where('usesCustomPrompt', false)
            ->where('can.update', true)
            ->where('prompt', SalesAgentConfig::defaultPrompt())
            ->where('provider', SalesAgentConfig::defaultProvider())
            ->where('model', SalesAgentConfig::defaultModel())
            ->has('providerModels')
            ->has('tools', count(SalesAgentConfig::TOOL_SLUGS)));
});

test('update persists agent config for selected company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => [
                SalesAgentConfig::TOOL_GET_PRODUCTS,
                SalesAgentConfig::TOOL_CREATE_ORDER,
            ],
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => 'gpt-4o-mini',
            'prompt' => 'Instrucciones personalizadas.',
            'use_default_prompt' => false,
        ])
        ->assertRedirect(route('sales.agent.edit'));

    $config = SalesAgentConfig::query()->where('company_id', $company->id)->first();

    expect($config)->not->toBeNull()
        ->and($config->enabled_tools)->toBe(SalesAgentConfig::defaultEnabledTools())
        ->and($config->prompt)->toBe('Instrucciones personalizadas.')
        ->and($config->provider)->toBe(SalesAgentConfig::DEFAULT_PROVIDER)
        ->and($config->model)->toBe('gpt-4o-mini');
});

test('update always persists all tools even when a subset is submitted', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    SalesAgentConfig::factory()
        ->for($company)
        ->withEnabledTools([SalesAgentConfig::TOOL_GET_PRODUCTS])
        ->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => [SalesAgentConfig::TOOL_GET_PRODUCTS],
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => SalesAgentConfig::defaultModel(),
            'use_default_prompt' => true,
        ])
        ->assertRedirect(route('sales.agent.edit'));

    $config = SalesAgentConfig::query()->where('company_id', $company->id)->first();

    expect($config?->enabled_tools)->toBe(SalesAgentConfig::defaultEnabledTools());
});

test('update with use default prompt clears stored prompt', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    SalesAgentConfig::factory()->for($company)->withCustomPrompt('Custom')->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => SalesAgentConfig::defaultEnabledTools(),
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => SalesAgentConfig::defaultModel(),
            'prompt' => 'ignored',
            'use_default_prompt' => true,
        ])
        ->assertRedirect(route('sales.agent.edit'));

    $config = SalesAgentConfig::query()->where('company_id', $company->id)->first();

    expect($config?->prompt)->toBeNull();
});

test('update rejects disabling all tools', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => [],
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => SalesAgentConfig::defaultModel(),
            'use_default_prompt' => true,
        ])
        ->assertSessionHasErrors('enabled_tools');
});

test('sales agent uses company config for tools and instructions', function () {
    $company = Company::factory()->create();

    SalesAgentConfig::factory()
        ->for($company)
        ->withEnabledTools([SalesAgentConfig::TOOL_GET_PRODUCTS])
        ->withCustomPrompt('Prompt de prueba')
        ->create();

    $agent = SalesAgent::make(companyId: $company->id);

    expect($agent->instructions())->toBe('Prompt de prueba');

    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf(GetProducts::class);
});

test('sales agent uses provider and model from company config', function () {
    $company = Company::factory()->create();

    SalesAgentConfig::factory()
        ->for($company)
        ->create([
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => 'gpt-4o-mini',
        ]);

    $agent = SalesAgent::make(companyId: $company->id);

    expect($agent->provider())->toBe(SalesAgentConfig::DEFAULT_PROVIDER)
        ->and($agent->model())->toBe('gpt-4o-mini');
});

test('sales agent falls back to defaults without config', function () {
    $company = Company::factory()->create();
    $agent = SalesAgent::make(companyId: $company->id);

    expect($agent->instructions())->toContain('asesor de ventas')
        ->and($agent->provider())->toBe(SalesAgentConfig::defaultProvider())
        ->and($agent->model())->toBe(SalesAgentConfig::defaultModel());

    $toolClasses = array_map(fn ($tool) => $tool::class, iterator_to_array($agent->tools()));

    expect($toolClasses)->toContain(GetProducts::class, CreateCustomer::class);
});

test('update rejects invalid model for provider', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => SalesAgentConfig::defaultEnabledTools(),
            'provider' => SalesAgentConfig::DEFAULT_PROVIDER,
            'model' => 'gpt-4o',
            'use_default_prompt' => true,
        ])
        ->assertSessionHasErrors('model');
});

test('guest cannot access agent config', function () {
    $this->get(route('sales.agent.edit'))->assertRedirect();
});
