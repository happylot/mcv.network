<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountCapabilityController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'capability' => ['required', 'string', Rule::in(['buy', 'sell_inventory', 'sell_services'])],
        ]);

        $account = $request->user()->currentAccount();

        abort_if(! $account || $account->isAdmin(), 403);

        $column = match ($validated['capability']) {
            'buy' => 'can_buy',
            'sell_inventory' => 'can_sell_inventory',
            'sell_services' => 'can_sell_services',
        };

        $account->forceFill([$column => true])->save();

        return back()->with('status', 'Capability enabled. Your dashboard has been updated.');
    }
}
