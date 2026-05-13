<?php

namespace Database\Factories\Administration;

use App\Models\Administration\Module;
use App\Models\Administration\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * @var class-string<Permission>
     */
    protected $model = Permission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(2),
            'slug' => fake()->unique()->slug(2),
            'module_id' => Module::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Permission $permission): void {
            if (! str_contains($permission->slug, '.')) {
                $module = Module::query()->findOrFail($permission->module_id);
                $permission->slug = Permission::composeStoredSlug($module, $permission->slug);
            }
        });
    }
}
