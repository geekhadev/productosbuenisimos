<?php

use App\Actions\AI\Tools\CreateCustomerAction;
use App\Ai\Tools\CreateCustomer;
use App\Models\Company;
use App\Models\Sales\Customer;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Str;
use Laravel\Ai\ObjectSchema;
use Laravel\Ai\Tools\Request;

test('create customer action creates customer without addresses', function () {
    $company = Company::factory()->create();

    $result = (new CreateCustomerAction)->execute($company->id, [
        'full_name' => 'Juan Pérez',
        'phone' => '555-1234',
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['full_name'])->toBe('Juan Pérez')
        ->and($result['phone'])->toBe('555-1234')
        ->and($result['company_id'])->toBe($company->id)
        ->and($result['addresses'])->toBe([]);

    expect(Customer::forCompany($company->id)->where('phone', '555-1234')->exists())->toBeTrue();
});

test('create customer action creates customer with addresses', function () {
    $company = Company::factory()->create();

    $result = (new CreateCustomerAction)->execute($company->id, [
        'full_name' => 'María López',
        'phone' => '555-5678',
        'addresses' => [
            [
                'country_name' => 'México',
                'state_name' => 'CDMX',
                'address' => 'Av. Insurgentes Sur 1234',
            ],
        ],
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['full_name'])->toBe('María López')
        ->and($result['phone'])->toBe('555-5678')
        ->and($result['addresses'])->toHaveCount(1)
        ->and($result['addresses'][0]['sort_order'])->toBe(0)
        ->and($result['addresses'][0]['country_name'])->toBe('México')
        ->and($result['addresses'][0]['state_name'])->toBe('CDMX')
        ->and($result['addresses'][0]['address'])->toBe('Av. Insurgentes Sur 1234');
});

test('create customer action returns validation error when phone is already taken', function () {
    $company = Company::factory()->create();

    Customer::factory()->for($company)->create(['phone' => '555-1234']);

    $result = (new CreateCustomerAction)->execute($company->id, [
        'full_name' => 'Otro Cliente',
        'phone' => '555-1234',
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('phone');
});

test('create customer action allows reusing phone from soft deleted customer', function () {
    $company = Company::factory()->create();

    $deleted = Customer::factory()->for($company)->create(['phone' => '555-1234']);
    $deleted->delete();

    $result = (new CreateCustomerAction)->execute($company->id, [
        'full_name' => 'Nuevo Cliente',
        'phone' => '555-1234',
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['full_name'])->toBe('Nuevo Cliente')
        ->and($result['phone'])->toBe('555-1234');
});

test('create customer action enforces phone uniqueness per company only', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    Customer::factory()->for($otherCompany)->create(['phone' => '555-1234']);

    $result = (new CreateCustomerAction)->execute($company->id, [
        'full_name' => 'Cliente empresa A',
        'phone' => '555-1234',
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['phone'])->toBe('555-1234');
});

test('create customer tool returns json for successful creation', function () {
    $company = Company::factory()->create();

    $response = (new CreateCustomer($company->id))->handle(new Request([
        'full_name' => 'Cliente tool',
        'phone' => '555-9999',
    ]));

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['full_name'])->toBe('Cliente tool')
        ->and($decoded['phone'])->toBe('555-9999')
        ->and($decoded['addresses'])->toBe([]);
});

test('create customer tool returns json validation error', function () {
    $company = Company::factory()->create();

    Customer::factory()->for($company)->create(['phone' => '555-0000']);

    $response = (new CreateCustomer($company->id))->handle(new Request([
        'full_name' => 'Duplicado',
        'phone' => '555-0000',
    ]));

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toHaveKey('error')
        ->and($decoded['error'])->toHaveKey('phone');
});

test('create customer tool exposes expected name and schema', function () {
    $tool = new CreateCustomer((string) Str::uuid());
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($tool->name())->toBe('create_customer')
        ->and($schema)->toHaveKeys(['full_name', 'phone', 'addresses']);
});

test('create customer tool address items satisfy openai strict schema', function () {
    $tool = new CreateCustomer((string) Str::uuid());
    $serialized = (new ObjectSchema($tool->schema(new JsonSchemaTypeFactory)))->toSchema();

    $addressItem = $serialized['properties']['addresses']['items'];

    expect($serialized['required'])->toContain('addresses')
        ->and($serialized['properties']['addresses']['type'])->toBe(['array', 'null'])
        ->and($addressItem['required'] ?? [])
        ->toBe(['country_name', 'state_name', 'address'])
        ->and($addressItem['properties']['country_name']['type'])->toBe(['string', 'null']);
});
