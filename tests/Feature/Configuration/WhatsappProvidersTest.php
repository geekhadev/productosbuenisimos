<?php

use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\User;
use App\Support\WhatsappConfigurationBridge;
use Database\Seeders\Administration\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers configuration whatsapp providers module', function () {
    expect(Permission::query()->where('slug', 'configuration.whatsapp-providers.list')->exists())->toBeTrue()
        ->and(Permission::query()->where('slug', 'configuration.whatsapp-providers.update')->exists())->toBeTrue();
});

test('edit page renders whatsapp providers configuration', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.whatsapp-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/whatsapp-providers/edit')
            ->where('can.update', true)
            ->has('providers', count(WhatsappProviderCredential::manageableProviderSlugs()))
            ->where('providers.0.slug', 'twilio')
            ->where('providers.0.label', 'Twilio')
            ->where('providers.1.slug', 'meta')
            ->where('providers.1.label', 'Meta'));
});

test('update twilio credential stores encrypted credentials metadata and applies config', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.whatsapp-providers.credential.update', ['provider' => 'twilio']), [
            'account_sid' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'auth_token' => 'twilio-auth-token-value',
            'from_number' => 'whatsapp:+14155238886',
        ])
        ->assertRedirect(route('configuration.whatsapp-providers.edit'));

    $credential = WhatsappProviderCredential::findForProvider('twilio');

    expect($credential)->not->toBeNull()
        ->and($credential->credentials)->toBe([
            'account_sid' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'auth_token' => 'twilio-auth-token-value',
            'from_number' => 'whatsapp:+14155238886',
        ])
        ->and($credential->secret_field_hints)->toBe([
            'auth_token' => '···alue',
        ])
        ->and($credential->credentials_updated_at)->not->toBeNull();

    WhatsappConfigurationBridge::apply();

    expect(config('whatsapp.twilio.account_sid'))->toBe('ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
        ->and(config('whatsapp.twilio.auth_token'))->toBe('twilio-auth-token-value')
        ->and(config('whatsapp.twilio.from_number'))->toBe('whatsapp:+14155238886');
});

test('update meta credential stores encrypted credentials metadata and applies config', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.whatsapp-providers.credential.update', ['provider' => 'meta']), [
            'access_token' => 'meta-access-token-value',
            'phone_number_id' => '123456789012345',
            'verify_token' => 'meta-verify-token',
            'app_secret' => 'meta-app-secret-value',
        ])
        ->assertRedirect(route('configuration.whatsapp-providers.edit'));

    $credential = WhatsappProviderCredential::findForProvider('meta');

    expect($credential)->not->toBeNull()
        ->and($credential->credentials)->toBe([
            'access_token' => 'meta-access-token-value',
            'phone_number_id' => '123456789012345',
            'verify_token' => 'meta-verify-token',
            'app_secret' => 'meta-app-secret-value',
        ])
        ->and($credential->secret_field_hints)->toBe([
            'access_token' => '···alue',
            'verify_token' => '···oken',
            'app_secret' => '···alue',
        ]);

    WhatsappConfigurationBridge::apply();

    expect(config('whatsapp.meta.access_token'))->toBe('meta-access-token-value')
        ->and(config('whatsapp.meta.phone_number_id'))->toBe('123456789012345')
        ->and(config('whatsapp.meta.verify_token'))->toBe('meta-verify-token')
        ->and(config('whatsapp.meta.app_secret'))->toBe('meta-app-secret-value');
});

test('provider is not marked configured when only env has credentials', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    config([
        'whatsapp.twilio.account_sid' => 'ACenvsid',
        'whatsapp.twilio.auth_token' => 'env-token',
        'whatsapp.twilio.from_number' => 'whatsapp:+14155238886',
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.whatsapp-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/whatsapp-providers/edit')
            ->where('providers.0.slug', 'twilio')
            ->where('providers.0.credentialsConfigured', false)
            ->where('providers.0.configuredViaEnvironment', true));
});

test('edit page exposes field metadata after twilio credential is stored', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    WhatsappProviderCredential::query()->create([
        'provider' => 'twilio',
        'credentials' => [
            'account_sid' => 'ACstoredsid',
            'auth_token' => 'stored-auth-token',
            'from_number' => 'whatsapp:+56912345678',
        ],
        'secret_field_hints' => [
            'auth_token' => WhatsappProviderCredential::hintFromSecret('stored-auth-token'),
        ],
        'credentials_updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('configuration.whatsapp-providers.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('configuration/whatsapp-providers/edit')
            ->where('providers.0.slug', 'twilio')
            ->where('providers.0.credentialsConfigured', true)
            ->where('providers.0.configuredViaEnvironment', false)
            ->has('providers.0.credentialsUpdatedAt')
            ->where('providers.0.fields.0.key', 'account_sid')
            ->where('providers.0.fields.0.value', 'ACstoredsid')
            ->where('providers.0.fields.1.key', 'auth_token')
            ->where('providers.0.fields.1.hint', '···oken')
            ->where('providers.0.fields.2.key', 'from_number')
            ->where('providers.0.fields.2.value', 'whatsapp:+56912345678'));
});

test('update twilio credential can change text fields without resubmitting secrets', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    WhatsappProviderCredential::query()->create([
        'provider' => 'twilio',
        'credentials' => [
            'account_sid' => 'AColdsid',
            'auth_token' => 'unchanged-auth-token',
            'from_number' => 'whatsapp:+14155238886',
        ],
        'secret_field_hints' => [
            'auth_token' => WhatsappProviderCredential::hintFromSecret('unchanged-auth-token'),
        ],
        'credentials_updated_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('configuration.whatsapp-providers.credential.update', ['provider' => 'twilio']), [
            'account_sid' => 'ACupdatedsid',
            'from_number' => 'whatsapp:+56987654321',
        ])
        ->assertRedirect(route('configuration.whatsapp-providers.edit'));

    $credential = WhatsappProviderCredential::findForProvider('twilio');

    expect($credential?->credentials)->toBe([
        'account_sid' => 'ACupdatedsid',
        'auth_token' => 'unchanged-auth-token',
        'from_number' => 'whatsapp:+56987654321',
    ])
        ->and($credential?->secret_field_hints)->toBe([
            'auth_token' => '···oken',
        ])
        ->and($credential?->credentials_updated_at?->isToday())->toBeTrue();
});
