<?php

use App\Actions\AI\Tools\CreateOrderAction;
use App\Ai\Tools\CreateOrder;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\Sales\Order;
use App\Models\Stock\Product;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

function createOrderToolFixture(Company $company): array
{
    $customer = Customer::factory()->for($company)->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $product = Product::factory()->for($company)->create([
        'is_active' => true,
        'price' => 150,
    ]);

    return compact('customer', 'address', 'product');
}

test('create order action creates order with product price', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-AI-001',
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 3],
        ],
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['name'])->toBe('PED-AI-001')
        ->and($result['customer_id'])->toBe($fixture['customer']->id)
        ->and($result['address_id'])->toBe($fixture['address']->id)
        ->and((string) $result['total_amount'])->toBe('450.00')
        ->and($result['customer']['full_name'])->toBe($fixture['customer']->full_name)
        ->and($result['items'])->toHaveCount(1)
        ->and((string) $result['items'][0]['unit_price'])->toBe('150.00')
        ->and((string) $result['items'][0]['line_total'])->toBe('450.00')
        ->and($result['items'][0]['product']['name'])->toBe($fixture['product']->name);

    expect(Order::forCompany($company->id)->where('name', 'PED-AI-001')->exists())->toBeTrue();
});

test('create order action generates name when omitted', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
        ],
    ]);

    expect($result)->not->toHaveKey('error')
        ->and($result['name'])->toMatch('/^Pedido-\d{8}-\d{6}$/');
});

test('create order action accepts custom unit price', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-AI-PRICE',
        'items' => [
            [
                'product_id' => $fixture['product']->id,
                'quantity' => 2,
                'unit_price' => 99.5,
            ],
        ],
    ]);

    expect($result)->not->toHaveKey('error')
        ->and((string) $result['total_amount'])->toBe('199.00')
        ->and((string) $result['items'][0]['unit_price'])->toBe('99.50');
});

test('create order action returns validation error for unknown customer', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => (string) Str::uuid(),
        'address_id' => $fixture['address']->id,
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
        ],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('customer_id');
});

test('create order action returns validation error when address does not belong to customer', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);
    $otherCustomer = Customer::factory()->for($company)->create();
    $otherAddress = CustomerAddress::factory()->for($otherCustomer)->create();

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $otherAddress->id,
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
        ],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('address_id');
});

test('create order action returns validation error for empty items', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'items' => [],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('items');
});

test('create order action returns validation error for inactive product', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);
    $inactive = Product::factory()->for($company)->create(['is_active' => false]);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-AI-INACTIVE',
        'items' => [
            ['product_id' => $inactive->id, 'quantity' => 1],
        ],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('items.0.product_id');
});

test('create order action returns validation error for duplicate product', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-AI-DUP',
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
            ['product_id' => $fixture['product']->id, 'quantity' => 2],
        ],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('items.1.product_id');
});

test('create order action rejects soft deleted customer', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);
    $fixture['customer']->delete();

    $result = (new CreateOrderAction)->execute($company->id, [
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-AI-DELETED',
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
        ],
    ]);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('customer_id');
});

test('create order tool returns json for successful creation', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $response = (new CreateOrder($company->id))->handle(new Request([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-TOOL',
        'items' => [
            ['product_id' => $fixture['product']->id, 'quantity' => 1],
        ],
    ]));

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['name'])->toBe('PED-TOOL')
        ->and($decoded)->not->toHaveKey('error')
        ->and($decoded['items'])->toHaveCount(1);
});

test('create order tool returns json validation error', function () {
    $company = Company::factory()->create();
    $fixture = createOrderToolFixture($company);

    $response = (new CreateOrder($company->id))->handle(new Request([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-TOOL-ERR',
        'items' => [],
    ]));

    $decoded = json_decode((string) $response, true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toHaveKey('error')
        ->and($decoded['error'])->toHaveKey('items');
});

test('create order tool exposes expected name and schema', function () {
    $tool = new CreateOrder((string) Str::uuid());
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($tool->name())->toBe('create_order')
        ->and($schema)->toHaveKeys(['customer_id', 'address_id', 'items', 'name']);
});
