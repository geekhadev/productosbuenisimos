<?php

namespace Database\Seeders\Configuration;

use App\Models\Configuration\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate(
            [
                'name' => Role::OWNER_SYSTEM_NAME,
                'is_public' => true,
                'company_id' => null,
            ],
            [],
        );
    }
}
