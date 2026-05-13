<?php

use Illuminate\Support\Facades\Route;

test('removed SII admin module routes are not registered', function () {
    expect(Route::has('sale.sii-certification-tickets.index'))->toBeFalse();
    expect(Route::has('sale.sii-certification-tickets.store'))->toBeFalse();
    expect(Route::has('sale.sii-certification-invoices.prototype'))->toBeFalse();
    expect(Route::has('shared.sii-tax-document-types.index'))->toBeFalse();
    expect(Route::has('shared.sii-economic-activities.index'))->toBeFalse();
});

test('company SII integration configuration routes are not registered', function () {
    expect(Route::has('configuration.companies.integrations.sii.update'))->toBeFalse();
    expect(Route::has('configuration.companies.integrations.sii.certificate.download'))->toBeFalse();
});
