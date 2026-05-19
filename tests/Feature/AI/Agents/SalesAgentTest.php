<?php

use App\Ai\Agents\SalesAgent;
use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetProducts;
use Illuminate\Support\Str;
use Laravel\Ai\Concerns\RemembersConversations;

test('sales agent loads instructions from prompt file', function () {
    $companyId = (string) Str::uuid();

    $instructions = SalesAgent::make(companyId: $companyId)->instructions();

    expect($instructions)
        ->toContain('asistente de ventas')
        ->toContain('get_customer_by_phone')
        ->toContain('create_order');
});

test('sales agent registers all sales tools with company id', function () {
    $companyId = (string) Str::uuid();

    $tools = SalesAgent::make(companyId: $companyId)->tools();

    expect($tools)->toHaveCount(5);

    $toolClasses = array_map(fn ($tool) => $tool::class, iterator_to_array($tools));

    expect($toolClasses)->toBe([
        GetProducts::class,
        GetCustomerByPhone::class,
        CreateCustomer::class,
        CreateCustomerAddress::class,
        CreateOrder::class,
    ]);
});

test('sales agent uses remembers conversations trait', function () {
    expect(class_uses(SalesAgent::class))->toContain(RemembersConversations::class);
});

test('sales agent can start conversation for user', function () {
    $companyId = (string) Str::uuid();
    $user = new class
    {
        public string $id = 'user-uuid';
    };

    $agent = SalesAgent::make(companyId: $companyId)->forUser($user);

    expect($agent->hasConversationParticipant())->toBeTrue()
        ->and($agent->conversationParticipant())->toBe($user)
        ->and($agent->currentConversation())->toBeNull()
        ->and($agent->messages())->toBe([]);
});
