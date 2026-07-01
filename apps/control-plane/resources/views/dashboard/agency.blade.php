@extends('layouts.app', ['title' => 'Agency Dashboard | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Dashboard <span aria-hidden="true">»</span></div>

    <section class="dash-stat-grid" aria-label="Agency summary">
        <article class="dash-card dash-stat-card">
            <span class="stat-icon blue"><i class="fa-solid fa-briefcase"></i></span>
            <div>
                <span class="dash-stat-label">Total Services</span>
                <strong class="dash-stat-value">{{ number_format($services->count()) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('agency.services.index') }}">Manage services →</a>
        </article>
        <article class="dash-card dash-stat-card">
            <span class="stat-icon green"><i class="fa-solid fa-circle-check"></i></span>
            <div>
                <span class="dash-stat-label">Live Services</span>
                <strong class="dash-stat-value">{{ number_format($services->where('status', 'approved')->count()) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('agency.services.create') }}">Add service →</a>
        </article>
        <article class="dash-card dash-stat-card">
            <span class="stat-icon orange"><i class="fa-solid fa-inbox"></i></span>
            <div>
                <span class="dash-stat-label">Open Orders</span>
                <strong class="dash-stat-value">{{ number_format($openOrderCount) }}</strong>
            </div>
            <a class="dash-card-link" href="{{ route('agency.orders.index') }}">Order queue →</a>
        </article>
        <article class="dash-card dash-stat-card">
            <span class="stat-icon purple"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="dash-stat-label">Available Payout</span>
                <strong class="dash-stat-value">{{ $wallet->formattedBalance() }}</strong>
            </div>
            <a class="dash-card-link" href="#">Payouts →</a>
        </article>
    </section>

    <section class="dash-card">
        <div class="dash-panel-header">
            <h2>Agency Service Catalog</h2>
            <a href="{{ route('agency.services.create') }}">Add service →</a>
        </div>

        @if ($services->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-briefcase"></i>
                <h3>Add your first service</h3>
                <p>Create packages for SEO writing, logo design, video production, or ads management.</p>
                <a class="button" href="{{ route('agency.services.create') }}">Add Service</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="website-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Turnaround</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $service->title }}</span></td>
                                <td>{{ $service->category }}</td>
                                <td><span class="price">{{ $service->formattedPrice() }}</span></td>
                                <td>{{ $service->turnaround_days }} days</td>
                                <td><span class="badge {{ $service->status }}">{{ str_replace('_', ' ', $service->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
