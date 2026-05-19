<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetProducts;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class SalesAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(private readonly string $companyId) {}

    public function instructions(): Stringable|string
    {
        return file_get_contents(resource_path('ai/prompts/agent-ventas.md'));
    }

    public function tools(): iterable
    {
        return [
            new GetProducts($this->companyId),
            new GetCustomerByPhone($this->companyId),
            new CreateCustomer($this->companyId),
            new CreateCustomerAddress($this->companyId),
            new CreateOrder($this->companyId),
        ];
    }
}
