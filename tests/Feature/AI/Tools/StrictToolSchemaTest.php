<?php

use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetProducts;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\ObjectSchema;

/**
 * @param  class-string<Tool>  $toolClass
 */
function assertToolSatisfiesOpenAiStrictSchema(string $toolClass): void
{
    $tool = new $toolClass((string) Str::uuid());
    $serialized = (new ObjectSchema($tool->schema(new JsonSchemaTypeFactory)))->toSchema();
    $properties = array_keys($serialized['properties'] ?? []);
    $required = $serialized['required'] ?? [];

    expect(array_diff($properties, $required))->toBe([]);
}

test('sales agent tools satisfy openai strict function schemas', function (string $toolClass) {
    assertToolSatisfiesOpenAiStrictSchema($toolClass);
})->with([
    CreateCustomer::class,
    CreateCustomerAddress::class,
    CreateOrder::class,
    GetCustomerByPhone::class,
    GetProducts::class,
]);
