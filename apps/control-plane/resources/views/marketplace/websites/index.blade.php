@extends('layouts.app', ['title' => 'Guest Post Marketplace | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Guest Post Marketplace <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Buy Guest Posts</h2>
                <p class="muted">Browse approved publisher websites and place orders using your wallet balance.</p>
            </div>
            <form method="GET" action="{{ route('marketplace.websites.index') }}">
                <select name="niche" onchange="this.form.submit()">
                    <option value="">All niches</option>
                    @foreach ($niches as $niche)
                        <option value="{{ $niche }}" @selected($selectedNiche === $niche)>{{ $niche }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($websites->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-store"></i>
                <h3>No approved websites yet</h3>
                <p>Approved publisher inventory will appear here once admin review is complete.</p>
            </div>
        @else
            <div class="marketplace-list">
                @foreach ($websites as $website)
                    <article class="marketplace-item">
                        <div>
                            <span class="website-name"><span class="domain-dot blue">●</span>{{ $website->domain }}</span>
                            <p class="muted">{{ $website->niche }} · {{ strtoupper($website->language) }} · {{ $website->country }}</p>
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

                        <form class="order-form" method="POST" action="{{ route('marketplace.orders.store', $website) }}">
                            @csrf
                            <strong class="marketplace-price">{{ $website->formattedPrice() }}</strong>
                            <div class="field">
                                <label for="target_url_{{ $website->id }}">Target URL</label>
                                <input id="target_url_{{ $website->id }}" type="url" name="target_url" placeholder="https://your-site.com/page" required>
                            </div>
                            <div class="field">
                                <label for="anchor_text_{{ $website->id }}">Anchor text</label>
                                <input id="anchor_text_{{ $website->id }}" name="anchor_text" placeholder="Optional">
                            </div>
                            <div class="field">
                                <label for="article_title_{{ $website->id }}">Article title</label>
                                <input id="article_title_{{ $website->id }}" name="article_title" placeholder="Optional">
                            </div>
                            <div class="field">
                                <label for="content_requirements_{{ $website->id }}">Requirements</label>
                                <textarea id="content_requirements_{{ $website->id }}" name="content_requirements" placeholder="Topic, tone, link rules, notes for publisher"></textarea>
                            </div>
                            <button class="button" type="submit">Buy Guest Post</button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
