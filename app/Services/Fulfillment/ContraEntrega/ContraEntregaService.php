<?php

namespace App\Services\Fulfillment\ContraEntrega;

use App\Models\Configuration\FulfillmentProviderCredential;
use App\Services\Fulfillment\ContraEntrega\Data\ContraEntregaOrderData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class ContraEntregaService
{
    private const PROVIDER_SLUG = 'contraentrega';
    private const CACHE_KEY = 'contraentrega_bearer_token';
    private const CACHE_TTL_HOURS = 12;

    private string $apiUrl;
    private string $email;
    private string $password;

    public function __construct()
    {
        $credential = FulfillmentProviderCredential::findForProvider(self::PROVIDER_SLUG);

        if ($credential === null || ! $credential->hasStoredCredentials()) {
            throw new RuntimeException('No hay credenciales configuradas para ContraEntrega.');
        }

        /** @var array<string, string> $credentials */
        $credentials = $credential->credentials;

        $this->apiUrl = rtrim($credentials['api_url'], '/');
        $this->email = $credentials['user'];
        $this->password = $credentials['pass'];
    }

    public function createOrder(ContraEntregaOrderData $order): void
    {
        $token = $this->resolveToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/orders", $order->toArray());

        if (! $response->successful()) {
            throw new RuntimeException(
                "ContraEntrega rechazó la orden {$order->orderNumber}. "
                ."HTTP {$response->status()}: "
                .($response->json('message') ?? $response->body())
            );
        }
    }

    private function resolveToken(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && filled($cached)) {
            return $cached;
        }

        $response = Http::post("{$this->apiUrl}/login", [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'No se pudo autenticar con ContraEntrega. '
                ."HTTP {$response->status()}: "
                .($response->json('message') ?? $response->body())
            );
        }

        $token = $response->json('token');

        if (! is_string($token) || ! filled($token)) {
            throw new RuntimeException('ContraEntrega no devolvió un token válido en la respuesta de login.');
        }

        Cache::put(self::CACHE_KEY, $token, now()->addHours(self::CACHE_TTL_HOURS));

        return $token;
    }
}
