<?php

namespace Database\Factories\Configuration;

use App\Models\Company;
use App\Models\Configuration\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * @var class-string<Role>
     */
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'is_public' => false,
            'company_id' => Company::factory(),
        ];
    }

    public function publicGlobal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'company_id' => null,
        ]);
    }
}
