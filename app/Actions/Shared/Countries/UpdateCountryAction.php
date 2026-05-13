<?php

namespace App\Actions\Shared\Countries;

use App\Models\Shared\Country;

class UpdateCountryAction
{
    /**
     * @param  array{name: string, name_code: string, phone_code: string, currency_name: string, currency_symbol: string}  $data
     */
    public function execute(Country $country, array $data): Country
    {
        $country->name = $data['name'];
        $country->name_code = $data['name_code'];
        $country->phone_code = $data['phone_code'];
        $country->currency_name = $data['currency_name'];
        $country->currency_symbol = $data['currency_symbol'];
        $country->save();

        return $country->fresh() ?? $country;
    }
}
