<?php

namespace App\Http\Controllers;

use App\Models\AgencyServiceOrder;
use App\Services\Marketplace\AgencyServiceOrderFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyClientOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->clientAccount($request);

        return view('services.orders.index', [
            'orders' => $account->agencyServiceOrdersAsClient()
                ->with(['service', 'agencyAccount'])
                ->latest()
                ->get(),
        ]);
    }

    public function approve(
        Request $request,
        AgencyServiceOrder $order,
        AgencyServiceOrderFulfillmentService $fulfillment,
    ): RedirectResponse {
        $account = $this->clientAccount($request);

        abort_if($order->client_account_id !== $account->id, 403);

        $fulfillment->approve($order, $account);

        return redirect()
            ->route('services.orders.index')
            ->with('status', 'Agency delivery approved. Payout has been released.');
    }

    private function clientAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->canBuyAgencyServices(), 403);

        return $account;
    }
}
