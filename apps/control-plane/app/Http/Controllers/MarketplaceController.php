<?php

namespace App\Http\Controllers;

use App\Models\AgencyService;
use App\Models\BuyRequest;
use App\Models\GuestPostOrder;
use App\Models\PublisherWebsite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->marketplaceAccount($request);
        $listingType = $request->query('type', 'all');
        $listingType = in_array($listingType, ['all', 'guest_post', 'service', 'buy_request'], true) ? $listingType : 'all';
        $niche = $request->query('niche');
        $category = $request->query('category');

        $websites = PublisherWebsite::query()
            ->with('account')
            ->where('status', 'approved')
            ->when(in_array($listingType, ['service', 'buy_request'], true), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(is_string($niche) && $niche !== '', fn ($query) => $query->where('niche', $niche))
            ->orderBy('guest_post_price_cents')
            ->get();

        $services = AgencyService::query()
            ->with('agencyAccount')
            ->where('status', 'approved')
            ->when(in_array($listingType, ['guest_post', 'buy_request'], true), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(is_string($category) && $category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('base_price_cents')
            ->get();

        $buyRequests = BuyRequest::query()
            ->with('account')
            ->where('status', 'open')
            ->when(in_array($listingType, ['guest_post', 'service'], true), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->get();

        return view('marketplace.websites.index', [
            'account' => $account,
            'wallet' => $account->wallet,
            'websites' => $websites,
            'services' => $services,
            'buyRequests' => $buyRequests,
            'niches' => PublisherWebsite::query()->where('status', 'approved')->distinct()->orderBy('niche')->pluck('niche'),
            'categories' => AgencyService::query()->where('status', 'approved')->distinct()->orderBy('category')->pluck('category'),
            'selectedListingType' => $listingType,
            'selectedNiche' => $niche,
            'selectedCategory' => $category,
        ]);
    }

    public function store(Request $request, PublisherWebsite $website): RedirectResponse
    {
        $account = $this->marketplaceAccount($request);

        abort_if(! $account->canBuy(), 403);
        abort_if(! $website->isApproved(), 404);

        $validated = $request->validate([
            'target_url' => ['required', 'url', 'max:255'],
            'anchor_text' => ['nullable', 'string', 'max:120'],
            'article_title' => ['nullable', 'string', 'max:160'],
            'content_requirements' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($website->account_id === $account->id) {
            return back()->withErrors(['website' => 'You cannot buy a placement on your own website.'])->withInput();
        }

        $order = DB::transaction(function () use ($account, $website, $validated): GuestPostOrder|RedirectResponse {
            $wallet = $account->wallet()->lockForUpdate()->firstOrFail();
            $amountCents = $website->guest_post_price_cents;

            if ($wallet->available_balance_cents < $amountCents) {
                return redirect()
                    ->route('billing.index')
                    ->with('status', 'Please add funds before buying this guest post placement.');
            }

            $order = GuestPostOrder::create([
                'advertiser_account_id' => $account->id,
                'publisher_account_id' => $website->account_id,
                'publisher_website_id' => $website->id,
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'pending_publisher',
                'target_url' => $validated['target_url'],
                'anchor_text' => $validated['anchor_text'] ?? null,
                'article_title' => $validated['article_title'] ?? null,
                'content_requirements' => $validated['content_requirements'] ?? null,
                'due_at' => now()->addDays($website->turnaround_days),
            ]);

            $wallet->ledgerEntries()->create([
                'type' => 'guest_post_order',
                'direction' => 'debit',
                'amount_cents' => $amountCents,
                'currency' => $account->currency,
                'status' => 'posted',
                'reference_type' => 'guest_post_order',
                'reference_id' => (string) $order->id,
                'idempotency_key' => 'guest_post_order:'.$order->id.':debit',
                'metadata' => [
                    'website_id' => $website->id,
                    'domain' => $website->domain,
                    'escrow_status' => 'held_for_fulfillment',
                ],
            ]);

            $wallet->decrement('available_balance_cents', $amountCents);

            return $order;
        });

        if ($order instanceof RedirectResponse) {
            return $order;
        }

        return redirect()
            ->route('marketplace.websites.index')
            ->with('status', 'Guest post order #'.$order->id.' created. The publisher can now review and fulfill it.');
    }

    private function marketplaceAccount(Request $request)
    {
        $account = $request->user()->currentAccount()?->load('wallet');

        abort_if(! $account, 403);

        return $account;
    }
}
