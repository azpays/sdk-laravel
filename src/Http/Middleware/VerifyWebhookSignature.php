<?php

declare(strict_types=1);

namespace AzPays\Laravel\Http\Middleware;

use AzPays\Webhooks\Webhook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request and verify the AzPays HMAC signature.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('azpays.webhook.secret', '');
        if ($secret === '') {
            return response()->json([
                'error' => 'AzPays webhook secret is not configured.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $signatureHeader = (string) $request->header('X-AzPays-Signature', '');
        $payload = (string) $request->getContent();
        $tolerance = (int) config('azpays.webhook.tolerance', 300);

        if (!Webhook::verifySignature($payload, $signatureHeader, $secret, $tolerance)) {
            return response()->json([
                'error' => 'Invalid signature or expired timestamp.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
