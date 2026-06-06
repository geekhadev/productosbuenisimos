<?php

namespace App\Models\Public;

use App\Enums\ChatbotSource;
use App\Models\Company;
use App\Support\SelectedCompanySession;
use Database\Factories\Public\ChatbotConversationFactory;
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
    'phone',
    'agent_conversation_id',
    'source',
    'is_active',
    'agent_paused',
])]
class ChatbotConversation extends Model
{
    /** @use HasFactory<ChatbotConversationFactory> */
    use HasFactory, HasUuids;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return HasMany<ChatbotMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'chatbot_conversation_id')
            ->orderBy('created_at');
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

    protected static function newFactory(): ChatbotConversationFactory
    {
        return ChatbotConversationFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => ChatbotSource::class,
            'is_active' => 'boolean',
            'agent_paused' => 'boolean',
        ];
    }
}
