<?php

use App\Models\Administration\Permission;
use Database\Seeders\Administration\PermissionsSeeder;

test('permissions seeder registers shared countries and states modules', function () {
    $this->seed(PermissionsSeeder::class);

    $expectedSlugs = [
        'shared.countries.list',
        'shared.countries.create',
        'shared.countries.edit',
        'shared.countries.delete',
        'shared.states.list',
        'shared.states.create',
        'shared.states.edit',
        'shared.states.delete',
    ];

    foreach ($expectedSlugs as $slug) {
        expect(Permission::query()->where('slug', $slug)->exists())->toBeTrue(
            "Missing permission [{$slug}]",
        );
    }
});
