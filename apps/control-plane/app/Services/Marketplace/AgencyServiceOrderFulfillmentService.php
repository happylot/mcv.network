<?php

namespace App\Services\Marketplace;

use App\Models\Account;
use App\Models\AgencyServiceOrder;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgencyServiceOrderFulfillmentService
{
    public function approve(AgencyServiceOrder $order, Account $approver): AgencyServiceOrder
    {
        return DB::transaction(function () use ($order, $approver): AgencyServiceOrder {
            $lockedOrder = AgencyServiceOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->isCompleted()) {
                return $lockedOrder;
            }

            if (! $lockedOrder->isSubmitted()) {
                throw ValidationException::withMessages([
                    'order' => 'Only submitted agency service orders can be approved.',
                ]);
            }

            $agencyWallet = Wallet::query()
                ->where('account_id', $lockedOrder->agency_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ledgerEntry = $agencyWallet->ledgerEntries()->firstOrCreate(
                ['idempotency_key' => 'agency_service_order:'.$lockedOrder->id.':agency_payout'],
                [
                    'type' => 'agency_service_payout',
                    'direction' => 'credit',
                    'amount_cents' => $lockedOrder->amount_cents,
                    'currency' => $lockedOrder->currency,
                    'status' => 'posted',
                    'reference_type' => 'agency_service_order',
                    'reference_id' => (string) $lockedOrder->id,
                    'metadata' => [
                        'approved_by_account_id' => $approver->id,
                        'delivery_url' => $lockedOrder->delivery_url,
                    ],
                ],
            );

            if ($ledgerEntry->wasRecentlyCreated) {
                $agencyWallet->increment('available_balance_cents', $lockedOrder->amount_cents);
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
