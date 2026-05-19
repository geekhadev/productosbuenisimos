<?php

use App\Ai\Agents\SalesAgent;
use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\GetProducts;
use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Sales\SalesAgentConfig;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers sales agent module', function () {
    expect(Permission::query()->where('slug', 'sales.agent.list')->exists())->toBeTrue()
        ->and(Permission::query()->where('slug', 'sales.agent.update')->exists())->toBeTrue();
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
            'prompt' => 'Instrucciones personalizadas.',
            'use_default_prompt' => false,
        ])
        ->assertRedirect(route('sales.agent.edit'));

    $config = SalesAgentConfig::query()->where('company_id', $company->id)->first();

    expect($config)->not->toBeNull()
        ->and($config->enabled_tools)->toBe([
            SalesAgentConfig::TOOL_GET_PRODUCTS,
            SalesAgentConfig::TOOL_CREATE_ORDER,
        ])
        ->and($config->prompt)->toBe('Instrucciones personalizadas.');
});

test('update with use default prompt clears stored prompt', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    SalesAgentConfig::factory()->for($company)->withCustomPrompt('Custom')->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.agent.update'), [
            'enabled_tools' => SalesAgentConfig::defaultEnabledTools(),
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

test('sales agent falls back to defaults without config', function () {
    $company = Company::factory()->create();
    $agent = SalesAgent::make(companyId: $company->id);

    expect($agent->instructions())->toContain('asistente de ventas');

    $toolClasses = array_map(fn ($tool) => $tool::class, iterator_to_array($agent->tools()));

    expect($toolClasses)->toContain(GetProducts::class, CreateCustomer::class);
});

test('guest cannot access agent config', function () {
    $this->get(route('sales.agent.edit'))->assertRedirect();
});
