@extends('layouts.app', ['title' => 'Agency Services Marketplace | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Services <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Hire Agency Services</h2>
                <p class="muted">Order SEO writing, logo design, video production, and ads management from approved agencies.</p>
            </div>
            <form method="GET" action="{{ route('services.index') }}">
                <select name="category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="marketplace-list">
            @forelse ($services as $service)
                <article class="marketplace-item">
                    <div>
                        <span class="website-name"><span class="domain-dot blue">●</span>{{ $service->title }}</span>
                        <p class="muted">{{ $service->category }} · Agency: {{ $service->agencyAccount->name }}</p>
                        <div class="metric-row">
                            <span>Price <strong>{{ $service->formattedPrice() }}</strong></span>
                            <span>{{ $service->turnaround_days }} days</span>
                        </div>
                        <p>{{ $service->description }}</p>
                        @if ($service->deliverables)
                            <p class="muted">{{ $service->deliverables }}</p>
                        @endif
                    </div>
                    <form class="order-form" method="POST" action="{{ route('services.orders.store', $service) }}">
                        @csrf
                        <strong class="marketplace-price">{{ $service->formattedPrice() }}</strong>
                        <div class="field">
                            <label for="brief_{{ $service->id }}">Brief</label>
                            <textarea id="brief_{{ $service->id }}" name="brief" placeholder="Describe what you need" required></textarea>
                        </div>
                        <div class="field">
                            <label for="reference_url_{{ $service->id }}">Reference URL</label>
                            <input id="reference_url_{{ $service->id }}" type="url" name="reference_url" placeholder="Optional">
                        </div>
                        <button class="button" type="submit">Order Service</button>
                    </form>
                </article>
            @empty
                <div class="empty-state">
                    <i class="fa-solid fa-briefcase"></i>
                    <h3>No approved agency services yet</h3>
                    <p>Admin-approved agency services will appear here.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
