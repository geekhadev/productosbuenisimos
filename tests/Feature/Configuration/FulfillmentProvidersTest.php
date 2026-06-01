<?php

use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Configuration\FulfillmentProviderCredential;
use App\Models\User;
use App\Support\FulfillmentConfigurationBridge;
use Database\Seeders\Administration\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers configuration fulfillment providers module', function () {
    expect(Permission::query()->where('slug', 'configuration.fulfillment-providers.list')->exists())->toBeTrue()
        ->and(Permission::query()->where('slug', 'configuration.fulfillment-providers.update')->exists())->toBeTrue();
});

test('edit page renders fulfillment providers configuration', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.fulfillment-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/fulfillment-providers/edit')
            ->where('can.update', true)
            ->has('providers', count(FulfillmentProviderCredential::manageableProviderSlugs()))
            ->where('providers.0.slug', 'contraentrega')
            ->where('providers.0.label', 'CONTRAENTREGA'));
});

test('update credential stores encrypted credentials metadata and applies config', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.fulfillment-providers.credential.update', ['provider' => 'contraentrega']), [
            'api_url' => 'https://api.contraentrega.test',
            'user' => 'integration-user',
            'pass' => 'secret-pass-value',
        ])
        ->assertRedirect(route('configuration.fulfillment-providers.edit'));

    $credential = FulfillmentProviderCredential::findForProvider('contraentrega');

    expect($credential)->not->toBeNull()
        ->and($credential->credentials)->toBe([
            'api_url' => 'https://api.contraentrega.test',
            'user' => 'integration-user',
            'pass' => 'secret-pass-value',
        ])
        ->and($credential->pass_last_chars)->toBe('···alue')
        ->and($credential->credentials_updated_at)->not->toBeNull();

    FulfillmentConfigurationBridge::apply();

    expect(config('fulfillment.providers.contraentrega.api_url'))->toBe('https://api.contraentrega.test')
        ->and(config('fulfillment.providers.contraentrega.user'))->toBe('integration-user')
        ->and(config('fulfillment.providers.contraentrega.pass'))->toBe('secret-pass-value');
});

test('provider is not marked configured when only env has credentials', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    config([
        'fulfillment.providers.contraentrega.api_url' => 'https://env.example.com',
        'fulfillment.providers.contraentrega.user' => 'env-user',
        'fulfillment.providers.contraentrega.pass' => 'env-pass',
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.fulfillment-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/fulfillment-providers/edit')
            ->where('providers.0.slug', 'contraentrega')
            ->where('providers.0.credentialsConfigured', false)
            ->where('providers.0.configuredViaEnvironment', true));
});

test('edit page exposes pass hint and updated at after credential is stored', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    FulfillmentProviderCredential::query()->create([
        'provider' => 'contraentrega',
        'credentials' => [
            'api_url' => 'https://api.contraentrega.test',
            'user' => 'stored-user',
            'pass' => 'stored-pass',
        ],
        'pass_last_chars' => FulfillmentProviderCredential::hintFromPass('stored-pass'),
        'credentials_updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.fulfillment-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/fulfillment-providers/edit')
            ->where('providers.0.slug', 'contraentrega')
            ->where('providers.0.credentialsConfigured', true)
            ->where('providers.0.apiUrl', 'https://api.contraentrega.test')
            ->where('providers.0.user', 'stored-user')
            ->where('providers.0.passLastChars', '···pass')
            ->where('providers.0.configuredViaEnvironment', false)
            ->has('providers.0.credentialsUpdatedAt'));
});

test('update credential can change api url and user without resubmitting pass', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    FulfillmentProviderCredential::query()->create([
        'provider' => 'contraentrega',
        'credentials' => [
            'api_url' => 'https://old.example.com',
            'user' => 'old-user',
            'pass' => 'unchanged-pass',
        ],
        'pass_last_chars' => FulfillmentProviderCredential::hintFromPass('unchanged-pass'),
        'credentials_updated_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.fulfillment-providers.credential.update', ['provider' => 'contraentrega']), [
            'api_url' => 'https://updated.example.com',
            'user' => 'updated-user',
        ])
        ->assertRedirect(route('configuration.fulfillment-providers.edit'));

    $credential = FulfillmentProviderCredential::findForProvider('contraentrega');

    expect($credential?->credentials)->toBe([
        'api_url' => 'https://updated.example.com',
        'user' => 'updated-user',
        'pass' => 'unchanged-pass',
    ])
        ->and($credential?->pass_last_chars)->toBe('···pass')
        ->and($credential?->credentials_updated_at?->isToday())->toBeTrue();
});

test('update credential replaces previous credentials and refreshes metadata', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    FulfillmentProviderCredential::query()->create([
        'provider' => 'contraentrega',
        'credentials' => [
            'api_url' => 'https://old.example.com',
            'user' => 'old-user',
            'pass' => 'old-pass-value',
        ],
        'pass_last_chars' => '···alue',
        'credentials_updated_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.fulfillment-providers.credential.update', ['provider' => 'contraentrega']), [
            'api_url' => 'https://new.example.com',
            'user' => 'new-user',
            'pass' => 'brand-new-pass',
        ])
        ->assertRedirect(route('configuration.fulfillment-providers.edit'));

    $credential = FulfillmentProviderCredential::findForProvider('contraentrega');

    expect($credential?->credentials)->toBe([
        'api_url' => 'https://new.example.com',
        'user' => 'new-user',
        'pass' => 'brand-new-pass',
    ])
        ->and($credential?->pass_last_chars)->toBe('···pass')
        ->and($credential?->credentials_updated_at?->isToday())->toBeTrue();
});
