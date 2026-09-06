<?php

declare(strict_types=1);

namespace AzPays\Laravel\Tests;

use AzPays\Laravel\Events\AzpaysWebhookReceived;
use Illuminate\Support\Facades\Event;

class WebhookTest extends TestCase
{
    public function testMissingSignatureReturns403(): void
    {
        $response = $this->postJson('/azpays/webhook', [
            'event' => 'payment.confirmed',
            'data' => ['id' => 'pay_123'],
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Invalid signature or expired timestamp.']);
    }

    public function testInvalidSignatureReturns403(): void
    {
        $response = $this->postJson('/azpays/webhook', [
            'event' => 'payment.confirmed',
            'data' => ['id' => 'pay_123'],
        ], [
            'X-AzPays-Signature' => 't=' . time() . ',v1=invalid_signature_hash',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Invalid signature or expired timestamp.']);
    }

    public function testValidSignatureDispatchesEventAndReturns200(): void
    {
        Event::fake();

        $secret = 'whsec_laravel_test_secret';
        $payload = [
            'event' => 'payment.confirmed',
            'data' => ['id' => 'pay_123', 'status' => 4],
        ];
        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$jsonPayload}", $secret);
        $signatureHeader = "t={$timestamp},v1={$signature}";

        $response = $this->call(
            'POST',
            '/azpays/webhook',
            [],
            [],
            [],
            [
                'HTTP_X_AZPAYS_SIGNATURE' => $signatureHeader,
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonPayload
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        Event::assertDispatched(AzpaysWebhookReceived::class, function (AzpaysWebhookReceived $event) {
            return $event->event === 'payment.confirmed'
                && $event->paymentId() === 'pay_123'
                && $event->isPayment() === true
                && $event->isPayout() === false
                && $event->isInvoice() === false;
        });
    }

    public function testMissingWebhookSecretReturns500(): void
    {
        $this->app['config']->set('azpays.webhook.secret', '');

        $response = $this->postJson('/azpays/webhook', ['event' => 'test']);
        $response->assertStatus(500);
        $response->assertJson(['error' => 'AzPays webhook secret is not configured.']);
    }

    public function testRouteMacroCanRegisterCustomUrl(): void
    {
        \Illuminate\Support\Facades\Route::azpaysWebhooks('custom/azpays-endpoint');

        $response = $this->postJson('/custom/azpays-endpoint', ['event' => 'test']);
        $response->assertStatus(403);
    }
}
