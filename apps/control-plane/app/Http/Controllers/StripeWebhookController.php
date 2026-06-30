<?php

namespace App\Http\Controllers;

use App\Services\Stripe\StripeTopUpService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeTopUpService $stripe): Response
    {
        $payload = $request->getContent();
        $webhookSecret = config('services.stripe.webhook_secret');

        if ((! is_string($webhookSecret) || $webhookSecret === '') && app()->isProduction()) {
            return response('Stripe webhook secret is not configured.', 500);
        }

        try {
            if (is_string($webhookSecret) && $webhookSecret !== '') {
                $event = Webhook::constructEvent(
                    $payload,
                    (string) $request->header('Stripe-Signature'),
                    $webhookSecret,
                );
            } else {
                $event = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
            }
        } catch (SignatureVerificationException|UnexpectedValueException|\JsonException) {
            return response('Invalid Stripe webhook payload.', 400);
        }

        $eventType = is_array($event) ? ($event['type'] ?? null) : ($event->type ?? null);
        $session = is_array($event)
            ? ($event['data']['object'] ?? null)
            : ($event->data->object ?? null);

        if (in_array($eventType, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true) && $session) {
            $sessionId = is_array($session) ? ($session['id'] ?? null) : ($session->id ?? null);

            if (is_string($sessionId) && $sessionId !== '') {
                $stripe->fulfillCheckoutSession($sessionId, is_array($session) ? $session : null);
            }
        }

        return response('ok');
    }
}
