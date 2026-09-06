<?php

declare(strict_types=1);

namespace AzPays\Laravel\Facades;

use AzPays\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AzPays\Services\PaymentService payments()
 * @method static \AzPays\Services\CheckoutService checkout()
 * @method static \AzPays\Services\InvoiceService invoices()
 * @method static \AzPays\Services\PaymentLinkService paymentLinks()
 * @method static \AzPays\Services\WebhookService webhooks()
 * @method static \AzPays\Services\WalletService wallets()
 * @method static \AzPays\Services\PriceService prices()
 * @method static \AzPays\Services\PayoutService payouts()
 * @method static \AzPays\Services\MerchantService merchants()
 * @method static \AzPays\Config getConfig()
 * @method static \AzPays\Http\Transport getTransport()
 *
 * @see \AzPays\Client
 */
class AzPays extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'azpays';
    }

    /**
     * Handle dynamic, static calls to the object.
     * Allows AzPays::payments() syntax to resolve the public readonly property $client->payments.
     *
     * @param string $method
     * @param array<mixed> $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        if (property_exists($instance, $method)) {
            return $instance->$method;
        }

        return $instance->$method(...$args);
    }

    /**
     * Replace the bound instance with a fake/mock for testing.
     *
     * @param mixed $fake
     * @return mixed
     */
    public static function fake(mixed $fake = null): mixed
    {
        if ($fake === null) {
            $fake = \Mockery::mock(Client::class);
        }

        static::swap($fake);

        return $fake;
    }
}
