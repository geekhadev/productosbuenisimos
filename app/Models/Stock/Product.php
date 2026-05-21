<?php

namespace App\Models\Stock;

use App\Enums\Stock\ProductMediaType;
use App\Models\Company;
use App\Support\SelectedCompanySession;
use Database\Factories\Stock\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

#[Fillable([
    'company_id',
    'name',
    'code',
    'sku',
    'width',
    'length',
    'height',
    'volume',
    'weight',
    'minimum_stock',
    'price',
    'description',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'stock_products';

    public const SORTABLE_COLUMNS = [
        'name',
        'code',
        'sku',
        'width',
        'length',
        'height',
        'volume',
        'weight',
        'minimum_stock',
        'price',
        'is_active',
        'created_at',
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return HasMany<ProductMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'product_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWithLandingImages(Builder $query): Builder
    {
        return $query->with(['media' => function ($relation): void {
            $relation
                ->where('type', ProductMediaType::Image->value)
                ->orderBy('sort_order')
                ->orderBy('created_at');
        }]);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearchFields(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where(function (Builder $inner) use ($term): void {
            $inner->where('name', 'like', $term)
                ->orWhere('code', 'like', $term)
                ->orWhere('sku', 'like', $term);
        });
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeOrderByColumn(Builder $query, string $column, string $direction): Builder
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActiveStatus(Builder $query, ?string $status): Builder
    {
        if ($status === 'active') {
            return $query->where('is_active', true);
        }

        if ($status === 'inactive') {
            return $query->where('is_active', false);
        }

        return $query;
    }

    /**
     * Resolución de ruta acotada a la empresa seleccionada en sesión (evita acceso cruzado entre compañías).
     */
    public function resolveRouteBinding($value, $field = null): static
    {
        $request = app(Request::class);
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $query = static::query()->where($field ?? $this->getRouteKeyName(), $value)
            ->where('company_id', $companyId);

        /** @var self */
        return $query->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'decimal:3',
            'length' => 'decimal:3',
            'height' => 'decimal:3',
            'volume' => 'decimal:3',
            'weight' => 'decimal:3',
            'minimum_stock' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
