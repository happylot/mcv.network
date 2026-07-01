@extends('layouts.app', ['title' => 'Agency Orders | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Orders <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Service Orders</h2>
                <p class="muted">Submit delivery links after completing client work.</p>
            </div>
        </div>
        <div class="marketplace-list">
            @forelse ($orders as $order)
                <article class="marketplace-item">
                    <div>
                        <span class="website-name"><span class="domain-dot blue">●</span>{{ $order->service->title }}</span>
                        <p class="muted">Client: {{ $order->clientAccount->name }} · Due {{ $order->due_at?->format('M j, Y') ?? 'not set' }}</p>
                        <div class="metric-row">
                            <span>Amount <strong>{{ $order->formattedAmount() }}</strong></span>
                            <span>Status <strong>{{ str_replace('_', ' ', $order->status) }}</strong></span>
                        </div>
                        <p>{{ $order->brief }}</p>
                        @if ($order->reference_url)
                            <p><strong>Reference:</strong> <a href="{{ $order->reference_url }}" target="_blank" rel="noopener">{{ $order->reference_url }}</a></p>
                        @endif
                        @if ($order->delivery_url)
                            <p><strong>Delivery:</strong> <a href="{{ $order->delivery_url }}" target="_blank" rel="noopener">{{ $order->delivery_url }}</a></p>
                        @endif
                    </div>
                    @if (in_array($order->status, ['pending_agency', 'submitted'], true))
                        <form class="order-form" method="POST" action="{{ route('agency.orders.submit', $order) }}">
                            @csrf
                            <div class="field">
                                <label for="delivery_url_{{ $order->id }}">Delivery URL</label>
                                <input id="delivery_url_{{ $order->id }}" type="url" name="delivery_url" value="{{ old('delivery_url', $order->delivery_url) }}" required>
                            </div>
                            <div class="field">
                                <label for="agency_notes_{{ $order->id }}">Agency notes</label>
                                <textarea id="agency_notes_{{ $order->id }}" name="agency_notes">{{ old('agency_notes', $order->agency_notes) }}</textarea>
                            </div>
                            <button class="button" type="submit">Submit Delivery</button>
                        </form>
                    @else
                        <div class="order-form">
                            <strong class="marketplace-price">{{ $order->formattedAmount() }}</strong>
                            <span class="badge {{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span>
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>No service orders yet</h3>
                    <p>Orders will appear after clients buy approved services.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
