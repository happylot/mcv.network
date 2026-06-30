<?php

namespace App\Services\Stripe;

use App\Models\Account;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeTopUpService
{
    public function createCheckoutSession(Payment $payment, Account $account, string $successUrl, string $cancelUrl): string
    {
        $client = $this->client();

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'client_reference_id' => (string) $payment->id,
            'customer_email' => $account->owner?->email,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($payment->currency),
                    'unit_amount' => $payment->amount_cents,
                    'product_data' => [
                        'name' => 'MCV Ads wallet top-up',
                        'description' => 'Wallet credit for '.$account->name,
                    ],
                ],
            ]],
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'account_id' => (string) $account->id,
                'type' => 'wallet_topup',
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'payment_id' => (string) $payment->id,
                    'account_id' => (string) $account->id,
                    'type' => 'wallet_topup',
                ],
            ],
        ]);

        $payment->forceFill([
            'provider_reference' => $session->id,
            'metadata' => array_merge($payment->metadata ?? [], [
                'stripe_checkout_session_id' => $session->id,
            ]),
        ])->save();

        return $session->url;
    }

    /**
     * @param  array<string, mixed>|Session|null  $sessionPayload
     */
    public function fulfillCheckoutSession(string $sessionId, array|Session|null $sessionPayload = null): ?Payment
    {
        $session = $sessionPayload ?? $this->client()->checkout->sessions->retrieve($sessionId, []);

        $paymentStatus = $this->sessionValue($session, 'payment_status');
        if ($paymentStatus !== 'paid') {
            return null;
        }

        return DB::transaction(function () use ($sessionId, $session): ?Payment {
            $paymentId = $this->sessionMetadataValue($session, 'payment_id');

            $payment = Payment::query()
                ->where('provider', 'stripe_checkout')
                ->when($paymentId, fn ($query) => $query->where('id', $paymentId), fn ($query) => $query->where('provider_reference', $sessionId))
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return null;
            }

            $account = $payment->account()->with('wallet')->lockForUpdate()->first();
            if (! $account?->wallet) {
                return null;
            }

            if ($payment->status === 'succeeded') {
                return $payment;
            }

            $paymentIntent = $this->sessionValue($session, 'payment_intent');

            $payment->forceFill([
                'status' => 'succeeded',
                'confirmed_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'stripe_checkout_session_id' => $sessionId,
                    'stripe_payment_intent' => is_string($paymentIntent) ? $paymentIntent : null,
                    'stripe_payment_status' => 'paid',
                ]),
            ])->save();

            $account->wallet->ledgerEntries()->firstOrCreate(
                ['idempotency_key' => 'stripe_checkout:'.$sessionId.':posted'],
                [
                    'type' => 'topup',
                    'direction' => 'credit',
                    'amount_cents' => $payment->amount_cents,
                    'currency' => $payment->currency,
                    'status' => 'posted',
                    'reference_type' => 'payment',
                    'reference_id' => (string) $payment->id,
                    'metadata' => [
                        'provider' => 'stripe_checkout',
                        'stripe_checkout_session_id' => $sessionId,
                    ],
                ],
            );

            $account->wallet->increment('available_balance_cents', $payment->amount_cents);

            return $payment->refresh();
        });
    }

    private function client(): StripeClient
    {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        return new StripeClient([
            'api_key' => $secret,
            'stripe_version' => '2026-02-25.clover',
        ]);
    }

    /**
     * @param  array<string, mixed>|Session  $session
     */
    private function sessionValue(array|Session $session, string $key): mixed
    {
        if ($session instanceof Session) {
            return $session->{$key} ?? null;
        }

        return $session[$key] ?? null;
    }

    /**
     * @param  array<string, mixed>|Session  $session
     */
    private function sessionMetadataValue(array|Session $session, string $key): mixed
    {
        $metadata = $this->sessionValue($session, 'metadata');

        if (is_array($metadata)) {
            return $metadata[$key] ?? null;
        }

        return $metadata?->{$key} ?? null;
    }
}
