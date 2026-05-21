<?php

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers sales leads module', function () {
    expect(Permission::query()->where('slug', 'sales.leads.list')->exists())
        ->toBeTrue();
});

test('store creates lead with manual source by default', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.leads.store'), [
            'phone' => '+56911112222',
        ])
        ->assertRedirect(route('sales.leads.index'));

    $lead = Lead::query()->where('phone', '+56911112222')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->company_id)->toBe($company->id)
        ->and($lead->source)->toBe(LeadSource::Manual)
        ->and($lead->status)->toBe(LeadStatus::Nuevo)
        ->and($lead->customer_id)->toBeNull();
});

test('store creates lead with explicit source', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.leads.store'), [
            'phone' => '+56933334444',
            'source' => 'whatsapp',
        ])
        ->assertRedirect(route('sales.leads.index'));

    $lead = Lead::query()->where('phone', '+56933334444')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->source)->toBe(LeadSource::Whatsapp);
});

test('index lists leads for selected company only', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();

    Lead::factory()->for($companyA)->create(['phone' => '+56910000001']);
    Lead::factory()->for($companyB)->create(['phone' => '+56910000002']);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.leads.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('sales/leads/index')
        ->has('data.data', 1)
        ->where('data.data.0.phone', '+56910000001'));
});

test('index filters by phone search', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    Lead::factory()->for($company)->create(['phone' => '+56911111111']);
    Lead::factory()->for($company)->create(['phone' => '+56922222222']);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.leads.index', ['search' => '2222']))
        ->assertInertia(fn ($page) => $page
            ->has('data.data', 1)
            ->where('data.data.0.phone', '+56922222222'));
});

test('index filters by status and source', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    Lead::factory()->for($company)->create([
        'phone' => '+56910000010',
        'source' => LeadSource::Web,
        'status' => LeadStatus::Nuevo,
    ]);
    Lead::factory()->for($company)->create([
        'phone' => '+56910000011',
        'source' => LeadSource::Manual,
        'status' => LeadStatus::Contactado,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.leads.index', [
            'status' => 'nuevo',
            'source' => 'web',
        ]))
        ->assertInertia(fn ($page) => $page
            ->has('data.data', 1)
            ->where('data.data.0.phone', '+56910000010'));
});

test('edit loads lead for form', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $lead = Lead::factory()->for($company)->create(['phone' => '+56955556666']);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.leads.edit', $lead))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('sales/leads/form')
            ->where('lead.id', $lead->id)
            ->where('lead.phone', '+56955556666'));
});

test('update changes status and customer link', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create();
    $lead = Lead::factory()->for($company)->create([
        'status' => LeadStatus::Nuevo,
        'customer_id' => null,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->patch(route('sales.leads.update', $lead), [
            'status' => 'convertido',
            'customer_id' => $customer->id,
        ])
        ->assertRedirect(route('sales.leads.index'));

    $lead->refresh();
    expect($lead->status)->toBe(LeadStatus::Convertido)
        ->and($lead->customer_id)->toBe($customer->id);
});

test('update clears customer link when empty', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create();
    $lead = Lead::factory()->for($company)->create([
        'status' => LeadStatus::Convertido,
        'customer_id' => $customer->id,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->patch(route('sales.leads.update', $lead), [
            'status' => 'contactado',
            'customer_id' => '',
        ])
        ->assertRedirect(route('sales.leads.index'));

    $lead->refresh();
    expect($lead->status)->toBe(LeadStatus::Contactado)
        ->and($lead->customer_id)->toBeNull();
});

test('update rejects customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customerB = Customer::factory()->for($companyB)->create();
    $lead = Lead::factory()->for($companyA)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->patch(route('sales.leads.update', $lead), [
            'status' => 'convertido',
            'customer_id' => $customerB->id,
        ])
        ->assertSessionHasErrors('customer_id');

    expect($lead->fresh()->customer_id)->toBeNull();
});

test('destroy soft deletes lead', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $lead = Lead::factory()->for($company)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->delete(route('sales.leads.destroy', $lead))
        ->assertRedirect(route('sales.leads.index'));

    expect(Lead::query()->find($lead->id))->toBeNull();
    expect(Lead::withTrashed()->find($lead->id))->not->toBeNull();
});

test('edit returns not found for lead from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();
    $lead = Lead::factory()->for($companyB)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.leads.edit', $lead))
        ->assertNotFound();
});

test('same phone can create multiple leads for company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $phone = '+56999990000';

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.leads.store'), ['phone' => $phone])
        ->assertRedirect(route('sales.leads.index'));

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.leads.store'), ['phone' => $phone])
        ->assertRedirect(route('sales.leads.index'));

    expect(Lead::query()->where('company_id', $company->id)->where('phone', $phone)->count())->toBe(2);
});
