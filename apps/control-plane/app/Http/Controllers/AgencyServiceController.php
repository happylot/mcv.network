<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyServiceController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->agencyAccount($request);

        return view('agency.services.index', [
            'services' => $account->agencyServices()->latest()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->agencyAccount($request);

        return view('agency.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $account = $this->agencyAccount($request);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:5000'],
            'deliverables' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'numeric', 'min:5', 'max:100000'],
            'turnaround_days' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $priceCents = (int) round(((float) $validated['base_price']) * 100);
        $isAutoApproved = $priceCents < 10000;

        $account->agencyServices()->create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'deliverables' => $validated['deliverables'] ?? null,
            'base_price_cents' => $priceCents,
            'turnaround_days' => (int) $validated['turnaround_days'],
            'status' => $isAutoApproved ? 'approved' : 'pending_review',
        ]);

        return redirect()
            ->route('agency.services.index')
            ->with('status', $isAutoApproved
                ? 'Service auto-approved and published to the marketplace.'
                : 'Service submitted for admin review.');
    }

    private function agencyAccount(Request $request)
    {
        $account = $request->user()->currentAccount();

        abort_if(! $account || ! $account->canSellServices(), 403);

        return $account;
    }
}
