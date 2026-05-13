<?php

namespace App\Actions\Sales\Customers;

use App\Models\Sales\Customer;

class SyncCustomerAddressesAction
{
    /**
     * Reemplaza todas las direcciones del cliente por las filas con al menos un campo no vacío,
     * en el orden recibido.
     *
     * @param  list<array{country_name: string, state_name: string, address: string}>  $addresses
     */
    public function execute(Customer $customer, array $addresses): void
    {
        $customer->addresses()->delete();

        $sortOrder = 0;
        foreach ($addresses as $row) {
            $countryName = trim($row['country_name'] ?? '');
            $stateName = trim($row['state_name'] ?? '');
            $line = trim($row['address'] ?? '');

            if ($countryName === '' && $stateName === '' && $line === '') {
                continue;
            }

            $customer->addresses()->create([
                'country_name' => $countryName === '' ? null : $countryName,
                'state_name' => $stateName === '' ? null : $stateName,
                'address' => $line === '' ? null : $line,
                'sort_order' => $sortOrder,
            ]);
            $sortOrder++;
        }
    }
}
