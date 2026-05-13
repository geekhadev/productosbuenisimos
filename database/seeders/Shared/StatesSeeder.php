<?php

namespace Database\Seeders\Shared;

use App\Models\Shared\Country;
use App\Models\Shared\State;
use Illuminate\Database\Seeder;

class StatesSeeder extends Seeder
{
    public function run(): void
    {
        $chile = Country::query()->where('name_code', 'CL')->first();
        $spain = Country::query()->where('name_code', 'ES')->first();
        $usa = Country::query()->where('name_code', 'US')->first();

        if ($chile !== null) {
            $this->seedForCountry($chile->id, [
                'Arica y Parinacota',
                'Tarapaca',
                'Antofagasta',
                'Atacama',
                'Coquimbo',
                'Valparaiso',
                'Metropolitana de Santiago',
                "Libertador General Bernardo O'Higgins",
                'Maule',
                'Nuble',
                'Biobio',
                'La Araucania',
                'Los Rios',
                'Los Lagos',
                'Aysen del General Carlos Ibanez del Campo',
                'Magallanes y de la Antartica Chilena',
            ]);
        }

        if ($spain !== null) {
            $this->seedForCountry($spain->id, [
                'Andalucia',
                'Aragon',
                'Cataluna',
                'Comunidad de Madrid',
                'Comunidad Valenciana',
                'Castilla-La Mancha',
                'Castilla y Leon',
                'Galicia',
                'Pais Vasco',
                'Islas Canarias',
            ]);
        }

        if ($usa !== null) {
            $this->seedForCountry($usa->id, [
                'California',
                'Texas',
                'Florida',
                'New York',
                'Illinois',
            ]);
        }
    }

    /**
     * @param  list<string>  $names
     */
    private function seedForCountry(int $countryId, array $names): void
    {
        foreach ($names as $name) {
            State::query()->updateOrCreate(
                ['country_id' => $countryId, 'name' => $name],
                ['name' => $name],
            );
        }
    }
}
