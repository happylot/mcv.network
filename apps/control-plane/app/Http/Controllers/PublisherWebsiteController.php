<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublisherWebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $account = $this->publisherAccount($request);

        return view('publisher.websites.index', [
            'account' => $account,
            'wallet' => $account->wallet,
            'websites' => $account->publisherWebsites()->latest()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $account = $this->publisherAccount($request);

        return view('publisher.websites.create', [
            'account' => $account,
            'wallet' => $account->wallet,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $account = $this->publisherAccount($request);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'niche' => ['required', 'string', 'max:80'],
            'language' => ['required', 'string', 'max:16'],
            'country' => ['required', 'string', 'size:2'],
            'monthly_traffic' => ['required', 'integer', 'min:0'],
            'domain_rating' => ['required', 'integer', 'min:0', 'max:100'],
            'domain_authority' => ['required', 'integer', 'min:0', 'max:100'],
            'guest_post_price' => ['required', 'numeric', 'min:5', 'max:100000'],
            'turnaround_days' => ['required', 'integer', 'min:1', 'max:60'],
            'guidelines' => ['nullable', 'string', 'max:5000'],
            'sample_url' => ['nullable', 'url', 'max:255'],
        ]);

        $domain = $this->normalizeDomain($validated['domain']);

        if ($account->publisherWebsites()->where('domain', $domain)->exists()) {
            return back()
                ->withErrors(['domain' => 'This website is already in your publisher inventory.'])
                ->withInput();
        }

        $priceCents = (int) round(((float) $validated['guest_post_price']) * 100);
        $isAutoApproved = $priceCents < 10000;

        $account->publisherWebsites()->create([
            'domain' => $domain,
            'name' => $validated['name'] ?: Str::of($domain)->replace('www.', '')->title()->toString(),
            'niche' => $validated['niche'],
            'language' => Str::lower($validated['language']),
            'country' => Str::upper($validated['country']),
            'monthly_traffic' => (int) $validated['monthly_traffic'],
            'domain_rating' => (int) $validated['domain_rating'],
            'domain_authority' => (int) $validated['domain_authority'],
            'guest_post_price_cents' => $priceCents,
            'turnaround_days' => (int) $validated['turnaround_days'],
            'guidelines' => $validated['guidelines'] ?? null,
            'sample_url' => $validated['sample_url'] ?? null,
            'status' => $isAutoApproved ? 'approved' : 'pending_review',
        ]);

        return redirect()
            ->route('publisher.websites.index')
            ->with('status', $isAutoApproved
                ? 'Website auto-approved and published to the marketplace.'
                : 'Website submitted for review. It will appear in the marketplace after approval.');
    }

    private function publisherAccount(Request $request)
    {
        $account = $request->user()->currentAccount()?->load('wallet');

        abort_if(! $account || ! $account->canSellInventory(), 403);

        return $account;
    }

    private function normalizeDomain(string $value): string
    {
        $host = parse_url(Str::startsWith($value, ['http://', 'https://']) ? $value : 'https://'.$value, PHP_URL_HOST);

        return Str::of($host ?: $value)
            ->lower()
            ->replaceMatches('/^www\./', '')
            ->trim('/')
            ->toString();
    }
}
