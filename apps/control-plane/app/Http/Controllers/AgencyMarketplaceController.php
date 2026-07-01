<?php

namespace App\Http\Controllers;

use App\Models\AgencyService;
use App\Models\AgencyServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AgencyMarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->clientAccount($request);
        $category = $request->query('category');

        $services = AgencyService::query()
            ->with('agencyAccount')
            ->where('status', 'approved')
            ->when(is_string($category) && $category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('base_price_cents')
            ->get();

        return view('services.index', [
            'account' => $account,
            'services' => $services,
            'categories' => AgencyService::query()->where('status', 'approved')->distinct()->orderBy('category')->pluck('category'),
            'selectedCategory' => $category,
        ]);
    }

    public function store(Request $request, AgencyService $service): RedirectResponse
    {
        $account = $this->clientAccount($request);

        abort_if(! $service->isApproved(), 404);

        $validated = $request->validate([
            'brief' => ['required', 'string', 'max:5000'],
            'reference_url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($service->agency_account_id === $account->id) {
            return back()->withErrors(['service' => 'You cannot buy your own agency service.'])->withInput();
        }

        $order = DB::transaction(function () use ($account, $service, $validated): AgencyServiceOrder|RedirectResponse {
            $wallet = $account->wallet()->lockForUpdate()->firstOrFail();
            $amountCents = $service->base_price_cents;

            if ($wallet->available_balance_cents < $amountCents) {
                return redirect()
                    ->route('billing.index')
                    ->with('status', 'Please add funds before ordering this agency service.');
            }

            $order = AgencyServiceOrder::create([
                'client_account_id' => $account->id,
                'agency_account_id' => $service->agency_account_id,
                'agency_service_id' => $service->id,
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'pending_agency',
                'brief' => $validated['brief'],
                'reference_url' => $validated['reference_url'] ?? null,
                'due_at' => now()->addDays($service->turnaround_days),
            ]);

            $wallet->ledgerEntries()->create([
                'type' => 'agency_service_order',
                'direction' => 'debit',
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'posted',
                'reference_type' => 'agency_service_order',
                'reference_id' => (string) $order->id,
                'idempotency_key' => 'agency_service_order:'.$order->id.':debit',
                'metadata' => [
                    'service_id' => $service->id,
                    'agency_account_id' => $service->agency_account_id,
                    'escrow_status' => 'held_for_delivery',
                ],
            ]);

            $wallet->decrement('available_balance_cents', $amountCents);

            return $order;
        });

        if ($order instanceof RedirectResponse) {
            return $order;
        }

        return redirect()
            ->route('services.orders.index')
            ->with('status', 'Agency service order #'.$order->id.' created.');
    }

    private function clientAccount(Request $request)
    {
        $account = $request->user()->currentAccount()?->load('wallet');

        abort_if(! $account || ! $account->canBuyAgencyServices(), 403);

        return $account;
    }
}
