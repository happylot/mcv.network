@extends('layouts.app', ['title' => 'Admin Dashboard | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Admin Dashboard <span aria-hidden="true">»</span></div>

    <section class="dash-stat-grid" aria-label="Admin summary">
        <article class="dash-card dash-stat-card">
            <span class="stat-icon orange"><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <span class="dash-stat-label">Pending Websites</span>
                <strong class="dash-stat-value">{{ number_format($pendingWebsiteCount) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('admin.publisher-websites.index') }}">Review queue →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon green"><i class="fa-solid fa-store"></i></span>
            <div>
                <span class="dash-stat-label">Live Marketplace Sites</span>
                <strong class="dash-stat-value">{{ number_format($approvedWebsiteCount) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('admin.publisher-websites.index', ['status' => 'approved']) }}">View live sites →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon blue"><i class="fa-solid fa-inbox"></i></span>
            <div>
                <span class="dash-stat-label">Open Orders</span>
                <strong class="dash-stat-value">{{ number_format($openOrderCount) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('admin.orders.index') }}">Order operations →</a>
        </article>
    </section>

    <section class="dash-card">
        <div class="dash-panel-header">
            <h2>Latest Publisher Websites</h2>
            <a href="{{ route('admin.publisher-websites.index', ['status' => 'all']) }}">View all →</a>
        </div>

        <div class="table-wrap">
            <table class="website-table">
                <thead>
                    <tr>
                        <th>Website</th>
                        <th>Publisher</th>
                        <th>Niche</th>
                        <th>Traffic</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentWebsites as $website)
                        <tr>
                            <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $website->domain }}</span></td>
                            <td>{{ $website->account->name }}</td>
                            <td>{{ $website->niche }}</td>
                            <td>{{ number_format($website->monthly_traffic) }}</td>
                            <td><span class="price">{{ $website->formattedPrice() }}</span></td>
                            <td><span class="badge {{ $website->status }}">{{ str_replace('_', ' ', $website->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No publisher websites submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
