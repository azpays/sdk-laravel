<?php

declare(strict_types=1);

namespace AzPays\Laravel\Tests;

use AzPays\Client;

class ServiceProviderTest extends TestCase
{
    public function testClientIsBoundInContainerAsSingleton(): void
    {
        $client1 = $this->app->make(Client::class);
        $client2 = $this->app->make('azpays');

        $this->assertInstanceOf(Client::class, $client1);
        $this->assertInstanceOf(Client::class, $client2);
        $this->assertSame($client1, $client2);
    }

    public function testConfigValuesPassedToClient(): void
    {
        /** @var Client $client */
        $client = $this->app->make(Client::class);

        $this->assertSame('az_test_laravel_mock_key', $client->getConfig()->getApiKey());
        $this->assertSame('https://api.azpays.net', $client->getConfig()->getBaseUrl());
        $this->assertSame(20, $client->getConfig()->getTimeout());
        $this->assertSame(2, $client->getConfig()->getMaxRetries());
    }
}
