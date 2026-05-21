<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetProducts;
use App\Models\Sales\SalesAgentConfig;
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

    public function provider(): string
    {
        $config = SalesAgentConfig::forCompany($this->companyId);

        return $config?->provider ?? SalesAgentConfig::defaultProvider();
    }

    public function model(): ?string
    {
        $config = SalesAgentConfig::forCompany($this->companyId);
        $model = $config?->model;

        return filled($model) ? $model : null;
    }

    public function instructions(): Stringable|string
    {
        $config = SalesAgentConfig::forCompany($this->companyId);

        if ($config && filled($config->prompt)) {
            return $config->prompt;
        }

        return SalesAgentConfig::defaultPrompt();
    }

    public function tools(): iterable
    {
        $config = SalesAgentConfig::forCompany($this->companyId);
        $enabled = $config?->enabled_tools ?? $this->defaultTools();

        $allTools = [
            SalesAgentConfig::TOOL_GET_PRODUCTS => new GetProducts($this->companyId),
            SalesAgentConfig::TOOL_GET_CUSTOMER_BY_PHONE => new GetCustomerByPhone($this->companyId),
            SalesAgentConfig::TOOL_CREATE_CUSTOMER => new CreateCustomer($this->companyId),
            SalesAgentConfig::TOOL_CREATE_CUSTOMER_ADDRESS => new CreateCustomerAddress($this->companyId),
            SalesAgentConfig::TOOL_CREATE_ORDER => new CreateOrder($this->companyId),
        ];

        return array_values(array_intersect_key($allTools, array_flip($enabled)));
    }

    /**
     * @return list<string>
     */
    private function defaultTools(): array
    {
        return SalesAgentConfig::defaultEnabledTools();
    }
}
