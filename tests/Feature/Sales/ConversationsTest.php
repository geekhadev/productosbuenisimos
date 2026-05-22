<?php

use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Administration\Permission;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
});

test('permissions seeder registers sales conversations module', function () {
    expect(Permission::query()->where('slug', 'sales.conversations.list')->exists())
        ->toBeTrue();
});

test('index lists conversations for selected company only', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();

    $conversationA = ChatbotConversation::factory()
        ->for($companyA)
        ->create(['phone' => '+56910000001']);
    ChatbotConversation::factory()
        ->for($companyB)
        ->create(['phone' => '+56910000002']);

    ChatbotMessage::factory()->for($conversationA, 'conversation')->create([
        'content' => 'Hola desde empresa A',
        'created_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.conversations.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('sales/conversations/index')
        ->has('conversations.data', 1)
        ->where('conversations.data.0.phone', '+56910000001')
        ->where('selected', null));
});

test('index resolves customer name when lead is converted', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $customer = Customer::factory()->for($company)->create([
        'full_name' => 'María Pérez',
        'phone' => '+56922223333',
    ]);

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create(['phone' => '+56922223333']);

    Lead::factory()->for($company)->create([
        'phone' => '+56922223333',
        'status' => LeadStatus::Convertido,
        'customer_id' => $customer->id,
    ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.index'))
        ->assertInertia(fn ($page) => $page
            ->where('conversations.data.0.contact_name', 'María Pérez')
            ->where('conversations.data.0.contact_type', 'customer')
            ->where('conversations.data.0.customer_id', $customer->id));
});

test('index shows lead status when not converted', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create(['phone' => '+56944445555']);

    Lead::factory()->for($company)->create([
        'phone' => '+56944445555',
        'status' => LeadStatus::Nuevo,
        'customer_id' => null,
    ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.index'))
        ->assertInertia(fn ($page) => $page
            ->where('conversations.data.0.contact_type', 'lead')
            ->where('conversations.data.0.lead_status', 'nuevo'));
});

test('index orders conversations by last message activity', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $older = ChatbotConversation::factory()
        ->for($company)
        ->create(['phone' => '+56911111111']);
    $newer = ChatbotConversation::factory()
        ->for($company)
        ->create(['phone' => '+56922222222']);

    ChatbotMessage::factory()->for($older, 'conversation')->create([
        'created_at' => now()->subHours(2),
    ]);
    ChatbotMessage::factory()->for($newer, 'conversation')->create([
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.index'))
        ->assertInertia(fn ($page) => $page
            ->where('conversations.data.0.phone', '+56922222222')
            ->where('conversations.data.1.phone', '+56911111111'));
});

test('show returns selected conversation with messages', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create([
            'phone' => '+56933334444',
            'source' => ChatbotSource::Web,
        ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::User,
        'source' => ChatbotSource::Web,
        'content' => 'Hola',
        'created_at' => now()->subMinutes(2),
    ]);
    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::Assistant,
        'source' => ChatbotSource::Whatsapp,
        'content' => 'Respuesta',
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.show', $conversation))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('sales/conversations/index')
            ->where('selected.id', $conversation->id)
            ->where('selected.source', 'web')
            ->has('selected.messages', 2)
            ->where('selected.messages.0.role', 'user')
            ->where('selected.messages.0.content', 'Hola')
            ->where('selected.messages.1.role', 'assistant')
            ->where('selected.messages.1.source', 'whatsapp'));
});

test('show cannot access conversation from another company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();
    $user = User::factory()->root()->create();

    $conversation = ChatbotConversation::factory()
        ->for($companyB)
        ->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($companyA))
        ->get(route('sales.conversations.show', $conversation))
        ->assertNotFound();
});

test('user without permission cannot access conversations index', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create();

    ChatbotConversation::factory()->for($company)->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.index'))
        ->assertForbidden();
});

test('index prefers converted lead when multiple leads share phone', function () {
    $company = Company::factory()->create();
    $user = User::factory()->root()->create();

    $customer = Customer::factory()->for($company)->create([
        'full_name' => 'Cliente Convertido',
    ]);

    $conversation = ChatbotConversation::factory()
        ->for($company)
        ->create(['phone' => '+56955556666']);

    Lead::factory()->for($company)->create([
        'phone' => '+56955556666',
        'status' => LeadStatus::Nuevo,
        'customer_id' => null,
        'created_at' => now()->subDay(),
    ]);

    Lead::factory()->for($company)->create([
        'phone' => '+56955556666',
        'status' => LeadStatus::Convertido,
        'customer_id' => $customer->id,
        'created_at' => now(),
    ]);

    ChatbotMessage::factory()->for($conversation, 'conversation')->create();

    $this->actingAs($user)
        ->withSession(withSelectedCompany($company))
        ->get(route('sales.conversations.index'))
        ->assertInertia(fn ($page) => $page
            ->where('conversations.data.0.contact_name', 'Cliente Convertido')
            ->where('conversations.data.0.contact_type', 'customer'));
});
