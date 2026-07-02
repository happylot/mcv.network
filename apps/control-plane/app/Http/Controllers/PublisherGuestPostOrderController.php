<?php

namespace App\Http\Controllers;

use App\Models\GuestPostOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherGuestPostOrderController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->publisherAccount($request);

        return view('publisher.orders.index', [
            'orders' => $account->guestPostOrdersAsPublisher()
                ->with(['website', 'advertiserAccount'])
                ->latest()
                ->get(),
        ]);
    }

    public function submit(Request $request, GuestPostOrder $order): RedirectResponse
    {
        $account = $this->publisherAccount($request);

        abort_if($order->publisher_account_id !== $account->id, 403);
        abort_if(! in_array($order->status, ['pending_publisher', 'submitted'], true), 409);

        $validated = $request->validate([
            'published_url' => ['required', 'url', 'max:255'],
            'publisher_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $order->update([
            'status' => 'submitted',
            'published_url' => $validated['published_url'],
            'publisher_notes' => $validated['publisher_notes'] ?? null,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('publisher.orders.index')
            ->with('status', 'Published URL submitted. The advertiser can now approve fulfillment.');
    }

    private function publisherAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->canSellInventory(), 403);

        return $account;
    }
}
