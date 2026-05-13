<?php

namespace App\Actions\Shared\Countries;

use App\Models\Shared\Country;

class DeleteCountryAction
{
    public function execute(Country $country): void
    {
        $country->delete();
    }
}
