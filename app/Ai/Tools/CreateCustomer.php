<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateCustomerAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateCustomer implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_customer';
    }

    public function description(): Stringable|string
    {
        return 'Crea un nuevo cliente en la empresa activa. '
            .'Requiere nombre completo y teléfono; opcionalmente acepta un arreglo de direcciones.';
    }

    public function handle(Request $request): Stringable|string
    {
        $result = (new CreateCustomerAction)->execute($this->companyId, $request->toArray());

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'full_name' => $schema
                ->string()
                ->description('Nombre completo del cliente.')
                ->max(255)
                ->required(),
            'phone' => $schema
                ->string()
                ->description('Teléfono del cliente. Debe ser único dentro de la empresa.')
                ->max(40)
                ->required(),
            'addresses' => $schema
                ->array()
                ->description('Lista de direcciones del cliente (máx. 20). Cada elemento puede tener country_name, state_name y address.')
                ->max(20)
                ->items($schema->object([
                    'country_name' => $schema->string()->description('País.')->max(120),
                    'state_name' => $schema->string()->description('Estado o provincia.')->max(120),
                    'address' => $schema->string()->description('Dirección textual completa.')->max(2000),
                ])),
        ];
    }
}
