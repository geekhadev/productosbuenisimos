<?php

namespace Database\Seeders\Stock;

use App\Models\Company;
use App\Models\Stock\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::query()->first();

        if ($company === null) {
            return;
        }

        $rows = [
            [
                'name' => 'Imanes de Neodimio Autoadhesivos para Puertas y Cajones',
                'code' => 'PROD001',
                'sku' => 'IMNNEO01',
                'is_active' => true,
                'price' => 4990,
                'weight' => 0,
                'width' => 1.7,
                'length' => 4.2,
                'height' => 0.2,
                'minimum_stock' => 1500,
                'description' => 'Imanes ultrafinos autoadhesivos, soportan hasta 20 lb de fuerza magnética.',
            ],
            [
                'name' => 'Paños Multiusos de Alambre para Limpieza',
                'code' => 'PROD002',
                'sku' => 'PANMIC01',
                'is_active' => true,
                'price' => 2490,
                'weight' => 0,
                'width' => 20,
                'length' => 20,
                'height' => 0.2,
                'minimum_stock' => 1000,
                'description' => 'Paño de limpieza multiusos',
            ],
            [
                'name' => 'Mallas Adhesivas para Coladera',
                'code' => 'PROD003',
                'sku' => 'FILDES01',
                'is_active' => true,
                'price' => 1990,
                'weight' => 0,
                'width' => 10,
                'length' => 10,
                'height' => 0.1,
                'minimum_stock' => 1000,
                'description' => 'Filtro adhesivo para coladera que evita que cabellos y residuos tapen el desagüe.',
            ],
        ];

        foreach ($rows as $row) {
            Product::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'sku' => $row['sku'],
                ],
                [
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'width' => $row['width'],
                    'length' => $row['length'],
                    'height' => $row['height'],
                    'volume' => 0,
                    'weight' => $row['weight'],
                    'minimum_stock' => $row['minimum_stock'],
                    'price' => $row['price'],
                    'is_active' => $row['is_active'],
                    'description' => $row['description'],
                ],
            );
        }
    }
}
