<?php

namespace App\Models\Sales;

use App\Models\Company;
use App\Support\AiConfigurationBridge;
use Database\Factories\Sales\SalesAgentConfigFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'company_id',
    'enabled_tools',
    'provider',
    'model',
    'prompt',
])]
class SalesAgentConfig extends Model
{
    /** @use HasFactory<SalesAgentConfigFactory> */
    use HasFactory, HasUuids;

    public const DEFAULT_PROVIDER = 'openai';

    public const TOOL_GET_PRODUCTS = 'get_products';

    public const TOOL_GET_CUSTOMER_BY_PHONE = 'get_customer_by_phone';

    public const TOOL_CREATE_CUSTOMER = 'create_customer';

    public const TOOL_CREATE_CUSTOMER_ADDRESS = 'create_customer_address';

    public const TOOL_CREATE_ORDER = 'create_order';

    public const TOOL_GET_CONVERSATION_TAGS = 'get_conversation_tags';

    public const TOOL_UPDATE_CONVERSATION_TAG = 'update_conversation_tag';

    public const TOOL_GET_PENDING_ORDERS_FOR_CUSTOMER = 'get_pending_orders_for_customer';

    public const TOOL_ADD_ITEMS_TO_ORDER = 'add_items_to_order';

    public const TOOL_GET_SIMILAR_PRODUCTS = 'get_similar_products';

    /**
     * @var list<string>
     */
    public const TOOL_SLUGS = [
        self::TOOL_GET_PRODUCTS,
        self::TOOL_GET_CUSTOMER_BY_PHONE,
        self::TOOL_CREATE_CUSTOMER,
        self::TOOL_CREATE_CUSTOMER_ADDRESS,
        self::TOOL_CREATE_ORDER,
        self::TOOL_GET_CONVERSATION_TAGS,
        self::TOOL_UPDATE_CONVERSATION_TAG,
        self::TOOL_GET_PENDING_ORDERS_FOR_CUSTOMER,
        self::TOOL_ADD_ITEMS_TO_ORDER,
        self::TOOL_GET_SIMILAR_PRODUCTS,
    ];

    /**
     * @var array<string, array{label: string, description: string}>
     */
    public const TOOL_DEFINITIONS = [
        self::TOOL_GET_PRODUCTS => [
            'label' => 'Consultar catálogo de productos',
            'description' => 'Permite al agente listar productos disponibles para el pedido.',
        ],
        self::TOOL_GET_CUSTOMER_BY_PHONE => [
            'label' => 'Buscar cliente por teléfono',
            'description' => 'Localiza un cliente existente por su número de teléfono.',
        ],
        self::TOOL_CREATE_CUSTOMER => [
            'label' => 'Crear cliente nuevo',
            'description' => 'Registra un cliente cuando no existe en el sistema.',
        ],
        self::TOOL_CREATE_CUSTOMER_ADDRESS => [
            'label' => 'Agregar dirección de entrega',
            'description' => 'Añade una dirección de entrega al cliente.',
        ],
        self::TOOL_CREATE_ORDER => [
            'label' => 'Registrar pedido',
            'description' => 'Crea el pedido con los productos seleccionados.',
        ],
        self::TOOL_GET_CONVERSATION_TAGS => [
            'label' => 'Consultar etiquetas de conversación',
            'description' => 'Permite al agente obtener las etiquetas del embudo de conversión configuradas.',
        ],
        self::TOOL_UPDATE_CONVERSATION_TAG => [
            'label' => 'Actualizar etiqueta de conversación',
            'description' => 'Permite al agente registrar en qué etapa del embudo quedó el cliente.',
        ],
        self::TOOL_GET_PENDING_ORDERS_FOR_CUSTOMER => [
            'label' => 'Consultar pedidos pendientes del cliente',
            'description' => 'Obtiene los pedidos del cliente que aún no han sido enviados a fulfillment, para ofrecer venta cruzada sobre un pedido abierto.',
        ],
        self::TOOL_ADD_ITEMS_TO_ORDER => [
            'label' => 'Agregar productos a pedido existente',
            'description' => 'Agrega ítems de venta cruzada a un pedido pendiente de fulfillment, sumando cantidades si el producto ya existe.',
        ],
        self::TOOL_GET_SIMILAR_PRODUCTS => [
            'label' => 'Consultar productos similares',
            'description' => 'Obtiene los productos similares configurados para los productos de un pedido, para usarlos en la venta cruzada.',
        ],
    ];

    protected $table = 'sales_agent_configs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_tools' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public static function forCompany(string $companyId): ?static
    {
        $key = self::cacheKey($companyId);
        $cached = Cache::get($key);

        if ($cached instanceof static) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($key);
        }

        $configId = Cache::remember(
            $key,
            60,
            fn (): ?string => static::query()
                ->where('company_id', $companyId)
                ->value('id'),
        );

        if ($configId === null) {
            return null;
        }

        return static::query()->find($configId);
    }

    public static function forgetCacheForCompany(string $companyId): void
    {
        Cache::forget(self::cacheKey($companyId));
    }

    /**
     * @return list<string>
     */
    public static function defaultEnabledTools(): array
    {
        return self::TOOL_SLUGS;
    }

    public static function defaultProvider(): string
    {
        $available = self::configuredProviders();

        if ($available === []) {
            return self::DEFAULT_PROVIDER;
        }

        $configDefault = (string) config('ai.default', self::DEFAULT_PROVIDER);

        if (in_array($configDefault, $available, true)) {
            return $configDefault;
        }

        return $available[0];
    }

    /**
     * @return list<string>
     */
    public static function configuredProviders(): array
    {
        return AiConfigurationBridge::availableSalesAgentProviderSlugs();
    }

    /**
     * @return list<string>
     */
    public static function allowedProviders(): array
    {
        return self::configuredProviders();
    }

    public static function resolveProvider(?string $provider): string
    {
        $available = self::configuredProviders();

        if ($provider !== null && in_array($provider, $available, true)) {
            return $provider;
        }

        return self::defaultProvider();
    }

    /**
     * @return list<string>
     */
    public static function allowedModelsForProvider(string $provider): array
    {
        /** @var list<array{value: string, label: string}> $models */
        $models = config("sales-agent.providers.{$provider}.models", []);

        return array_column($models, 'value');
    }

    public static function defaultModel(): ?string
    {
        return self::resolveModel(self::defaultProvider(), null);
    }

    public static function resolveModel(string $provider, ?string $model): ?string
    {
        $allowedModels = self::allowedModelsForProvider($provider);

        if ($model !== null && in_array($model, $allowedModels, true)) {
            return $model;
        }

        return $allowedModels[0] ?? null;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function providersForFrontend(): array
    {
        /** @var array<string, array{label: string, models: list<array{value: string, label: string}>}> $catalog */
        $catalog = config('sales-agent.providers', []);

        return array_values(array_map(
            fn (string $slug): array => [
                'value' => $slug,
                'label' => $catalog[$slug]['label'],
            ],
            self::configuredProviders(),
        ));
    }

    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    public static function providerModelsForFrontend(): array
    {
        /** @var array<string, array{label: string, models: list<array{value: string, label: string}>}> $providers */
        $providers = config('sales-agent.providers', []);

        $configured = array_intersect_key(
            $providers,
            array_flip(self::configuredProviders()),
        );

        return array_map(
            fn (array $provider): array => $provider['models'],
            $configured,
        );
    }

    public static function defaultPrompt(): string
    {
        return file_get_contents(resource_path('ai/prompts/agent-ventas.md'));
    }

    /**
     * @return list<array{slug: string, label: string, description: string}>
     */
    public static function toolsForFrontend(): array
    {
        return array_map(
            fn (string $slug): array => [
                'slug' => $slug,
                'label' => self::TOOL_DEFINITIONS[$slug]['label'],
                'description' => self::TOOL_DEFINITIONS[$slug]['description'],
            ],
            self::TOOL_SLUGS,
        );
    }

    protected static function newFactory(): SalesAgentConfigFactory
    {
        return SalesAgentConfigFactory::new();
    }

    private static function cacheKey(string $companyId): string
    {
        return "sales_agent_config:{$companyId}";
    }
}
