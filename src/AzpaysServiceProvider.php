<?php

declare(strict_types=1);

namespace AzPays\Laravel;

use AzPays\Client;
use AzPays\Laravel\Http\Controllers\WebhookController;
use AzPays\Laravel\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AzpaysServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/azpays.php', 'azpays');

        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']->get('azpays', []);

            return new Client((string) ($config['api_key'] ?? ''), [
                'base_url'    => $config['base_url'] ?? null,
                'timeout'     => (int) ($config['timeout'] ?? 30),
                'max_retries' => (int) ($config['max_retries'] ?? 3),
                'debug'       => (bool) ($config['debug'] ?? false),
            ]);
        });

        $this->app->alias(Client::class, 'azpays');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/azpays.php' => config_path('azpays.php'),
            ], 'azpays-config');
        }

        $this->registerRouteMacro();
        $this->registerWebhookRoute();
    }

    /**
     * Register the Route::azpaysWebhooks macro.
     */
    protected function registerRouteMacro(): void
    {
        Route::macro('azpaysWebhooks', function (string $url = 'azpays/webhook') {
            return Route::post($url, WebhookController::class)
                ->middleware(VerifyWebhookSignature::class)
                ->name('azpays.webhook');
        });
    }

    /**
     * Automatically register webhook route if path is defined in configuration.
     */
    protected function registerWebhookRoute(): void
    {
        $path = config('azpays.webhook.path');
        if (empty($path)) {
            return;
        }

        $middleware = (array) config('azpays.webhook.middleware', []);
        $middleware[] = VerifyWebhookSignature::class;

        Route::post($path, WebhookController::class)
            ->middleware($middleware)
            ->name('azpays.webhook');
    }
}
