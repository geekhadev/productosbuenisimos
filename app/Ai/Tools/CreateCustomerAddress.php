<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateCustomerAddressAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateCustomerAddress implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_customer_address';
    }

    public function description(): Stringable|string
    {
        return 'Crea una nueva dirección de entrega para un cliente existente. '
            .'Guarda país, estado, ciudad, colonia, calle, número, CP y referencia (o coordenadas GPS).';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new CreateCustomerAddressAction)->execute(
            companyId: $this->companyId,
            customerId: $request->string('customer_id'),
            countryName: $request->has('country_name') ? $request->string('country_name') : null,
            stateName: $request->has('state_name') ? $request->string('state_name') : null,
            cityName: $request->has('city_name') ? $request->string('city_name') : null,
            districtName: $request->has('district_name') ? $request->string('district_name') : null,
            streetPrefix: $request->has('street_prefix') ? $request->string('street_prefix') : null,
            houseNumber: $request->has('house_number') ? $request->string('house_number') : null,
            zipCode: $request->has('zip_code') ? $request->string('zip_code') : null,
            reference: $request->has('reference') ? $request->string('reference') : null,
            address: $request->has('address') ? $request->string('address') : null,
        );

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_id' => $schema
                ->string()
                ->description('UUID del cliente al que se asociará la nueva dirección.')
                ->required(),
            'country_name' => $schema
                ->string()
                ->description('Nombre del país. Usa siempre "Mexico".')
                ->max(120)
                ->nullable()
                ->required(),
            'state_name' => $schema
                ->string()
                ->description('Estado o provincia (ej. "Puebla", "CDMX"). Máx. 120 caracteres.')
                ->max(120)
                ->nullable()
                ->required(),
            'city_name' => $schema
                ->string()
                ->description('Ciudad o municipio (ej. "Puebla", "Tlaxcala"). Máx. 120 caracteres.')
                ->max(120)
                ->nullable()
                ->required(),
            'district_name' => $schema
                ->string()
                ->description('Colonia, barrio o fraccionamiento (ej. "Col. Centro", "Fracc. Lomas"). Máx. 120 caracteres.')
                ->max(120)
                ->nullable()
                ->required(),
            'street_prefix' => $schema
                ->string()
                ->description('Tipo de vía (ej. "Calle", "Avenida", "Boulevard", "Callejón"). Opcional. Máx. 30 caracteres.')
                ->max(30)
                ->nullable()
                ->required(),
            'house_number' => $schema
                ->string()
                ->description('Número exterior (ej. "123", "SN"). Opcional. Máx. 30 caracteres.')
                ->max(30)
                ->nullable()
                ->required(),
            'zip_code' => $schema
                ->string()
                ->description('Código postal de 5 dígitos (ej. "72000"). Opcional.')
                ->max(10)
                ->nullable()
                ->required(),
            'reference' => $schema
                ->string()
                ->description('Referencias adicionales de entrega o coordenadas GPS (ej. "Entre calles X y Y", "📍 19.0414, -98.2063"). Opcional.')
                ->nullable()
                ->required(),
            'address' => $schema
                ->string()
                ->description('Dirección legible concatenada: "Calle X #123, Col. Centro". Máx. 2000 caracteres.')
                ->max(2000)
                ->nullable()
                ->required(),
        ];
    }
}
