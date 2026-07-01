<?php

namespace App\Http\Controllers;

use App\Models\GuestPostOrder;
use App\Services\Marketplace\GuestPostOrderFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuestPostOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->adminAccount($request);
        $status = $request->query('status', 'submitted');
        $query = GuestPostOrder::query()->with(['website', 'advertiserAccount', 'publisherAccount'])->latest();

        if (is_string($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.orders.index', [
            'account' => $account,
            'status' => $status,
            'orders' => $query->get(),
        ]);
    }

    public function approve(
        Request $request,
        GuestPostOrder $order,
        GuestPostOrderFulfillmentService $fulfillment,
    ): RedirectResponse {
        $account = $this->adminAccount($request);

        $fulfillment->approve($order, $account);

        return redirect()
            ->route('admin.orders.index')
            ->with('status', 'Order #'.$order->id.' approved. Publisher payout has been released.');
    }

    private function adminAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->isAdmin(), 403);

        return $account;
    }
}
