<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AddItemsToOrder;
use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\GetSimilarProducts;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetConversationTags;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetPendingOrdersForCustomer;
use App\Ai\Tools\GetProducts;
use App\Ai\Tools\UpdateConversationTag;
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

    public function __construct(
        private readonly string $companyId,
        private readonly ?string $chatbotConversationId = null,
    ) {}

    public function provider(): string
    {
        $config = SalesAgentConfig::forCompany($this->companyId);

        return SalesAgentConfig::resolveProvider($config?->provider);
    }

    public function model(): ?string
    {
        $config = SalesAgentConfig::forCompany($this->companyId);
        $provider = SalesAgentConfig::resolveProvider($config?->provider);

        return SalesAgentConfig::resolveModel($provider, $config?->model);
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
            SalesAgentConfig::TOOL_GET_CONVERSATION_TAGS => new GetConversationTags($this->companyId),
            SalesAgentConfig::TOOL_UPDATE_CONVERSATION_TAG => new UpdateConversationTag(
                companyId: $this->companyId,
                chatbotConversationId: $this->chatbotConversationId ?? '',
            ),
            SalesAgentConfig::TOOL_GET_PENDING_ORDERS_FOR_CUSTOMER => new GetPendingOrdersForCustomer($this->companyId),
            SalesAgentConfig::TOOL_ADD_ITEMS_TO_ORDER => new AddItemsToOrder($this->companyId),
            SalesAgentConfig::TOOL_GET_SIMILAR_PRODUCTS => new GetSimilarProducts($this->companyId),
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
