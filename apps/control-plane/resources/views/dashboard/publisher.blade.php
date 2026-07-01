@extends('layouts.app', ['title' => 'Publisher Dashboard | MCV Ads'])

@section('mainClass', 'dashboard-page')

@php
    $approvedWebsites = $websites->where('status', 'approved')->count();
    $pendingWebsites = $websites->where('status', 'pending_review')->count();
    $averagePrice = $websites->avg('guest_post_price_cents') ?? 0;
    $recentActivities = collect([
        ['text' => 'Publisher account is ready for website submissions', 'time' => 'today', 'icon' => 'fa-solid fa-circle-check'],
        ['text' => 'Submitted websites will be reviewed before marketplace listing', 'time' => 'today', 'icon' => 'fa-solid fa-shield-halved'],
        ['text' => 'Payout wallet is ready after your first completed order', 'time' => 'today', 'icon' => 'fa-solid fa-wallet'],
    ]);
@endphp

@section('content')
    <div class="dashboard-crumb">Publisher Dashboard <span aria-hidden="true">»</span></div>

    <section class="dash-stat-grid" aria-label="Publisher summary">
        <article class="dash-card dash-stat-card">
            <span class="stat-icon blue"><i class="fa-solid fa-globe"></i></span>
            <div>
                <span class="dash-stat-label">Total Websites</span>
                <strong class="dash-stat-value">{{ number_format($websites->count()) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('publisher.websites.index') }}">Manage inventory →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="dash-stat-label">Live Listings</span>
                <strong class="dash-stat-value">{{ number_format($approvedWebsites) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('publisher.websites.index') }}">View approved sites →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon purple"><i class="fa-solid fa-hourglass-half"></i></span>
            <div>
                <span class="dash-stat-label">Pending Review</span>
                <strong class="dash-stat-value">{{ number_format($pendingWebsites) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('publisher.websites.create') }}">Submit another site →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon orange"><i class="fa-solid fa-tag"></i></span>
            <div>
                <span class="dash-stat-label">Avg. Guest Post Price</span>
                <strong class="dash-stat-value">${{ number_format($averagePrice / 100, 2) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('publisher.websites.index') }}">Review pricing →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon sky"><i class="fa-solid fa-inbox"></i></span>
            <div>
                <span class="dash-stat-label">Open Orders</span>
                <strong class="dash-stat-value">{{ number_format($openOrderCount) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('publisher.orders.index') }}">Order queue →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon red"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="dash-stat-label">Available Payout</span>
                <strong class="dash-stat-value">{{ $wallet->formattedBalance() }}</strong>
            </div>
            <a class="dash-card-link" href="#">Payouts →</a>
        </article>
    </section>

    <section class="dashboard-grid">
        <div class="dash-card">
            <div class="dash-panel-header">
                <h2>Your Guest Post Inventory</h2>
                <a href="{{ route('publisher.websites.create') }}">Add website →</a>
            </div>

            @if ($websites->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-globe"></i>
                    <h3>Add your first website</h3>
                    <p>Submit your site, metrics, pricing, and editorial rules so advertisers can buy guest posts after approval.</p>
                    <a class="button" href="{{ route('publisher.websites.create') }}">Add Website</a>
                </div>
            @else
                <div class="table-wrap">
                    <table class="website-table website-table--compact">
                        <thead>
                            <tr>
                                <th>Website</th>
                                <th>DR</th>
                                <th>DA</th>
                                <th>Traffic</th>
                                <th>Niche</th>
                                <th>Language</th>
                                <th>Price</th>
                                <th>Turnaround</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($websites as $website)
                                <tr>
                                    <td>
                                        <span class="website-name">
                                            <span class="domain-dot blue">●</span>
                                            <span class="website-domain">{{ $website->domain }}</span>
                                        </span>
                                    </td>
                                    <td><span class="score-pill green">{{ $website->domain_rating }}</span></td>
                                    <td><span class="score-pill blue">{{ $website->domain_authority }}</span></td>
                                    <td>{{ number_format($website->monthly_traffic) }}</td>
                                    <td>{{ $website->niche }}</td>
                                    <td>{{ strtoupper($website->language) }}</td>
                                    <td><span class="price">{{ $website->formattedPrice() }}</span></td>
                                    <td>{{ $website->turnaround_days }} days</td>
                                    <td><span class="badge {{ $website->status }}">{{ str_replace('_', ' ', $website->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <aside class="right-rail" aria-label="Publisher side panels">
            <article class="dash-card wallet-card">
                <span class="wallet-mini-icon"><i class="fa-solid fa-wallet"></i></span>
                <h3>Available Payout</h3>
                <strong>{{ $wallet->formattedBalance() }}</strong>
                <a class="button" href="#"><i class="fa-solid fa-money-bill-transfer"></i> Payouts</a>
            </article>

            <article class="activity-card">
                <div class="activity-head">
                    <h2>Recent Activity</h2>
                    <a href="#">View all</a>
                </div>
                <div class="activity-list">
                    @foreach ($recentActivities as $activity)
                        <div class="activity-item">
                            <i class="{{ $activity['icon'] }}"></i>
                            <div>
                                <p>{{ $activity['text'] }}</p>
                                <span class="activity-time">{{ $activity['time'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </aside>
    </section>
@endsection
