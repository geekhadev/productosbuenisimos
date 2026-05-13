<?php

namespace Database\Factories;

use App\Models\Administration\System;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<System>
 */
class SystemFactory extends Factory
{
    /**
     * @var class-string<System>
     */
    protected $model = System::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(3),
            'slug' => fake()->unique()->slug(3),
        ];
    }
}
