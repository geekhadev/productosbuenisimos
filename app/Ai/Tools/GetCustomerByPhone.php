<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetCustomerByPhoneAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCustomerByPhone implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_customer_by_phone';
    }

    public function description(): Stringable|string
    {
        return 'Busca un cliente registrado por su número de teléfono dentro de la empresa '
            .'y devuelve su información básica (nombre, teléfono y direcciones) como JSON.';
    }

    public function handle(Request $request): Stringable|string
    {
        $customer = (new GetCustomerByPhoneAction)->execute(
            $this->companyId,
            $request->string('phone'),
        );

        if ($customer === null) {
            return 'No se encontró ningún cliente con ese número de teléfono.';
        }

        return json_encode($customer, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'phone' => $schema
                ->string()
                ->description('Número de teléfono del cliente a consultar.')
                ->max(255)
                ->required(),
        ];
    }
}
