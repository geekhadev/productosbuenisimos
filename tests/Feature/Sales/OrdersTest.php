<?php

use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Stock\Product;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

function createOrderFixture(Company $company): array
{
    $customer = Customer::factory()->for($company)->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $product = Product::factory()->for($company)->create([
        'is_active' => true,
        'price' => 100,
    ]);

    return compact('customer', 'address', 'product');
}

test('permissions seeder registers sales orders module', function () {
    expect(Permission::query()->where('slug', 'sales.orders.list')->exists())
        ->toBeTrue();
});

test('root user can list orders scoped to selected company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();

    $fixtureA = createOrderFixture($companyA);
    $fixtureB = createOrderFixture($companyB);

    $orderA = Order::factory()->for($companyA)->create([
        'customer_id' => $fixtureA['customer']->id,
        'address_id' => $fixtureA['address']->id,
        'name' => 'PED-A',
        'total_amount' => 200,
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderA->id,
        'product_id' => $fixtureA['product']->id,
        'quantity' => 2,
        'unit_price' => 100,
        'line_total' => 200,
    ]);

    Order::factory()->for($companyB)->create([
        'customer_id' => $fixtureB['customer']->id,
        'address_id' => $fixtureB['address']->id,
        'name' => 'PED-B',
    ]);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.orders.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('sales/orders/index')
        ->has('data.data', 1)
        ->where('data.data.0.name', 'PED-A'));
});

test('list filters orders by created_at date range', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $inRange = Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-IN',
        'created_at' => '2026-05-10 12:00:00',
    ]);
    OrderItem::factory()->create([
        'order_id' => $inRange->id,
        'product_id' => $fixture['product']->id,
    ]);

    Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-OUT',
        'created_at' => '2026-04-01 12:00:00',
    ]);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.orders.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('data.data', 1)
        ->where('data.data.0.name', 'PED-IN'));
});

test('list rejects date_to before date_from with 422', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.orders.index', [
            'date_from' => '2026-05-20',
            'date_to' => '2026-05-01',
        ]))
        ->assertSessionHasErrors('date_to');
});

test('store creates order with items and coherent total', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.orders.store'), [
            'name' => 'PED-NEW',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [
                [
                    'product_id' => $fixture['product']->id,
                    'quantity' => 3,
                ],
            ],
        ]);

    $response->assertRedirect(route('sales.orders.index'));

    $order = Order::query()->where('name', 'PED-NEW')->first();
    expect($order)->not->toBeNull();
    expect((string) $order->total_amount)->toBe('300.00');
    expect($order->items)->toHaveCount(1);
});

test('store rejects order without items', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.orders.store'), [
            'name' => 'PED-EMPTY',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [],
        ])
        ->assertSessionHasErrors('items');
});

test('store rejects duplicate product in same request', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.orders.store'), [
            'name' => 'PED-DUP',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [
                ['product_id' => $fixture['product']->id, 'quantity' => 1],
                ['product_id' => $fixture['product']->id, 'quantity' => 2],
            ],
        ])
        ->assertSessionHasErrors('items.1.product_id');
});

test('store rejects inactive product', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);
    $inactive = Product::factory()->for($company)->create(['is_active' => false]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->post(route('sales.orders.store'), [
            'name' => 'PED-INACTIVE',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [
                ['product_id' => $inactive->id, 'quantity' => 1],
            ],
        ])
        ->assertSessionHasErrors('items.0.product_id');
});

test('update rejects address not belonging to customer', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $otherCustomer = Customer::factory()->for($company)->create();
    $otherAddress = CustomerAddress::factory()->for($otherCustomer)->create();

    $order = Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-UPD',
        'total_amount' => 100,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $fixture['product']->id,
        'quantity' => 1,
        'unit_price' => 100,
        'line_total' => 100,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.orders.update', $order), [
            'name' => 'PED-UPD',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $otherAddress->id,
            'items' => [
                [
                    'product_id' => $fixture['product']->id,
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
        ])
        ->assertSessionHasErrors('address_id');
});

test('update rejects duplicate name within company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-TAKEN',
    ]);

    $order = Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-MINE',
        'total_amount' => 50,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $fixture['product']->id,
        'quantity' => 1,
        'unit_price' => 50,
        'line_total' => 50,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.orders.update', $order), [
            'name' => 'PED-TAKEN',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [
                [
                    'product_id' => $fixture['product']->id,
                    'quantity' => 1,
                    'unit_price' => 50,
                ],
            ],
        ])
        ->assertSessionHasErrors('name');
});

test('update recalculates total from quantity and unit price', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $order = Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-CALC',
        'total_amount' => 100,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $fixture['product']->id,
        'quantity' => 1,
        'unit_price' => 100,
        'line_total' => 100,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->put(route('sales.orders.update', $order), [
            'name' => 'PED-CALC',
            'customer_id' => $fixture['customer']->id,
            'address_id' => $fixture['address']->id,
            'items' => [
                [
                    'product_id' => $fixture['product']->id,
                    'quantity' => 3,
                    'unit_price' => 25.5,
                ],
            ],
        ])
        ->assertRedirect(route('sales.orders.index'));

    $order->refresh();
    expect((string) $order->total_amount)->toBe('76.50');
    expect((string) $order->items()->first()->line_total)->toBe('76.50');
});

test('another company can reuse the same order name', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();

    $fixtureA = createOrderFixture($companyA);
    $fixtureB = createOrderFixture($companyB);

    Order::factory()->for($companyA)->create([
        'customer_id' => $fixtureA['customer']->id,
        'address_id' => $fixtureA['address']->id,
        'name' => 'PED-SHARED',
    ]);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($companyB))
        ->post(route('sales.orders.store'), [
            'name' => 'PED-SHARED',
            'customer_id' => $fixtureB['customer']->id,
            'address_id' => $fixtureB['address']->id,
            'items' => [
                ['product_id' => $fixtureB['product']->id, 'quantity' => 1],
            ],
        ]);

    $response->assertRedirect(route('sales.orders.index'));
    expect(Order::query()->where('name', 'PED-SHARED')->count())->toBe(2);
});

test('destroy physically deletes order and items', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixture = createOrderFixture($company);

    $order = Order::factory()->for($company)->create([
        'customer_id' => $fixture['customer']->id,
        'address_id' => $fixture['address']->id,
        'name' => 'PED-DEL',
        'total_amount' => 50,
    ]);
    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $fixture['product']->id,
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->delete(route('sales.orders.destroy', $order))
        ->assertRedirect(route('sales.orders.index'));

    expect(Order::query()->find($order->id))->toBeNull();
    expect(OrderItem::query()->find($item->id))->toBeNull();
});

test('user cannot access order from another company via route binding', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();
    $fixtureB = createOrderFixture($companyB);

    $orderB = Order::factory()->for($companyB)->create([
        'customer_id' => $fixtureB['customer']->id,
        'address_id' => $fixtureB['address']->id,
        'name' => 'PED-OTHER',
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.orders.edit', $orderB))
        ->assertNotFound();
});
