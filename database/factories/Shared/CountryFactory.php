<?php

namespace Database\Factories\Shared;

use App\Models\Shared\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'name' => $name,
            'name_code' => strtoupper(fake()->unique()->lexify('??')),
            'phone_code' => '+'.fake()->numerify('##'),
            'currency_name' => fake()->word(),
            'currency_symbol' => strtoupper(fake()->lexify('???')),
        ];
    }
}
