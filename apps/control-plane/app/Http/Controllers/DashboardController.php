<?php

namespace App\Http\Controllers;

use App\Models\AgencyService;
use App\Models\AgencyServiceOrder;
use App\Models\GuestPostOrder;
use App\Models\PublisherWebsite;
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

        if ($account->isAdmin()) {
            return view('dashboard.admin', [
                'pendingWebsiteCount' => PublisherWebsite::query()->where('status', 'pending_review')->count(),
                'approvedWebsiteCount' => PublisherWebsite::query()->where('status', 'approved')->count(),
                'openOrderCount' => GuestPostOrder::query()->whereIn('status', ['pending_publisher', 'submitted'])->count(),
                'pendingServiceCount' => AgencyService::query()->where('status', 'pending_review')->count(),
                'openServiceOrderCount' => AgencyServiceOrder::query()->whereIn('status', ['pending_agency', 'submitted'])->count(),
                'recentWebsites' => PublisherWebsite::query()->with('account')->latest()->limit(8)->get(),
            ]);
        }

        if ($account->isAgency()) {
            $services = $account->agencyServices()->latest()->get();

            return view('dashboard.agency', [
                'account' => $account,
                'wallet' => $account->wallet,
                'services' => $services,
                'openOrderCount' => $account->agencyServiceOrdersAsAgency()->whereIn('status', ['pending_agency', 'submitted'])->count(),
            ]);
        }

        if ($account->isPublisher()) {
            $websites = $account->publisherWebsites()->latest()->get();

            return view('dashboard.publisher', [
                'account' => $account,
                'wallet' => $account->wallet,
                'recentEntries' => $account->wallet?->ledgerEntries ?? collect(),
                'websites' => $websites,
                'openOrderCount' => $account->guestPostOrdersAsPublisher()->whereIn('status', ['pending_publisher', 'submitted'])->count(),
            ]);
        }

        return view('dashboard.show', [
            'account' => $account,
            'wallet' => $account->wallet,
            'recentEntries' => $account->wallet?->ledgerEntries ?? collect(),
        ]);
    }
}
