<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BuyRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->canBuy(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'budget' => ['required', 'numeric', 'min:5', 'max:100000'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $account->buyRequests()->create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'budget_cents' => (int) round(((float) $validated['budget']) * 100),
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        return redirect()
            ->route('marketplace.websites.index', ['type' => 'buy_request'])
            ->with('status', 'Buy request posted. Sellers can now review your brief.');
    }
}
