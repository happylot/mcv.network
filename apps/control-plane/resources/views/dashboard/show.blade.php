@extends('layouts.app', ['title' => 'Dashboard | MCV Ads'])

@section('mainClass', 'dashboard-page')

@php
    $availablePublishers = 32675;
    $liveWebsites = 100766;
    $recentActivities = $recentEntries->map(function ($entry) {
        return [
            'text' => ucfirst($entry->type).' '.$entry->direction.' request for '.$entry->formattedAmount(),
            'time' => $entry->created_at?->diffForHumans() ?? 'just now',
            'icon' => 'fa-solid fa-coins',
        ];
    })->take(3)->values();

    if ($recentActivities->isEmpty()) {
        $recentActivities = collect([
            ['text' => 'You have created a project name: '.$account->name, 'time' => '7 hours ago', 'icon' => 'fa-solid fa-square-plus'],
            ['text' => 'Your advertiser account is pending approval', 'time' => '7 hours ago', 'icon' => 'fa-solid fa-circle-check'],
            ['text' => 'Wallet is ready for your first top-up', 'time' => 'today', 'icon' => 'fa-solid fa-wallet'],
        ]);
    }

    $websites = [
        ['name' => 'www.beforeitsnews.com', 'dot' => 'gold', 'dr' => 74, 'da' => 78, 'traffic' => '1.1M', 'niche' => 'Health', 'price' => '$24.95'],
        ['name' => 'www.timebusinessnews.com', 'dot' => 'gray', 'dr' => 71, 'da' => 60, 'traffic' => '49.8K', 'niche' => 'News and Media', 'price' => '$14.97'],
        ['name' => 'www.newsbreak.com', 'dot' => 'red', 'dr' => 83, 'da' => 78, 'traffic' => '26.6M', 'niche' => 'General', 'price' => '$24.95'],
        ['name' => 'www.ventsmagazine.com', 'dot' => 'gray', 'dr' => 77, 'da' => 63, 'traffic' => '19.1K', 'niche' => 'Music', 'price' => '$19.96'],
        ['name' => 'www.biznewnetwork.com', 'dot' => 'blue', 'dr' => 72, 'da' => 62, 'traffic' => '104.4K', 'niche' => 'Business', 'price' => '$24.95'],
    ];
@endphp

@section('content')
    <div class="dashboard-crumb">Dashboard <span aria-hidden="true">»</span></div>

    <section class="dash-stat-grid" aria-label="Dashboard summary">
        <article class="dash-card dash-stat-card">
            <span class="stat-icon blue"><i class="fa-solid fa-briefcase"></i></span>
            <div>
                <span class="dash-stat-label">Active Tasks</span>
                <strong class="dash-stat-value">0</strong>
            </div>
            <a class="dash-card-link" href="#">View all tasks →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon green"><i class="fa-solid fa-chart-pie"></i></span>
            <div>
                <span class="dash-stat-label">Pending Tasks</span>
                <strong class="dash-stat-value">0</strong>
            </div>
            <a class="dash-card-link" href="#">View all tasks →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon purple"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="dash-stat-label">Wallet Balance</span>
                <strong class="dash-stat-value">{{ $wallet->formattedBalance() }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('billing.index') }}">Add Funds →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon orange"><i class="fa-solid fa-chart-column"></i></span>
            <div>
                <span class="dash-stat-label">Total Spent</span>
                <strong class="dash-stat-value">$0.00</strong>
            </div>
            <a class="dash-card-link" href="#">This month's spending</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon sky"><i class="fa-solid fa-users"></i></span>
            <div>
                <span class="dash-stat-label">Available Publishers</span>
                <strong class="dash-stat-value">{{ number_format($availablePublishers) }}</strong>
            </div>
            <a class="dash-card-link" href="#">View All Publishers →</a>
        </article>

        <article class="dash-card dash-stat-card">
            <span class="stat-icon red"><i class="fa-solid fa-sitemap"></i></span>
            <div>
                <span class="dash-stat-label">Live Websites</span>
                <strong class="dash-stat-value">{{ number_format($liveWebsites) }}</strong>
            </div>
            <a class="dash-card-link" href="#">View All Websites →</a>
        </article>
    </section>

    <section class="dashboard-grid">
        <div class="dash-card">
            <div class="dash-panel-header">
                <h2>Recommended Websites For You</h2>
                <a href="#">View all websites →</a>
            </div>

            <div class="table-wrap">
                <table class="website-table">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($websites as $website)
                            <tr>
                                <td>
                                    <span class="website-name">
                                        <span class="domain-dot {{ $website['dot'] }}">●</span>
                                        {{ $website['name'] }}
                                    </span>
                                </td>
                                <td><span class="score-pill green">{{ $website['dr'] }}</span></td>
                                <td><span class="score-pill blue">{{ $website['da'] }}</span></td>
                                <td>{{ $website['traffic'] }}</td>
                                <td>{{ $website['niche'] }}</td>
                                <td><span class="flag-us">🇺🇸</span></td>
                                <td><span class="price">{{ $website['price'] }}</span></td>
                                <td>2 days</td>
                                <td><a class="buy-btn" href="#">Buy<br>Post</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="right-rail" aria-label="Dashboard side panels">
            <article class="dash-card wallet-card">
                <span class="wallet-mini-icon"><i class="fa-solid fa-wallet"></i></span>
                <h3>Wallet Balance</h3>
                <strong>{{ $wallet->formattedBalance() }}</strong>
                <a class="button" href="{{ route('billing.index') }}"><i class="fa-solid fa-plus"></i> Add Funds</a>
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
