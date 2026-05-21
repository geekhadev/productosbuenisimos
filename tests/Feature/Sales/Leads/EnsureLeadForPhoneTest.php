<?php

use App\Actions\Sales\Leads\EnsureLeadForPhoneAction;
use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;

test('ensure lead for phone action creates new lead with nuevo status when phone is unknown', function () {
    $company = Company::factory()->create();

    $result = (new EnsureLeadForPhoneAction)->execute($company->id, 'web', '555-1234');

    expect($result)->not->toHaveKey('error')
        ->and($result['phone'])->toBe('555-1234')
        ->and($result['source'])->toBe('web')
        ->and($result['status'])->toBe(LeadStatus::Nuevo->value)
        ->and($result['customer_id'])->toBeNull()
        ->and($result['customer'])->toBeNull();

    expect(Lead::query()->where('company_id', $company->id)->where('phone', '555-1234')->exists())->toBeTrue();
});

test('ensure lead for phone action returns existing active lead without duplicating', function () {
    $company = Company::factory()->create();

    $existing = Lead::factory()->for($company)->create([
        'phone' => '555-1234',
        'source' => LeadSource::Whatsapp,
        'status' => LeadStatus::Contactado,
    ]);

    $result = (new EnsureLeadForPhoneAction)->execute($company->id, 'web', '555-1234');

    expect($result['id'])->toBe($existing->id)
        ->and($result['status'])->toBe(LeadStatus::Contactado->value)
        ->and(Lead::query()->where('company_id', $company->id)->where('phone', '555-1234')->count())->toBe(1);
});

test('ensure lead for phone action creates lead as convertido when customer exists with same phone', function () {
    $company = Company::factory()->create();

    $customer = Customer::factory()->for($company)->create([
        'full_name' => 'Juan Pérez',
        'phone' => '555-1234',
    ]);

    $result = (new EnsureLeadForPhoneAction)->execute($company->id, 'web', '555-1234');

    expect($result['status'])->toBe(LeadStatus::Convertido->value)
        ->and($result['customer_id'])->toBe($customer->id)
        ->and($result['customer']['full_name'])->toBe('Juan Pérez')
        ->and($result['customer']['phone'])->toBe('555-1234');
});

test('ensure lead for phone action allows new lead when previous lead was soft deleted', function () {
    $company = Company::factory()->create();

    $deleted = Lead::factory()->for($company)->create(['phone' => '555-1234']);
    $deleted->delete();

    $result = (new EnsureLeadForPhoneAction)->execute($company->id, 'web', '555-1234');

    expect($result['id'])->not->toBe($deleted->id)
        ->and(Lead::withTrashed()->where('company_id', $company->id)->where('phone', '555-1234')->count())->toBe(2);
});

test('ensure lead for phone action returns validation error for empty phone', function () {
    $company = Company::factory()->create();

    $result = (new EnsureLeadForPhoneAction)->execute($company->id, 'web', '');

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toHaveKey('phone');
});
