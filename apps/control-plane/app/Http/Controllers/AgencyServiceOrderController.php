<?php

namespace App\Http\Controllers;

use App\Models\AgencyServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyServiceOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->agencyAccount($request);

        return view('agency.orders.index', [
            'orders' => $account->agencyServiceOrdersAsAgency()
                ->with(['service', 'clientAccount'])
                ->latest()
                ->get(),
        ]);
    }

    public function submit(Request $request, AgencyServiceOrder $order): RedirectResponse
    {
        $account = $this->agencyAccount($request);

        abort_if($order->agency_account_id !== $account->id, 403);
        abort_if(! in_array($order->status, ['pending_agency', 'submitted'], true), 409);

        $validated = $request->validate([
            'delivery_url' => ['required', 'url', 'max:255'],
            'agency_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $order->update([
            'status' => 'submitted',
            'delivery_url' => $validated['delivery_url'],
            'agency_notes' => $validated['agency_notes'] ?? null,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('agency.orders.index')
            ->with('status', 'Delivery submitted. The client or admin can approve payout.');
    }

    private function agencyAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->isAgency(), 403);

        return $account;
    }
}
