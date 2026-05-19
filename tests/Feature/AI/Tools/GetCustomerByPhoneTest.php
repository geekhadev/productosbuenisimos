<?php

use App\Actions\AI\Tools\GetCustomerByPhoneAction;
use App\Ai\Tools\GetCustomerByPhone;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

test('get customer by phone action returns customer with addresses for exact phone match', function () {
    $company = Company::factory()->create();

    $customer = Customer::factory()->for($company)->create([
        'full_name' => 'Juan Pérez',
        'phone' => '555-1234',
    ]);

    $address = CustomerAddress::factory()->for($customer)->create([
        'sort_order' => 1,
        'country_name' => 'México',
        'state_name' => 'Ciudad de México',
        'address' => 'Av. Insurgentes Sur 1234',
    ]);

    $result = (new GetCustomerByPhoneAction)->execute($company->id, '555-1234');

    expect($result)->not->toBeNull()
        ->and($result['id'])->toBe($customer->id)
        ->and($result['full_name'])->toBe('Juan Pérez')
        ->and($result['phone'])->toBe('555-1234')
        ->and($result['addresses'])->toHaveCount(1)
        ->and($result['addresses'][0]['id'])->toBe($address->id)
        ->and($result['addresses'][0]['customer_id'])->toBe($customer->id)
        ->and($result['addresses'][0]['sort_order'])->toBe(1)
        ->and($result['addresses'][0]['country_name'])->toBe('México')
        ->and($result['addresses'][0]['state_name'])->toBe('Ciudad de México')
        ->and($result['addresses'][0]['address'])->toBe('Av. Insurgentes Sur 1234');
});

test('get customer by phone action returns null when phone is not registered', function () {
    $company = Company::factory()->create();

    Customer::factory()->for($company)->create(['phone' => '555-1234']);

    expect((new GetCustomerByPhoneAction)->execute($company->id, '999-9999'))->toBeNull();
});

test('get customer by phone action does not match partial phone numbers', function () {
    $company = Company::factory()->create();

    Customer::factory()->for($company)->create(['phone' => '555-1234']);

    expect((new GetCustomerByPhoneAction)->execute($company->id, '555'))->toBeNull();
});

test('get customer by phone action ignores customers from other companies', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    Customer::factory()->for($otherCompany)->create(['phone' => '555-1234']);

    expect((new GetCustomerByPhoneAction)->execute($company->id, '555-1234'))->toBeNull();
});

test('get customer by phone action ignores soft deleted customers', function () {
    $company = Company::factory()->create();

    $customer = Customer::factory()->for($company)->create(['phone' => '555-1234']);
    $customer->delete();

    expect((new GetCustomerByPhoneAction)->execute($company->id, '555-1234'))->toBeNull();
});

test('get customer by phone tool returns json for matching customer', function () {
    $company = Company::factory()->create();

    Customer::factory()->for($company)->create([
        'full_name' => 'Cliente tool',
        'phone' => '555-9999',
    ]);

    $response = (new GetCustomerByPhone($company->id))->handle(
        new Request(['phone' => '555-9999'])
    );

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['full_name'])->toBe('Cliente tool')
        ->and($decoded['phone'])->toBe('555-9999')
        ->and($decoded['addresses'])->toBeArray();
});

test('get customer by phone tool returns error message when customer is not found', function () {
    $company = Company::factory()->create();

    $response = (new GetCustomerByPhone($company->id))->handle(
        new Request(['phone' => '000-0000'])
    );

    expect((string) $response)->toBe('No se encontró ningún cliente con ese número de teléfono.');
});

test('get customer by phone tool exposes expected name and phone schema', function () {
    $tool = new GetCustomerByPhone((string) Str::uuid());
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($tool->name())->toBe('get_customer_by_phone')
        ->and($schema)->toHaveKey('phone');
});
