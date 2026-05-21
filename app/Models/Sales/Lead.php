<?php

namespace App\Models\Sales;

use App\Enums\Sales\LeadSource;
use App\Enums\Sales\LeadStatus;
use App\Models\Company;
use App\Support\SelectedCompanySession;
use Database\Factories\Sales\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

#[Fillable([
    'company_id',
    'phone',
    'source',
    'status',
    'customer_id',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sales_leads';

    public const SORTABLE_COLUMNS = [
        'phone',
        'source',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
        ];
    }

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
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearchFields(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';

        return $query->where('phone', 'like', $term);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWithSource(Builder $query, ?string $source): Builder
    {
        if ($source === null || $source === '') {
            return $query;
        }

        return $query->where('source', $source);
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

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
