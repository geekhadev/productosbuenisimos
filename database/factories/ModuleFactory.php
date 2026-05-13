<?php

namespace Database\Factories;

use App\Models\Administration\Module;
use App\Models\Administration\System;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    /**
     * @var class-string<Module>
     */
    protected $model = Module::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(2),
            'slug' => fake()->unique()->slug(2),
            'system_id' => System::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Module $module): void {
            if (! str_contains($module->slug, '.')) {
                $system = $module->system;
                $module->slug = Module::composeStoredSlug($system, $module->slug);
            }
        });
    }
}
