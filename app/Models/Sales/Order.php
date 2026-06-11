<?php

namespace App\Models\Sales;

use App\Models\Company;
use App\Support\SelectedCompanySession;
use Database\Factories\Sales\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

#[Fillable([
    'company_id',
    'name',
    'customer_id',
    'address_id',
    'total_amount',
    'delivery_date',
    'fulfillment_sent_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUuids;

    protected $table = 'sales_orders';

    public const SORTABLE_COLUMNS = [
        'name',
        'total_amount',
        'created_at',
        'updated_at',
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return BelongsTo<CustomerAddress, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
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

        return $query->where('name', 'like', $term);
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
    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null && $from !== '') {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null && $to !== '') {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Resolución de ruta acotada a la empresa seleccionada en sesión.
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
            'total_amount' => 'decimal:2',
            'delivery_date' => 'date',
            'fulfillment_sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
