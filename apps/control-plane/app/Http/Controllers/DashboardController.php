<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $account = $request->user()
            ->currentAccount()
            ?->load(['wallet.ledgerEntries' => fn ($query) => $query->latest()->limit(5)]);

        abort_if(! $account, 404);

        return view('dashboard.show', [
            'account' => $account,
            'wallet' => $account->wallet,
            'recentEntries' => $account->wallet?->ledgerEntries ?? collect(),
        ]);
    }
}
