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
        return 'Crea una nueva dirección para un cliente existente de la empresa. '
            .'Requiere el customer_id y al menos uno de: country_name, state_name o address.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new CreateCustomerAddressAction)->execute(
            $this->companyId,
            $request->string('customer_id'),
            $request->has('country_name') ? $request->string('country_name') : null,
            $request->has('state_name') ? $request->string('state_name') : null,
            $request->has('address') ? $request->string('address') : null,
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
                ->description('Nombre del país (máx. 120 caracteres). Opcional.')
                ->max(120),
            'state_name' => $schema
                ->string()
                ->description('Nombre del estado o provincia (máx. 120 caracteres). Opcional.')
                ->max(120),
            'address' => $schema
                ->string()
                ->description('Dirección completa: calle, número, colonia, etc. (máx. 2000 caracteres). Opcional.')
                ->max(2000),
        ];
    }
}
