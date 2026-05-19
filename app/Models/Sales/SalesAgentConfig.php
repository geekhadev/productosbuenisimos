<?php

namespace App\Models\Sales;

use App\Models\Company;
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
    'prompt',
])]
class SalesAgentConfig extends Model
{
    /** @use HasFactory<SalesAgentConfigFactory> */
    use HasFactory, HasUuids;

    public const TOOL_GET_PRODUCTS = 'get_products';

    public const TOOL_GET_CUSTOMER_BY_PHONE = 'get_customer_by_phone';

    public const TOOL_CREATE_CUSTOMER = 'create_customer';

    public const TOOL_CREATE_CUSTOMER_ADDRESS = 'create_customer_address';

    public const TOOL_CREATE_ORDER = 'create_order';

    /**
     * @var list<string>
     */
    public const TOOL_SLUGS = [
        self::TOOL_GET_PRODUCTS,
        self::TOOL_GET_CUSTOMER_BY_PHONE,
        self::TOOL_CREATE_CUSTOMER,
        self::TOOL_CREATE_CUSTOMER_ADDRESS,
        self::TOOL_CREATE_ORDER,
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
        return Cache::remember(
            self::cacheKey($companyId),
            60,
            fn (): ?static => static::query()->where('company_id', $companyId)->first(),
        );
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
