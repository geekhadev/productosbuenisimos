<?php

namespace App\Models\Shared;

use Database\Factories\Shared\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'name_code',
    'phone_code',
    'currency_name',
    'currency_symbol',
])]
class Country extends Model
{
    protected $table = 'shared_countries';

    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    public const SORTABLE_COLUMNS = [
        'name',
        'name_code',
        'phone_code',
        'currency_name',
        'currency_symbol',
        'created_at',
    ];

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
                ->orWhere('name_code', 'like', $term)
                ->orWhere('phone_code', 'like', $term)
                ->orWhere('currency_name', 'like', $term)
                ->orWhere('currency_symbol', 'like', $term);
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

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    /**
     * @return HasMany<State, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
