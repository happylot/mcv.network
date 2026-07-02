<?php

namespace App\Http\Controllers;

use App\Models\GuestPostOrder;
use App\Services\Marketplace\GuestPostOrderFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertiserGuestPostOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->advertiserAccount($request);

        return view('marketplace.orders.index', [
            'orders' => $account->guestPostOrdersAsAdvertiser()
                ->with(['website', 'publisherAccount'])
                ->latest()
                ->get(),
        ]);
    }

    public function approve(
        Request $request,
        GuestPostOrder $order,
        GuestPostOrderFulfillmentService $fulfillment,
    ): RedirectResponse {
        $account = $this->advertiserAccount($request);

        abort_if($order->advertiser_account_id !== $account->id, 403);

        $fulfillment->approve($order, $account);

        return redirect()
            ->route('marketplace.orders.index')
            ->with('status', 'Fulfillment approved. Publisher payout has been released.');
    }

    private function advertiserAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->canBuy(), 403);

        return $account;
    }
}
