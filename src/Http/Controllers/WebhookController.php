<?php

declare(strict_types=1);

namespace AzPays\Laravel\Http\Controllers;

use AzPays\Laravel\Events\AzpaysWebhookReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    /**
     * Handle incoming verified AzPays webhook and dispatch event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = (string) ($payload['event'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        event(new AzpaysWebhookReceived($event, $data, $payload));

        return response()->json([
            'status' => 'success',
        ]);
    }
}
