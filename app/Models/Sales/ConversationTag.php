<?php

namespace App\Models\Sales;

use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Support\SelectedCompanySession;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

#[Fillable([
    'company_id',
    'name',
    'description',
    'color',
    'sort_order',
])]
class ConversationTag extends Model
{
    use HasUuids;

    public const SORT_CONVERSATION_STARTED = 0;

    public const SORT_CONTACT_STARTED = 1;

    public const SORT_PRODUCT_CONSULTED = 2;

    public const SORT_PRODUCT_PRESENTED = 3;

    public const SORT_INTEREST_CONFIRMED = 4;

    public const SORT_ADDRESS_IN_PROGRESS = 5;

    public const SORT_ADDRESS_COMPLETE = 6;

    public const SORT_ORDER_CONFIRMED = 7;

    public const SORT_ORDER_REGISTERED = 8;

    public const SORT_CROSS_SELL_OFFERED = 9;

    protected $table = 'sales_conversation_tags';

    /**
     * @return array{id: string, name: string, color: string, sort_order: int}
     */
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
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
     * @return HasMany<ChatbotConversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class, 'conversation_tag_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function resolveRouteBinding($value, $field = null): static
    {
        $request = app(Request::class);
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        /** @var self */
        return static::query()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->where('company_id', $companyId)
            ->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
