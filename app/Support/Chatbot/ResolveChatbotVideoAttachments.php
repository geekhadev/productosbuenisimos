<?php

namespace App\Support\Chatbot;

use App\Actions\AI\Tools\GetProductsAction;
use App\Enums\ChatbotMessageRole;
use App\Models\Public\ChatbotConversation;
use App\Models\Stock\Product;
use App\Support\Stock\ProductMediaPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\ToolResult;

class ResolveChatbotVideoAttachments
{
    /**
     * @var list<string>
     */
    private const STOP_WORDS = [
        'para', 'como', 'este', 'esta', 'estos', 'estas', 'con', 'sin', 'por',
        'una', 'uno', 'del', 'los', 'las', 'que', 'son', 'mas', 'muy', 'cada',
        'tipo', 'otro', 'otra', 'producto', 'productos', 'contamos', 'tambien',
        'interesarle', 'agregar', 'pedido', 'don', 'dona', 'donde', 'desde',
        'hasta', 'sobre', 'entre', 'hacia', 'tiene', 'tienen', 'puede', 'pueden',
    ];

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     * @return list<array{type: string, url: string, product_name: string}>
     */
    public function resolve(
        string $companyId,
        AgentResponse $response,
        string $userMessage,
        ?array $productContext,
        ChatbotConversation $conversation,
    ): array {
        $products = $this->collectRelevantProducts(
            $companyId,
            $response,
            $userMessage,
            $productContext,
            $conversation,
        );

        $shouldAttach = $this->shouldAttachMedia($response, $products, $productContext);

        Log::info('ResolveChatbotVideoAttachments: evaluación', [
            'conversation_id' => $conversation->id,
            'products_found' => $products->count(),
            'product_names' => $products->pluck('name')->all(),
            'products_with_images' => $products->filter(fn ($p) => ! empty($p['images'] ?? []))->count(),
            'products_with_video' => $products->filter(fn ($p) => filled($p['video']['url'] ?? null))->count(),
            'should_attach_media' => $shouldAttach,
            'get_products_invoked' => $this->getProductsWasInvoked($response),
            'has_product_context' => $productContext !== null,
        ]);

        if ($products->isEmpty() || ! $shouldAttach) {
            return [];
        }

        $alreadySentVideoUrls = $this->alreadySentVideoUrls($conversation);
        $alreadySentImageUrls = $this->alreadySentImageUrls($conversation);

        $videoAttachments = $products
            ->filter(fn (array $product): bool => filled($product['video']['url'] ?? null))
            ->unique('id')
            ->reject(fn (array $product): bool => in_array($this->absoluteUrl((string) $product['video']['url']), $alreadySentVideoUrls, true))
            ->map(fn (array $product): array => [
                'type' => 'video',
                'url' => $this->absoluteUrl((string) $product['video']['url']),
                'product_name' => $product['name'],
            ])
            ->values();

        $productsWithVideoIds = $products
            ->filter(fn (array $product): bool => filled($product['video']['url'] ?? null))
            ->pluck('id')
            ->all();

        $imageAttachments = $products
            ->filter(fn (array $product): bool => ! in_array($product['id'] ?? null, $productsWithVideoIds, true)
                && ! empty($product['images'] ?? []))
            ->unique('id')
            ->map(fn (array $product): ?array => $this->firstImageAttachment($product, $alreadySentImageUrls))
            ->filter()
            ->values();

        $resolved = $videoAttachments->merge($imageAttachments)->all();

        Log::info('ResolveChatbotVideoAttachments: attachments resueltos', [
            'conversation_id' => $conversation->id,
            'total' => count($resolved),
            'attachments' => array_map(fn ($a) => [
                'type' => $a['type'],
                'url' => $a['url'],
                'product_name' => $a['product_name'],
            ], $resolved),
        ]);

        return $resolved;
    }

    public function sanitizeAgentText(string $text): string
    {
        $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/u', '', $text) ?? $text;
        $text = preg_replace('/\[(?:Ver\s+)?[Vv]ideo[^\]]*\]\([^)]*\)/u', '', $text) ?? $text;
        $text = preg_replace('/\[(?:Ver\s+)?[Ii]magen[^\]]*\]\([^)]*\)/u', '', $text) ?? $text;
        $text = preg_replace('/[ \t]+$/m', '', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     * @param  Collection<int, array<string, mixed>>  $products
     */
    private function shouldAttachMedia(
        AgentResponse $response,
        Collection $products,
        ?array $productContext,
    ): bool {
        if ($this->getProductsWasInvoked($response)) {
            return true;
        }

        if ($productContext !== null) {
            return true;
        }

        $agentText = $this->normalize($response->text);

        if ($this->mentionsVideo($agentText)) {
            return true;
        }

        return $products->contains(
            fn (array $product): bool => $this->productMentionedInAgentText($agentText, $product),
        );
    }

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     * @return Collection<int, array<string, mixed>>
     */
    private function collectRelevantProducts(
        string $companyId,
        AgentResponse $response,
        string $userMessage,
        ?array $productContext,
        ChatbotConversation $conversation,
    ): Collection {
        $catalog = collect((new GetProductsAction)->execute($companyId));

        $products = collect();

        if ($productContext !== null) {
            $products = $this->productFromContext($companyId, $productContext);
        }

        $userSearchText = $this->conversationUserText($conversation).' '.$userMessage;
        $agentText = $this->normalize($response->text);

        $userMatched = $catalog->filter(
            fn (array $product): bool => $this->matchesProduct($userSearchText, $product),
        );

        if ($this->getSimilarProductsWasInvoked($response)) {
            $crossSellMatched = $this->productsFromSimilarProductsTool($response)
                ->filter(fn (array $product): bool => $this->productMentionedInAgentText($agentText, $product));

            return $products
                ->merge($userMatched)
                ->merge($crossSellMatched)
                ->unique('id')
                ->values();
        }

        $agentMatchedFromCatalog = $catalog->filter(
            fn (array $product): bool => $this->productMentionedInAgentText($agentText, $product),
        );

        return $products
            ->merge($userMatched)
            ->merge($agentMatchedFromCatalog)
            ->unique('id')
            ->values();
    }

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}  $productContext
     * @return Collection<int, array<string, mixed>>
     */
    private function productFromContext(string $companyId, array $productContext): Collection
    {
        $product = Product::query()
            ->forCompany($companyId)
            ->where('is_active', true)
            ->whereKey($productContext['id'])
            ->with(['media' => fn ($query) => $query->orderBy('sort_order')->orderBy('created_at')])
            ->first([
                'id', 'name', 'code', 'sku', 'price',
                'description',
                'weight', 'width', 'length', 'height', 'volume',
            ]);

        if ($product === null) {
            return collect();
        }

        return collect([[
            ...$product->only([
                'id', 'name', 'code', 'sku', 'price',
                'description',
                'weight', 'width', 'length', 'height', 'volume',
            ]),
            ...ProductMediaPayload::fromMedia($product->media),
        ]]);
    }

    private function conversationUserText(ChatbotConversation $conversation): string
    {
        return $conversation->messages()
            ->where('role', ChatbotMessageRole::User)
            ->orderBy('created_at')
            ->pluck('content')
            ->implode(' ');
    }

    private function getProductsWasInvoked(AgentResponse $response): bool
    {
        return $response->toolResults->contains(
            fn (ToolResult $toolResult): bool => $toolResult->name === 'get_products',
        );
    }

    private function getSimilarProductsWasInvoked(AgentResponse $response): bool
    {
        return $response->toolResults->contains(
            fn (ToolResult $toolResult): bool => $toolResult->name === 'get_similar_products',
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function productsFromSimilarProductsTool(AgentResponse $response): Collection
    {
        $toolResult = $response->toolResults->first(
            fn (ToolResult $toolResult): bool => $toolResult->name === 'get_similar_products',
        );

        if ($toolResult === null) {
            return collect();
        }

        $decoded = is_string($toolResult->result)
            ? json_decode($toolResult->result, true)
            : $toolResult->result;

        if (! is_array($decoded)) {
            return collect();
        }

        return collect($decoded)->filter(fn (mixed $item): bool => is_array($item) && filled($item['id'] ?? null));
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function matchesProduct(string $haystack, array $product): bool
    {
        $haystack = $this->normalize($haystack);

        foreach ([$product['name'] ?? '', $product['code'] ?? '', $product['sku'] ?? ''] as $needle) {
            if (! is_string($needle) || $needle === '') {
                continue;
            }

            $normalizedNeedle = $this->normalize($needle);

            if (str_contains($haystack, $normalizedNeedle)) {
                return true;
            }

            foreach ($this->significantTokens($normalizedNeedle) as $token) {
                if (str_contains($haystack, $token)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function productMentionedInAgentText(string $normalizedText, array $product): bool
    {
        $name = $this->normalize((string) ($product['name'] ?? ''));

        if ($name === '') {
            return false;
        }

        if (str_contains($normalizedText, $name)) {
            return true;
        }

        $tokens = $this->significantTokens($name);

        if ($tokens === []) {
            return false;
        }

        $matchedCount = collect($tokens)
            ->filter(fn (string $token): bool => str_contains($normalizedText, $token))
            ->count();

        if (count($tokens) === 1) {
            return $matchedCount === 1 && mb_strlen($tokens[0]) >= 5;
        }

        return $matchedCount >= 2;
    }

    private function mentionsVideo(string $normalizedText): bool
    {
        return str_contains($normalizedText, 'video')
            || str_contains($normalizedText, 'funcionamiento');
    }

    /**
     * @return list<string>
     */
    private function significantTokens(string $normalizedText): array
    {
        return collect(explode(' ', $normalizedText))
            ->map(fn (string $token): string => trim($token))
            ->filter(fn (string $token): bool => mb_strlen($token) >= 4)
            ->reject(fn (string $token): bool => in_array($token, self::STOP_WORDS, true))
            ->unique()
            ->values()
            ->all();
    }

    private function normalize(string $text): string
    {
        return Str::of($text)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->squish()
            ->value();
    }

    /**
     * @return list<string>
     */
    private function alreadySentVideoUrls(ChatbotConversation $conversation): array
    {
        return $conversation->messages()
            ->where('role', ChatbotMessageRole::Assistant)
            ->whereNotNull('attachments')
            ->pluck('attachments')
            ->flatten(1)
            ->filter(fn (mixed $attachment): bool => is_array($attachment) && ($attachment['type'] ?? '') === 'video' && filled($attachment['url'] ?? null))
            ->pluck('url')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function alreadySentImageUrls(ChatbotConversation $conversation): array
    {
        return $conversation->messages()
            ->where('role', ChatbotMessageRole::Assistant)
            ->whereNotNull('attachments')
            ->pluck('attachments')
            ->flatten(1)
            ->filter(fn (mixed $attachment): bool => is_array($attachment) && ($attachment['type'] ?? '') === 'image' && filled($attachment['url'] ?? null))
            ->pluck('url')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  list<string>  $alreadySentImageUrls
     * @return array{type: string, url: string, product_name: string}|null
     */
    private function firstImageAttachment(array $product, array $alreadySentImageUrls): ?array
    {
        foreach ($product['images'] ?? [] as $image) {
            $mediaId = (string) ($image['id'] ?? '');
            $url = $mediaId !== '' ? route('whatsapp-media.show', ['mediaId' => $mediaId]) : '';
            $alreadySent = in_array($url, $alreadySentImageUrls, true);

            Log::info('ResolveChatbotVideoAttachments: firstImageAttachment candidato', [
                'product_name' => $product['name'] ?? null,
                'media_id' => $mediaId,
                'url' => $url,
                'already_sent' => $alreadySent,
            ]);

            if (filled($url) && ! $alreadySent) {
                return [
                    'type' => 'image',
                    'url' => $url,
                    'product_name' => (string) ($product['name'] ?? ''),
                ];
            }
        }

        return null;
    }

    private function absoluteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }
}
