<?php

use App\Models\Configuration\FulfillmentProvider;
use App\Models\Configuration\FulfillmentProviderCredential;
use App\Models\User;
use App\Support\FulfillmentConfigurationBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('fulfillment providers are loaded from the database catalog', function () {
    $provider = FulfillmentProvider::findBySlug('contraentrega');

    expect($provider)->not->toBeNull()
        ->and($provider->label)->toBe('CONTRAENTREGA')
        ->and(FulfillmentConfigurationBridge::providersForFrontend())->toHaveCount(1)
        ->and(FulfillmentConfigurationBridge::providersForFrontend()[0]['slug'])->toBe('contraentrega')
        ->and(FulfillmentConfigurationBridge::providersForFrontend()[0]['credentialsConfigured'])->toBeFalse();
});

test('root user can store fulfillment provider credentials in the database', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.fulfillment-providers.credential.update', 'contraentrega'), [
            'api_url' => 'https://api.contraentrega.test',
            'user' => 'fulfillment-user',
            'pass' => 'secret-pass',
        ])
        ->assertRedirect(route('configuration.fulfillment-providers.edit'));

    $credential = FulfillmentProviderCredential::findForProvider('contraentrega');

    expect($credential)->not->toBeNull()
        ->and($credential->hasStoredCredentials())->toBeTrue()
        ->and($credential->credentials['api_url'])->toBe('https://api.contraentrega.test')
        ->and($credential->credentials['user'])->toBe('fulfillment-user')
        ->and($credential->credentials['pass'])->toBe('secret-pass')
        ->and(FulfillmentConfigurationBridge::providerHasAvailableCredentials('contraentrega'))->toBeTrue();
});

test('unknown fulfillment providers cannot be updated', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.fulfillment-providers.credential.update', 'unknown-provider'), [
            'api_url' => 'https://api.example.test',
            'user' => 'user',
            'pass' => 'pass',
        ])
        ->assertNotFound();
});
