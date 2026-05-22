<?php

use App\Actions\Sales\Customers\CreateCustomerAction;
use App\Actions\Sales\Leads\AssociateLeadsWithCustomerForPhoneAction;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;

test('associate leads with customer for phone action scopes by company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $phone = '555-1111';

    $customer = Customer::factory()->for($company)->create(['phone' => $phone]);
    $lead = Lead::factory()->for($company)->create(['phone' => $phone, 'customer_id' => null]);
    Lead::factory()->for($otherCompany)->create(['phone' => $phone, 'customer_id' => null]);

    $updated = (new AssociateLeadsWithCustomerForPhoneAction)->execute($company->id, $phone, $customer);

    expect($updated)->toBe(1)
        ->and($lead->fresh()->customer_id)->toBe($customer->id)
        ->and($lead->fresh()->status)->toBe(LeadStatus::Convertido);

    $otherLead = Lead::forCompany($otherCompany->id)->where('phone', $phone)->first();
    expect($otherLead->customer_id)->toBeNull();
});

test('store customer associates matching lead for company', function () {
    $company = Company::factory()->create();
    $phone = '+56944445555';
    $lead = Lead::factory()->for($company)->create(['phone' => $phone, 'customer_id' => null]);

    $customer = app(CreateCustomerAction::class)->execute($company->id, [
        'full_name' => 'Cliente web',
        'phone' => $phone,
        'addresses' => [],
    ]);

    expect($lead->fresh())
        ->customer_id->toBe($customer->id)
        ->status->toBe(LeadStatus::Convertido);
});
