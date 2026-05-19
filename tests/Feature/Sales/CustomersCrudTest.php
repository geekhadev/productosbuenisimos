<?php

use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers sales customers module', function () {
    expect(Permission::query()->where('slug', 'sales.customers.list')->exists())
        ->toBeTrue();
});

test('store creates customer with address rows', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.customers.store'), [
            'full_name' => 'María López',
            'phone' => '+56987654321',
            'addresses' => [
                [
                    'country_name' => 'Chile',
                    'state_name' => 'RM',
                    'address' => 'Av. Providencia 100',
                ],
            ],
        ]);

    $response->assertRedirect(route('sales.customers.index'));

    $customer = Customer::query()->where('phone', '+56987654321')->first();
    expect($customer)->not->toBeNull()
        ->and($customer->company_id)->toBe($company->id);

    $addresses = CustomerAddress::query()->where('customer_id', $customer->id)->get();
    expect($addresses)->toHaveCount(1)
        ->and($addresses[0]->country_name)->toBe('Chile')
        ->and($addresses[0]->state_name)->toBe('RM')
        ->and($addresses[0]->address)->toBe('Av. Providencia 100');
});

test('store creates customer without address when all address rows are empty', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.customers.store'), [
            'full_name' => 'Sin Dirección',
            'phone' => '+56900001111',
            'addresses' => [
                [
                    'country_name' => '',
                    'state_name' => '',
                    'address' => '',
                ],
            ],
        ])
        ->assertRedirect(route('sales.customers.index'));

    $customer = Customer::query()->where('phone', '+56900001111')->first();
    expect($customer)->not->toBeNull();
    expect(CustomerAddress::query()->where('customer_id', $customer->id)->count())->toBe(0);
});

test('store skips blank address rows and persists only rows with data', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.customers.store'), [
            'full_name' => 'Dos Direcciones',
            'phone' => '+56900002222',
            'addresses' => [
                [
                    'country_name' => '',
                    'state_name' => '',
                    'address' => '',
                ],
                [
                    'country_name' => 'Chile',
                    'state_name' => 'Valparaíso',
                    'address' => 'Calle 2',
                ],
            ],
        ])
        ->assertRedirect(route('sales.customers.index'));

    $customer = Customer::query()->where('phone', '+56900002222')->first();
    expect($customer)->not->toBeNull();

    $addresses = CustomerAddress::query()
        ->where('customer_id', $customer->id)
        ->orderBy('sort_order')
        ->get();

    expect($addresses)->toHaveCount(1)
        ->and($addresses[0]->state_name)->toBe('Valparaíso');
});

test('edit loads customer with addresses for the form', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create();
    CustomerAddress::factory()->for($customer)->create([
        'country_name' => 'Perú',
        'state_name' => 'Lima',
        'address' => 'Jr. Lima 1',
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.customers.edit', $customer));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('sales/customers/form')
        ->where('customer.id', $customer->id)
        ->has('customer.addresses', 1)
        ->where('customer.addresses.0.country_name', 'Perú')
        ->where('customer.addresses.0.state_name', 'Lima')
        ->where('customer.addresses.0.address', 'Jr. Lima 1'));
});

test('update replaces customer addresses when addresses payload is sent', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create([
        'full_name' => 'Cliente Original',
        'phone' => '+56933334444',
    ]);
    CustomerAddress::factory()->for($customer)->create([
        'country_name' => 'Chile',
        'state_name' => 'Antofagasta',
        'address' => 'Vieja',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->patch(route('sales.customers.update', $customer), [
            'full_name' => 'Cliente Actualizado',
            'phone' => '+56933334444',
            'addresses' => [
                [
                    'country_name' => 'Chile',
                    'state_name' => 'Biobío',
                    'address' => 'Nueva calle 9',
                ],
            ],
        ])
        ->assertRedirect(route('sales.customers.index'));

    $customer->refresh();
    expect($customer->full_name)->toBe('Cliente Actualizado');

    $addresses = CustomerAddress::query()->where('customer_id', $customer->id)->get();
    expect($addresses)->toHaveCount(1)
        ->and($addresses[0]->state_name)->toBe('Biobío')
        ->and($addresses[0]->address)->toBe('Nueva calle 9');
});

test('update with empty address rows removes all addresses', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create();
    CustomerAddress::factory()->for($customer)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->patch(route('sales.customers.update', $customer), [
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
            'addresses' => [
                [
                    'country_name' => '',
                    'state_name' => '',
                    'address' => '',
                ],
            ],
        ])
        ->assertRedirect(route('sales.customers.index'));

    expect(CustomerAddress::query()->where('customer_id', $customer->id)->count())->toBe(0);
});

test('destroy soft deletes customer and removes addresses', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($company)->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->delete(route('sales.customers.destroy', $customer))
        ->assertRedirect(route('sales.customers.index'));

    expect(Customer::query()->find($customer->id))->toBeNull();
    expect(Customer::withTrashed()->find($customer->id))->not->toBeNull();
    expect(CustomerAddress::query()->find($address->id))->toBeNull();
});

test('edit returns not found for customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($companyB)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.customers.edit', $customer))
        ->assertNotFound();
});

test('update returns not found for customer from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();
    $customer = Customer::factory()->for($companyB)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->patch(route('sales.customers.update', $customer), [
            'full_name' => 'Hack',
            'phone' => '+56999998888',
            'addresses' => [],
        ])
        ->assertNotFound();

    expect(Customer::query()->find($customer->id)?->full_name)->not->toBe('Hack');
});
