<?php

namespace App\Http\Controllers;

use App\Models\AgencyServiceOrder;
use App\Services\Marketplace\AgencyServiceOrderFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAgencyServiceOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->adminAccount($request);
        $status = $request->query('status', 'submitted');
        $query = AgencyServiceOrder::query()->with(['service', 'clientAccount', 'agencyAccount'])->latest();

        if (is_string($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.agency-orders.index', [
            'account' => $account,
            'status' => $status,
            'orders' => $query->get(),
        ]);
    }

    public function approve(
        Request $request,
        AgencyServiceOrder $order,
        AgencyServiceOrderFulfillmentService $fulfillment,
    ): RedirectResponse {
        $account = $this->adminAccount($request);

        $fulfillment->approve($order, $account);

        return redirect()
            ->route('admin.agency-orders.index')
            ->with('status', 'Agency order #'.$order->id.' approved. Payout has been released.');
    }

    private function adminAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->isAdmin(), 403);

        return $account;
    }
}
