<?php

namespace App\Services\Marketplace;

use App\Models\Account;
use App\Models\GuestPostOrder;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuestPostOrderFulfillmentService
{
    public function approve(GuestPostOrder $order, Account $approver): GuestPostOrder
    {
        return DB::transaction(function () use ($order, $approver): GuestPostOrder {
            $lockedOrder = GuestPostOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->isCompleted()) {
                return $lockedOrder;
            }

            if (! $lockedOrder->isSubmitted()) {
                throw ValidationException::withMessages([
                    'order' => 'Only submitted guest post orders can be approved.',
                ]);
            }

            $publisherWallet = Wallet::query()
                ->where('account_id', $lockedOrder->publisher_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ledgerEntry = $publisherWallet->ledgerEntries()->firstOrCreate(
                ['idempotency_key' => 'guest_post_order:'.$lockedOrder->id.':publisher_payout'],
                [
                    'type' => 'guest_post_payout',
                    'direction' => 'credit',
                    'amount_cents' => $lockedOrder->amount_cents,
                    'currency' => $lockedOrder->currency,
                    'status' => 'posted',
                    'reference_type' => 'guest_post_order',
                    'reference_id' => (string) $lockedOrder->id,
                    'metadata' => [
                        'approved_by_account_id' => $approver->id,
                        'published_url' => $lockedOrder->published_url,
                    ],
                ],
            );

            if ($ledgerEntry->wasRecentlyCreated) {
                $publisherWallet->increment('available_balance_cents', $lockedOrder->amount_cents);
            }

            $lockedOrder->update([
                'status' => 'completed',
                'approved_at' => now(),
                'approved_by_account_id' => $approver->id,
            ]);

            return $lockedOrder->refresh();
        });
    }
}
