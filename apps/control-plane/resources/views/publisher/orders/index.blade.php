@extends('layouts.app', ['title' => 'Publisher Orders | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Publisher Orders <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Guest Post Orders</h2>
                <p class="muted">Submit the live article URL after publishing. Payout is released after advertiser or admin approval.</p>
            </div>
        </div>

        <div class="marketplace-list">
            @forelse ($orders as $order)
                <article class="marketplace-item">
                    <div>
                        <span class="website-name"><span class="domain-dot blue">●</span>{{ $order->website->domain }}</span>
                        <p class="muted">Advertiser: {{ $order->advertiserAccount->name }} · Due {{ $order->due_at?->format('M j, Y') ?? 'not set' }}</p>
                        <div class="metric-row">
                            <span>Amount <strong>{{ $order->formattedAmount() }}</strong></span>
                            <span>Status <strong>{{ str_replace('_', ' ', $order->status) }}</strong></span>
                            @if ($order->anchor_text)
                                <span>Anchor <strong>{{ $order->anchor_text }}</strong></span>
                            @endif
                        </div>
                        <p><strong>Target URL:</strong> <a href="{{ $order->target_url }}" target="_blank" rel="noopener">{{ $order->target_url }}</a></p>
                        @if ($order->article_title)
                            <p><strong>Article title:</strong> {{ $order->article_title }}</p>
                        @endif
                        @if ($order->content_requirements)
                            <p class="muted">{{ $order->content_requirements }}</p>
                        @endif
                        @if ($order->published_url)
                            <p><strong>Submitted URL:</strong> <a href="{{ $order->published_url }}" target="_blank" rel="noopener">{{ $order->published_url }}</a></p>
                        @endif
                    </div>

                    @if (in_array($order->status, ['pending_publisher', 'submitted'], true))
                        <form class="order-form" method="POST" action="{{ route('publisher.orders.submit', $order) }}">
                            @csrf
                            <div class="field">
                                <label for="published_url_{{ $order->id }}">Published URL</label>
                                <input id="published_url_{{ $order->id }}" type="url" name="published_url" value="{{ old('published_url', $order->published_url) }}" placeholder="https://publisher.com/live-article" required>
                            </div>
                            <div class="field">
                                <label for="publisher_notes_{{ $order->id }}">Publisher notes</label>
                                <textarea id="publisher_notes_{{ $order->id }}" name="publisher_notes" placeholder="Optional notes for advertiser">{{ old('publisher_notes', $order->publisher_notes) }}</textarea>
                            </div>
                            <button class="button" type="submit">Submit Fulfillment</button>
                        </form>
                    @else
                        <div class="order-form">
                            <strong class="marketplace-price">{{ $order->formattedAmount() }}</strong>
                            <span class="badge {{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span>
                            <p class="muted">Approved {{ $order->approved_at?->diffForHumans() ?? 'after review' }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>No guest post orders yet</h3>
                    <p>Orders will appear here after advertisers buy placements on your approved websites.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
