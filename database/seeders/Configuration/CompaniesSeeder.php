<?php

namespace Database\Seeders\Configuration;

use App\Enums\CompanyDocumentType;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'document_type' => CompanyDocumentType::ID,
                'document_number' => '123456789',
                'name' => 'PRODUCTOS BUENISIMOS SPA',
                'alias' => 'PRODUCTOS BUENISIMOS',
                'email' => 'info@productosbuenisimos.cl',
                'phone' => '+56987654321',
                'address' => 'AV. DE LOS CONQUISTADORES 2134',
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->firstOrCreate($company);
        }
    }
}
