<?php

namespace App\Actions\Shared\Countries;

use App\Models\Shared\Country;

class CreateCountryAction
{
    /**
     * @param  array{name: string, name_code: string, phone_code: string, currency_name: string, currency_symbol: string}  $data
     */
    public function execute(array $data): Country
    {
        return Country::query()->create([
            'name' => $data['name'],
            'name_code' => $data['name_code'],
            'phone_code' => $data['phone_code'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
        ]);
    }
}
