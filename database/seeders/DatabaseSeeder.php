<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Database\Seeders\Administration\PermissionsSeeder;
use Database\Seeders\Configuration\CompaniesSeeder;
use Database\Seeders\Shared\CountriesSeeder;
use Database\Seeders\Shared\StatesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Irwing Naranjo',
            'email' => 'khalisser@gmail.com',
            'type' => UserType::Root,
            'password' => Hash::make('qwerty123'),
            'email_verified_at' => now(),
        ]);

        $this->call(PermissionsSeeder::class);
        $this->call(CompaniesSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(StatesSeeder::class);
    }
}
