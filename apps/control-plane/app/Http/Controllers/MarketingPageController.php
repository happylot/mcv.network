<?php

namespace App\Http\Controllers;

use App\Models\AgencyService;
use App\Models\BuyRequest;
use App\Models\PublisherWebsite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarketingPageController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): View
    {
        $path = trim($path ?? '', '/');

        if ($path === '') {
            return view('marketing.index', [
                ...$this->homepageMarketplacePreview($request),
            ]);
        }

        if (str_contains($path, '..')) {
            abort(404);
        }

        $view = 'marketing.'.str_replace('/', '.', $path).'.index';

        abort_unless(view()->exists($view), 404);

        return view($view);
    }

    private function homepageMarketplacePreview(Request $request): array
    {
        $selectedGroup = $request->query('listing_group', 'all');
        $selectedGroup = in_array($selectedGroup, ['all', 'guest_post', 'service', 'buy_request'], true) ? $selectedGroup : 'all';

        $websiteListings = PublisherWebsite::query()
            ->with('account')
            ->where('status', 'approved')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (PublisherWebsite $website) => [
                'type' => 'guest_post',
                'label' => 'Guest post offer',
                'icon' => 'fa-solid fa-globe',
                'title' => $website->domain,
                'seller' => $website->account?->name ?? 'Verified publisher',
                'meta' => $website->niche.' · '.strtoupper($website->language).' · DR '.$website->domain_rating,
                'price' => $website->formattedPrice(),
                'description' => $website->guidelines ?: number_format($website->monthly_traffic).' visits/mo · '.$website->turnaround_days.' days turnaround',
                'created_at' => $website->created_at,
                'is_sample' => false,
            ]);

        $serviceListings = AgencyService::query()
            ->with('agencyAccount')
            ->where('status', 'approved')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (AgencyService $service) => [
                'type' => 'service',
                'label' => 'Service offer',
                'icon' => 'fa-solid fa-briefcase',
                'title' => $service->title,
                'seller' => $service->agencyAccount?->name ?? 'Verified agency',
                'meta' => $service->category.' · '.$service->turnaround_days.' days',
                'price' => $service->formattedPrice(),
                'description' => $service->description,
                'created_at' => $service->created_at,
                'is_sample' => false,
            ]);

        $buyRequestListings = BuyRequest::query()
            ->with('account')
            ->where('status', 'open')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (BuyRequest $buyRequest) => [
                'type' => 'buy_request',
                'label' => 'Buy request',
                'icon' => 'fa-solid fa-bullhorn',
                'title' => $buyRequest->title,
                'seller' => $buyRequest->account?->name ?? 'Verified buyer',
                'meta' => $buyRequest->category.' · open brief',
                'price' => $buyRequest->formattedBudget(),
                'description' => $buyRequest->description,
                'created_at' => $buyRequest->created_at,
                'is_sample' => false,
            ]);

        $allListings = collect()
            ->concat($websiteListings)
            ->concat($serviceListings)
            ->concat($buyRequestListings)
            ->sortByDesc('created_at')
            ->values();

        if ($allListings->isEmpty()) {
            $allListings = $this->sampleMarketplaceListings();
        }

        $filteredListings = $selectedGroup === 'all'
            ? $allListings
            : $allListings->where('type', $selectedGroup)->values();

        return [
            'publicMarketplaceListings' => $filteredListings->take(6),
            'publicMarketplaceCounts' => [
                'all' => $allListings->count(),
                'guest_post' => $allListings->where('type', 'guest_post')->count(),
                'service' => $allListings->where('type', 'service')->count(),
                'buy_request' => $allListings->where('type', 'buy_request')->count(),
            ],
            'selectedPublicListingGroup' => $selectedGroup,
        ];
    }

    private function sampleMarketplaceListings(): Collection
    {
        return collect([
            [
                'type' => 'guest_post',
                'label' => 'Guest post offer',
                'icon' => 'fa-solid fa-globe',
                'title' => 'finance-insights.example',
                'seller' => 'Sample publisher',
                'meta' => 'Finance · EN · DR 62',
                'price' => '$149.00',
                'description' => 'Contextual guest post placement with 5-day turnaround and editorial review.',
                'created_at' => now()->subMinutes(4),
                'is_sample' => true,
            ],
            [
                'type' => 'service',
                'label' => 'Service offer',
                'icon' => 'fa-solid fa-briefcase',
                'title' => 'SEO article package',
                'seller' => 'Sample agency',
                'meta' => 'SEO Writing · 4 days',
                'price' => '$120.00',
                'description' => 'Keyword research, outline, 1200-word article, and meta title for campaigns.',
                'created_at' => now()->subMinutes(8),
                'is_sample' => true,
            ],
            [
                'type' => 'buy_request',
                'label' => 'Buy request',
                'icon' => 'fa-solid fa-bullhorn',
                'title' => 'Need SaaS backlinks this week',
                'seller' => 'Sample buyer',
                'meta' => 'Guest Post · open brief',
                'price' => '$500.00',
                'description' => 'Looking for DR 50+ SaaS and startup websites with English traffic.',
                'created_at' => now()->subMinutes(12),
                'is_sample' => true,
            ],
        ]);
    }
}
