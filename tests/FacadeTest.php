<?php

declare(strict_types=1);

namespace AzPays\Laravel\Tests;

use AzPays\Client;
use AzPays\Laravel\Facades\AzPays;
use AzPays\Services\CheckoutService;
use AzPays\Services\InvoiceService;
use AzPays\Services\MerchantService;
use AzPays\Services\PaymentLinkService;
use AzPays\Services\PaymentService;
use AzPays\Services\PayoutService;
use AzPays\Services\PriceService;
use AzPays\Services\WalletService;
use AzPays\Services\WebhookService;

class FacadeTest extends TestCase
{
    public function testFacadeResolvesMerchantServices(): void
    {
        $this->assertInstanceOf(PaymentService::class, AzPays::payments());
        $this->assertInstanceOf(CheckoutService::class, AzPays::checkout());
        $this->assertInstanceOf(InvoiceService::class, AzPays::invoices());
        $this->assertInstanceOf(PaymentLinkService::class, AzPays::paymentLinks());
        $this->assertInstanceOf(WebhookService::class, AzPays::webhooks());
        $this->assertInstanceOf(WalletService::class, AzPays::wallets());
        $this->assertInstanceOf(PriceService::class, AzPays::prices());
        $this->assertInstanceOf(PayoutService::class, AzPays::payouts());
        $this->assertInstanceOf(MerchantService::class, AzPays::merchants());
    }

    public function testFacadeFakeSwapsInstance(): void
    {
        $mock = \Mockery::mock(Client::class);
        AzPays::fake($mock);

        $this->assertSame($mock, $this->app->make('azpays'));
    }
}
