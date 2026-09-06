<?php

declare(strict_types=1);

namespace AzPays\Laravel\Tests;

use AzPays\Laravel\AzpaysServiceProvider;
use AzPays\Laravel\Facades\AzPays;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AzpaysServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'AzPays' => AzPays::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('azpays.api_key', 'az_test_laravel_mock_key');
        $app['config']->set('azpays.base_url', 'https://api.azpays.net');
        $app['config']->set('azpays.timeout', 20);
        $app['config']->set('azpays.max_retries', 2);
        $app['config']->set('azpays.webhook.secret', 'whsec_laravel_test_secret');
        $app['config']->set('azpays.webhook.path', 'azpays/webhook');
    }
}
