<?php

use App\Models\Configuration\WhatsappProvider;
use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\User;
use App\Support\WhatsappConfigurationBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('whatsapp providers are loaded from the database catalog', function () {
    expect(WhatsappProvider::manageableSlugs())->toHaveCount(2)
        ->and(WhatsappConfigurationBridge::providersForFrontend())->toHaveCount(2)
        ->and(WhatsappConfigurationBridge::providersForFrontend()[0]['credentialsConfigured'])->toBeFalse();
});

test('root user can store whatsapp provider credentials in the database', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.whatsapp-providers.credential.update', 'twilio'), [
            'account_sid' => 'AC123',
            'auth_token' => 'secret-token',
            'from_number' => 'whatsapp:+14155238886',
        ])
        ->assertRedirect(route('configuration.whatsapp-providers.edit'));

    $credential = WhatsappProviderCredential::findForProvider('twilio');

    expect($credential)->not->toBeNull()
        ->and($credential->hasStoredCredentials())->toBeTrue()
        ->and($credential->credentials['account_sid'])->toBe('AC123')
        ->and(WhatsappConfigurationBridge::providerHasAvailableCredentials('twilio'))->toBeTrue();
});

test('unknown whatsapp providers cannot be updated', function () {
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->put(route('configuration.whatsapp-providers.credential.update', 'unknown-provider'), [
            'account_sid' => 'AC123',
            'auth_token' => 'secret-token',
            'from_number' => 'whatsapp:+14155238886',
        ])
        ->assertNotFound();
});
