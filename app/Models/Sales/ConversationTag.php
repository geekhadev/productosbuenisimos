<?php

namespace App\Models\Sales;

use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use App\Support\SelectedCompanySession;

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

    protected $table = 'sales_conversation_tags';

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
