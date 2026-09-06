<?php

declare(strict_types=1);

namespace AzPays\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AzpaysWebhookReceived
{
    use Dispatchable, SerializesModels;

    public string $event;
    public array $data;
    public array $payload;

    /**
     * @param string $event Event type (e.g. "payment.confirmed", "payment.failed")
     * @param array<string, mixed> $data Event data payload
     * @param array<string, mixed> $payload Full raw webhook payload
     */
    public function __construct(string $event, array $data, array $payload = [])
    {
        $this->event = $event;
        $this->data = $data;
        $this->payload = $payload;
    }

    public function isPayment(): bool
    {
        return str_starts_with($this->event, 'payment.');
    }

    public function isPayout(): bool
    {
        return str_starts_with($this->event, 'payout.');
    }

    public function isInvoice(): bool
    {
        return str_starts_with($this->event, 'invoice.');
    }

    public function paymentId(): ?string
    {
        return $this->data['id'] ?? $this->data['payment_id'] ?? null;
    }
}
