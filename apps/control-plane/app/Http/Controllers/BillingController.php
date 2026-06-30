<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Stripe\StripeTopUpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user()->currentAccount()?->load('wallet');

        abort_if(! $account, 404);

        return view('billing.index', [
            'account' => $account,
            'wallet' => $account->wallet,
            'payments' => $account->payments()->latest()->limit(20)->get(),
            'ledgerEntries' => $account->wallet->ledgerEntries()->latest()->limit(50)->get(),
        ]);
    }

    public function store(Request $request, StripeTopUpService $stripe): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:50000'],
            'method' => ['required', 'in:bank_transfer,stripe_checkout'],
            'reference_note' => ['nullable', 'string', 'max:500'],
        ]);

        $account = $request->user()->currentAccount()?->load('wallet');

        abort_if(! $account, 404);

        $amountCents = (int) round(((float) $validated['amount']) * 100);

        if ($validated['method'] === 'stripe_checkout') {
            $payment = Payment::create([
                'account_id' => $account->id,
                'provider' => 'stripe_checkout',
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'requires_payment',
                'metadata' => [
                    'reference_note' => $validated['reference_note'] ?? null,
                ],
            ]);

            $checkoutUrl = $stripe->createCheckoutSession(
                $payment,
                $account,
                route('billing.stripe.success', absolute: true).'?session_id={CHECKOUT_SESSION_ID}',
                route('billing.stripe.cancel', absolute: true),
            );

            return redirect()->away($checkoutUrl);
        }

        DB::transaction(function () use ($account, $amountCents, $validated): void {
            $payment = Payment::create([
                'account_id' => $account->id,
                'provider' => 'bank_transfer',
                'provider_reference' => 'BANK-'.Str::upper(Str::random(10)),
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'pending',
                'metadata' => [
                    'reference_note' => $validated['reference_note'] ?? null,
                    'instructions' => 'Manual bank transfer pending admin reconciliation.',
                ],
            ]);

            $account->wallet->ledgerEntries()->create([
                'type' => 'topup',
                'direction' => 'credit',
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'pending',
                'reference_type' => 'payment',
                'reference_id' => (string) $payment->id,
                'idempotency_key' => 'payment:'.$payment->id.':pending',
                'metadata' => ['provider' => 'bank_transfer'],
            ]);

            $account->wallet->increment('pending_balance_cents', $amountCents);
        });

        return redirect()
            ->route('billing.index')
            ->with('status', 'Top-up request created. Funds will move to available balance after admin confirmation.');
    }

    public function stripeSuccess(Request $request, StripeTopUpService $stripe): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (is_string($sessionId) && $sessionId !== '') {
            $payment = $stripe->fulfillCheckoutSession($sessionId);

            if ($payment) {
                return redirect()
                    ->route('billing.index')
                    ->with('status', 'Stripe payment received. Wallet balance has been updated.');
            }
        }

        return redirect()
            ->route('billing.index')
            ->with('status', 'Stripe checkout is processing. Your wallet will update after payment confirmation.');
    }

    public function stripeCancel(): RedirectResponse
    {
        return redirect()
            ->route('billing.index')
            ->with('status', 'Stripe checkout was cancelled. No funds were added.');
    }
}
