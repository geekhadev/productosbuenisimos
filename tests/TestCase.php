<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /** Sin esto, config:cache + RefreshDatabase puede ejecutar migrate:fresh sobre la BD de desarrollo. */
    public function createApplication(): Application
    {
        $this->prepareTestingDatabaseEnvironment();

        return parent::createApplication();
    }

    private function prepareTestingDatabaseEnvironment(): void
    {
        $uses = class_uses_recursive(static::class);

        if (! isset($uses[WithCachedConfig::class])) {
            $cachedConfigPath = dirname(__DIR__).'/bootstrap/cache/config.php';

            if (is_file($cachedConfigPath)) {
                @unlink($cachedConfigPath);
            }
        }

        foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => ''] as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        foreach (['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD', 'DB_SOCKET'] as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
