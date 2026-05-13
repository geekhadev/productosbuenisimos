<?php

namespace Database\Seeders\Shared;

use App\Models\Shared\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::query()->updateOrCreate(
            ['name_code' => 'CL'],
            [
                'name' => 'Chile',
                'phone_code' => '+56',
                'currency_name' => 'Peso Chileno',
                'currency_symbol' => 'CLP',
            ],
        );

        Country::query()->updateOrCreate(
            ['name_code' => 'AR'],
            [
                'name' => 'Argentina',
                'phone_code' => '+54',
                'currency_name' => 'Peso Argentino',
                'currency_symbol' => 'ARS',
            ],
        );

        Country::query()->updateOrCreate(
            ['name_code' => 'ES'],
            [
                'name' => 'Espana',
                'phone_code' => '+34',
                'currency_name' => 'Euro',
                'currency_symbol' => 'EUR',
            ],
        );

        Country::query()->updateOrCreate(
            ['name_code' => 'US'],
            [
                'name' => 'Estados Unidos',
                'phone_code' => '+1',
                'currency_name' => 'Dolar estadounidense',
                'currency_symbol' => 'USD',
            ],
        );
    }
}
