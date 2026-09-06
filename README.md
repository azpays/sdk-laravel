# AzPays Laravel SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azpays/sdk-laravel.svg)](https://packagist.org/packages/azpays/sdk-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/azpays/sdk-laravel.svg)](https://packagist.org/packages/azpays/sdk-laravel)
[![License](https://img.shields.io/packagist/l/azpays/sdk-laravel.svg)](https://github.com/azpays/sdk-laravel/blob/main/LICENSE)

Official Laravel wrapper for the [AzPays](https://azpays.net) crypto payment platform. Built on top of [`azpays/sdk-php`](https://github.com/azpays/sdk-php).

## Requirements

- **PHP 8.1** or higher
- **Laravel 10.x** or **11.x**

---

## Installation

Install via Composer:

```bash
composer require azpays/sdk-laravel
```

The package will automatically register its Service Provider (`AzpaysServiceProvider`) and Facade (`AzPays`).

---

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="azpays-config"
```

This will publish `config/azpays.php` into your application's `config` folder.

Add your credentials to your `.env` file:

```env
AZPAYS_API_KEY=az_live_your_merchant_api_key
AZPAYS_WEBHOOK_SECRET=whsec_your_webhook_secret

# Optional settings
AZPAYS_BASE_URL=https://api.azpays.net
AZPAYS_TIMEOUT=30
AZPAYS_MAX_RETRIES=3
AZPAYS_DEBUG=false
```

---

## Usage

### 1. Using the Facade

```php
use AzPays\Laravel\Facades\AzPays;

// Create a payment
$payment = AzPays::payments()->create([
    'fiat_amount'     => 49.99,
    'description'     => 'Order #1001',
    'accepted_chains' => ['trx', 'bnb', 'ton'],
    'accepted_tokens' => ['USDT'],
]);

// Get payment details
$payment = AzPays::payments()->get('payment-id-or-token');

// Create a hosted payment link
$link = AzPays::paymentLinks()->create([
    'title'  => 'Pro Subscription',
    'amount' => 99.00,
]);
```

### 2. Using Dependency Injection

You can type-hint the core `AzPays\Client` directly into your controllers, jobs, or services:

```php
namespace App\Http\Controllers;

use AzPays\Client;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request, Client $azpays)
    {
        $payment = $azpays->payments->create([
            'fiat_amount' => $request->float('amount'),
            'description' => "Order for {$request->user()->email}",
        ]);

        return response()->json($payment);
    }
}
```

---

## Available Services

All merchant-focused services from `azpays/sdk-php` are accessible via the Facade or Client:

| Service | Facade Accessor | Description |
| :--- | :--- | :--- |
| **Payments** | `AzPays::payments()` | Crypto payment creation, retrieval, listing, and metrics |
| **Checkout** | `AzPays::checkout()` | Public checkout sessions, coin selection, rate locking |
| **Invoices** | `AzPays::invoices()` | Full invoice lifecycle (draft, send, finalize, void) |
| **Payment Links** | `AzPays::paymentLinks()` | Hosted reusable payment walls and shortlinks |
| **Webhooks** | `AzPays::webhooks()` | Webhook delivery logs & signature verification |
| **Wallets** | `AzPays::wallets()` | Wallet generation, balance checks, transfers, gas estimation |
| **Prices** | `AzPays::prices()` | Real-time crypto price quotes and 24h candlesticks |
| **Payouts** | `AzPays::payouts()` | Merchant settlement disbursements |
| **Merchants** | `AzPays::merchants()` | Profile info (`me()`), key rotation, and dynamic multi-chain assets |

---

## Webhook Handling

`sdk-laravel` includes built-in webhook handling with automated HMAC-SHA256 signature verification and replay-attack protection.

### 1. Route Setup

By default, the package registers a webhook route at `POST /azpays/webhook` protected by signature verification middleware.

#### CSRF Exemption

Because webhooks come directly from AzPays servers, you must exclude the webhook route from CSRF verification:

**Laravel 11 (`bootstrap/app.php`):**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'azpays/webhook',
    ]);
})
```

**Laravel 10 (`app/Http/Middleware/VerifyCsrfToken.php`):**
```php
protected $except = [
    'azpays/webhook',
];
```

### 2. Listening to Webhook Events

Whenever a valid webhook is received, the package dispatches `AzPays\Laravel\Events\AzpaysWebhookReceived`.

Create an Event Listener:

```bash
php artisan make:listener HandleAzpaysWebhook
```

In your listener:

```php
namespace App\Listeners;

use AzPays\Laravel\Events\AzpaysWebhookReceived;

class HandleAzpaysWebhook
{
    public function handle(AzpaysWebhookReceived $event): void
    {
        switch ($event->event) {
            case 'payment.confirmed':
                $paymentId = $event->paymentId();
                $paymentData = $event->data;

                // Update order status in your database
                // Order::where('payment_id', $paymentId)->update(['status' => 'paid']);
                break;

            case 'payment.failed':
                // Notify user of payment failure
                break;

            case 'payout.completed':
                // Handle settlement payout completion
                break;
        }
    }
}
```

Register the listener in your `EventServiceProvider` (or `AppServiceProvider` in Laravel 11):

```php
use App\Listeners\HandleAzpaysWebhook;
use AzPays\Laravel\Events\AzpaysWebhookReceived;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(
        AzpaysWebhookReceived::class,
        HandleAzpaysWebhook::class,
    );
}
```

---

## Testing

Use `AzPays::fake()` in your PHPUnit or Pest tests:

```php
use AzPays\Laravel\Facades\AzPays;

public function test_payment_creation(): void
{
    $mock = AzPays::fake();
    
    $mock->payments = Mockery::mock();
    $mock->payments->shouldReceive('create')
        ->once()
        ->andReturn(['id' => 'pay_mock_123', 'status' => 0]);

    $response = $this->postJson('/api/checkout', ['amount' => 50.0]);
    $response->assertOk();
}
```

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Ensure all tests pass (`vendor/bin/phpunit`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

---

## Security

If you discover any security-related issues, please email [security@azpays.net](mailto:security@azpays.net) instead of using the public issue tracker. All security vulnerabilities will be promptly addressed.

---

## License

The AzPays Laravel SDK is open-sourced software licensed under the [MIT license](LICENSE).
