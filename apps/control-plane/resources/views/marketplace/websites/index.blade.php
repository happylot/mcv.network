@extends('layouts.app', ['title' => 'Marketplace Listings | MCV Ads'])

@php
    $listingCount = $websites->count() + $services->count();
    $buyRequestCount = $buyRequests->count();
@endphp

@section('content')
    <div class="dashboard-crumb">Marketplace <span aria-hidden="true">»</span></div>

    <section class="marketplace-hero-card">
        <div>
            <span class="capability-status enabled"><i class="fa-solid fa-store"></i> Shared Marketplace</span>
            <h1>Listings for buyers and sellers</h1>
            <p class="muted">Every account can browse sell offers and buy requests. Available actions depend on your enabled capabilities.</p>
        </div>
        <div class="marketplace-hero-actions">
            @if ($account->canSellInventory())
                <a class="button secondary" href="{{ route('publisher.websites.create') }}"><i class="fa-solid fa-plus"></i> Sell Inventory</a>
            @else
                <form method="POST" action="{{ route('account.capabilities.store') }}">
                    @csrf
                    <input type="hidden" name="capability" value="sell_inventory">
                    <button class="button secondary" type="submit"><i class="fa-solid fa-toggle-on"></i> Enable Inventory</button>
                </form>
            @endif

            @if ($account->canSellServices())
                <a class="button" href="{{ route('agency.services.create') }}"><i class="fa-solid fa-plus"></i> Sell Service</a>
            @else
                <form method="POST" action="{{ route('account.capabilities.store') }}">
                    @csrf
                    <input type="hidden" name="capability" value="sell_services">
                    <button class="button" type="submit"><i class="fa-solid fa-toggle-on"></i> Enable Services</button>
                </form>
            @endif
        </div>
    </section>

    <section class="dash-card marketplace-filter-card">
        <form class="marketplace-filters" method="GET" action="{{ route('marketplace.websites.index') }}">
            <div class="field">
                <label for="type">Listing type</label>
                <select id="type" name="type" onchange="this.form.submit()">
                    <option value="all" @selected($selectedListingType === 'all')>All listings</option>
                    <option value="guest_post" @selected($selectedListingType === 'guest_post')>Guest post offers</option>
                    <option value="service" @selected($selectedListingType === 'service')>Agency service offers</option>
                    <option value="buy_request" @selected($selectedListingType === 'buy_request')>Buy requests</option>
                </select>
            </div>
            <div class="field">
                <label for="niche">Website niche</label>
                <select id="niche" name="niche" onchange="this.form.submit()">
                    <option value="">All niches</option>
                    @foreach ($niches as $niche)
                        <option value="{{ $niche }}" @selected($selectedNiche === $niche)>{{ $niche }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="category">Service category</label>
                <select id="category" name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <a class="button link" href="{{ route('marketplace.websites.index') }}">Reset</a>
        </form>
    </section>

    <section class="marketplace-board" aria-label="Marketplace listings">
        <div class="marketplace-main-column">
            <div class="dash-panel-header marketplace-board-head">
                <div>
                    <h2>Sell offers</h2>
                    <p class="muted">{{ number_format($listingCount) }} approved listings from publishers and agencies.</p>
                </div>
                <span class="badge active">Visible to all roles</span>
            </div>

            <div class="marketplace-list unified-list">
                @forelse ($websites as $website)
                    <article class="marketplace-item unified-listing-card">
                        <div class="listing-main">
                            <div class="listing-title-row">
                                <span class="listing-kind publisher"><i class="fa-solid fa-globe"></i> Guest post offer</span>
                                @if ($website->account_id === $account->id)
                                    <span class="badge active">Your listing</span>
                                @endif
                            </div>
                            <span class="website-name"><span class="domain-dot blue">●</span>{{ $website->domain }}</span>
                            <p class="muted">Seller: {{ $website->account->name }} · {{ $website->niche }} · {{ strtoupper($website->language) }} · {{ $website->country }}</p>
                            <div class="metric-row">
                                <span>DR <strong>{{ $website->domain_rating }}</strong></span>
                                <span>DA <strong>{{ $website->domain_authority }}</strong></span>
                                <span>{{ number_format($website->monthly_traffic) }} visits/mo</span>
                                <span>{{ $website->turnaround_days }} days</span>
                            </div>
                            @if ($website->guidelines)
                                <p class="muted">{{ str($website->guidelines)->limit(150) }}</p>
                            @endif
                        </div>

                        <aside class="listing-action-panel">
                            <strong class="marketplace-price">{{ $website->formattedPrice() }}</strong>
                            @if ($website->account_id === $account->id)
                                <a class="button secondary" href="{{ route('publisher.websites.index') }}">Manage Listing</a>
                            @elseif (! $account->canBuy())
                                <span class="capability-status locked"><i class="fa-solid fa-lock"></i> Buying disabled</span>
                            @else
                                <form class="order-form compact-order-form" method="POST" action="{{ route('marketplace.orders.store', $website) }}">
                                    @csrf
                                    <div class="field">
                                        <label for="target_url_{{ $website->id }}">Target URL</label>
                                        <input id="target_url_{{ $website->id }}" type="url" name="target_url" placeholder="https://your-site.com/page" required>
                                    </div>
                                    <div class="field">
                                        <label for="anchor_text_{{ $website->id }}">Anchor text</label>
                                        <input id="anchor_text_{{ $website->id }}" name="anchor_text" placeholder="Optional">
                                    </div>
                                    <input type="hidden" name="article_title" value="">
                                    <input type="hidden" name="content_requirements" value="">
                                    <button class="button" type="submit">Buy Guest Post</button>
                                </form>
                            @endif
                        </aside>
                    </article>
                @empty
                    @if ($selectedListingType === 'guest_post')
                        <div class="empty-state">
                            <i class="fa-solid fa-globe"></i>
                            <h3>No guest post offers yet</h3>
                            <p>Approved publisher inventory will appear here for every role to browse.</p>
                        </div>
                    @endif
                @endforelse

                @forelse ($services as $service)
                    <article class="marketplace-item unified-listing-card">
                        <div class="listing-main">
                            <div class="listing-title-row">
                                <span class="listing-kind agency"><i class="fa-solid fa-briefcase"></i> Service offer</span>
                                @if ($service->agency_account_id === $account->id)
                                    <span class="badge active">Your listing</span>
                                @endif
                            </div>
                            <span class="website-name"><span class="domain-dot blue">●</span>{{ $service->title }}</span>
                            <p class="muted">Seller: {{ $service->agencyAccount->name }} · {{ $service->category }}</p>
                            <div class="metric-row">
                                <span>Price <strong>{{ $service->formattedPrice() }}</strong></span>
                                <span>{{ $service->turnaround_days }} days</span>
                            </div>
                            <p>{{ $service->description }}</p>
                            @if ($service->deliverables)
                                <p class="muted">{{ str($service->deliverables)->limit(150) }}</p>
                            @endif
                        </div>

                        <aside class="listing-action-panel">
                            <strong class="marketplace-price">{{ $service->formattedPrice() }}</strong>
                            @if ($service->agency_account_id === $account->id)
                                <a class="button secondary" href="{{ route('agency.services.index') }}">Manage Listing</a>
                            @elseif (! $account->canBuy())
                                <span class="capability-status locked"><i class="fa-solid fa-lock"></i> Buying disabled</span>
                            @else
                                <form class="order-form compact-order-form" method="POST" action="{{ route('services.orders.store', $service) }}">
                                    @csrf
                                    <div class="field">
                                        <label for="brief_{{ $service->id }}">Brief</label>
                                        <textarea id="brief_{{ $service->id }}" name="brief" placeholder="Describe what you need" required></textarea>
                                    </div>
                                    <input type="hidden" name="reference_url" value="">
                                    <button class="button" type="submit">Order Service</button>
                                </form>
                            @endif
                        </aside>
                    </article>
                @empty
                    @if ($selectedListingType === 'service')
                        <div class="empty-state">
                            <i class="fa-solid fa-briefcase"></i>
                            <h3>No service offers yet</h3>
                            <p>Approved agency services will appear here for every role to browse.</p>
                        </div>
                    @endif
                @endforelse

                @if ($listingCount === 0 && $selectedListingType === 'all')
                    <div class="empty-state">
                        <i class="fa-solid fa-store"></i>
                        <h3>No approved listings yet</h3>
                        <p>Publisher inventory and agency services will appear here after admin review.</p>
                    </div>
                @endif
            </div>
        </div>

        <aside class="buy-request-column">
            <div class="dash-panel-header">
                <div>
                    <h2>Buy requests</h2>
                    <p class="muted">{{ number_format($buyRequestCount) }} open briefs visible to sellers.</p>
                </div>
            </div>
            @if ($account->canBuy())
                <form class="buy-request-card buy-request-form" method="POST" action="{{ route('marketplace.buy-requests.store') }}">
                    @csrf
                    <span class="listing-kind buyer"><i class="fa-solid fa-bullhorn"></i> Post demand</span>
                    <h3>Create buy request</h3>
                    <div class="field">
                        <label for="buy_request_title">Title</label>
                        <input id="buy_request_title" name="title" placeholder="Need 5 fintech guest posts" required>
                    </div>
                    <div class="field">
                        <label for="buy_request_category">Category</label>
                        <input id="buy_request_category" name="category" placeholder="Guest Post, SEO, Video..." required>
                    </div>
                    <div class="field">
                        <label for="buy_request_budget">Budget</label>
                        <input id="buy_request_budget" name="budget" type="number" min="5" step="0.01" placeholder="500" required>
                    </div>
                    <div class="field">
                        <label for="buy_request_description">Brief</label>
                        <textarea id="buy_request_description" name="description" placeholder="Describe what you want sellers to offer" required></textarea>
                    </div>
                    <button class="button" type="submit">Post Buy Request</button>
                </form>
            @else
                <div class="buy-request-card">
                    <span class="listing-kind buyer"><i class="fa-solid fa-lock"></i> Buying disabled</span>
                    <h3>Enable buying to post briefs</h3>
                    <p class="muted">Buy requests are demand-side listings. Enable buyer capability before posting what you want to purchase.</p>
                </div>
            @endif

            <div class="buy-request-list">
                @forelse ($buyRequests as $buyRequest)
                    <article class="buy-request-card">
                        <span class="listing-kind buyer"><i class="fa-solid fa-bullhorn"></i> Buy request</span>
                        <h3>{{ $buyRequest->title }}</h3>
                        <p class="muted">Buyer: {{ $buyRequest->account->name }} · {{ $buyRequest->category }}</p>
                        <div class="metric-row">
                            <span>Budget <strong>{{ $buyRequest->formattedBudget() }}</strong></span>
                            <span>{{ $buyRequest->created_at->diffForHumans() }}</span>
                        </div>
                        <p>{{ str($buyRequest->description)->limit(180) }}</p>
                        @if ($buyRequest->account_id === $account->id)
                            <span class="badge active">Your request</span>
                        @elseif ($account->canSellInventory() || $account->canSellServices())
                            <a class="button secondary" href="#">Respond to Brief</a>
                        @else
                            <span class="capability-status locked"><i class="fa-solid fa-lock"></i> Seller capability needed</span>
                        @endif
                    </article>
                @empty
                    <div class="buy-request-card">
                        <span class="listing-kind buyer"><i class="fa-solid fa-circle-info"></i> Empty</span>
                        <h3>No buy requests yet</h3>
                        <p class="muted">When buyers post demand, publishers and agencies will see those briefs here.</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>
@endsection
