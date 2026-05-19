<?php

use App\Actions\AI\Tools\CreateCustomerAddressAction;
use App\Ai\Tools\CreateCustomerAddress;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

test('create customer address action creates address with all fields', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        'México',
        'Ciudad de México',
        'Av. Insurgentes Sur 1234',
    );

    expect($result)->not->toHaveKey('error')
        ->and($result['customer_id'])->toBe($customer->id)
        ->and($result['sort_order'])->toBe(1)
        ->and($result['country_name'])->toBe('México')
        ->and($result['state_name'])->toBe('Ciudad de México')
        ->and($result['address'])->toBe('Av. Insurgentes Sur 1234');

    expect(CustomerAddress::query()->where('customer_id', $customer->id)->count())->toBe(1);
});

test('create customer address action assigns incremental sort order', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();

    CustomerAddress::factory()->for($customer)->create(['sort_order' => 0]);

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        null,
        null,
        'Nueva dirección',
    );

    expect($result)->not->toHaveKey('error')
        ->and($result['sort_order'])->toBe(1)
        ->and($result['address'])->toBe('Nueva dirección');
});

test('create customer address action normalizes empty strings to null', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        '   ',
        '',
        'Solo calle 10',
    );

    expect($result)->not->toHaveKey('error')
        ->and($result['country_name'])->toBeNull()
        ->and($result['state_name'])->toBeNull()
        ->and($result['address'])->toBe('Solo calle 10');
});

test('create customer address action returns error when customer not in company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $customer = Customer::factory()->for($otherCompany)->create();

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        'México',
        null,
        'Calle 1',
    );

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toBe('No se encontró el cliente en la empresa.');
});

test('create customer address action returns error when customer is soft deleted', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    $customer->delete();

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        'México',
        null,
        'Calle 1',
    );

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toBe('No se encontró el cliente en la empresa.');
});

test('create customer address action returns error when all address fields are empty', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();

    $result = (new CreateCustomerAddressAction)->execute(
        $company->id,
        $customer->id,
        null,
        null,
        null,
    );

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toBe('Debes proporcionar al menos uno de: country_name, state_name o address.');
});

test('create customer address tool returns json for successful creation', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();

    $response = (new CreateCustomerAddress($company->id))->handle(new Request([
        'customer_id' => $customer->id,
        'address' => 'Calle Principal 100',
    ]));

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['address'])->toBe('Calle Principal 100')
        ->and($decoded['customer_id'])->toBe($customer->id);
});

test('create customer address tool exposes expected name and schema', function () {
    $tool = new CreateCustomerAddress((string) Str::uuid());
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($tool->name())->toBe('create_customer_address')
        ->and($schema)->toHaveKeys(['customer_id', 'country_name', 'state_name', 'address']);
});
