@extends('layouts.app', ['title' => 'My Websites | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Publisher Websites <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>My Websites</h2>
                <p class="muted">Manage guest post inventory, pricing, and editorial metadata.</p>
            </div>
            <a class="button" href="{{ route('publisher.websites.create') }}"><i class="fa-solid fa-plus"></i> Add Website</a>
        </div>

        @if ($websites->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-globe"></i>
                <h3>No websites yet</h3>
                <p>Add your first website to start the publisher onboarding review.</p>
                <a class="button" href="{{ route('publisher.websites.create') }}">Add Website</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="website-table">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>DR</th>
                            <th>DA</th>
                            <th>Traffic</th>
                            <th>Niche</th>
                            <th>Country</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($websites as $website)
                            <tr>
                                <td>
                                    <span class="website-name">
                                        <span class="domain-dot blue">●</span>
                                        {{ $website->domain }}
                                    </span>
                                    @if ($website->sample_url)
                                        <a class="dash-card-link" href="{{ $website->sample_url }}" target="_blank" rel="noopener">Sample post</a>
                                    @endif
                                </td>
                                <td><span class="score-pill green">{{ $website->domain_rating }}</span></td>
                                <td><span class="score-pill blue">{{ $website->domain_authority }}</span></td>
                                <td>{{ number_format($website->monthly_traffic) }}</td>
                                <td>{{ $website->niche }}</td>
                                <td>{{ $website->country }}</td>
                                <td><span class="price">{{ $website->formattedPrice() }}</span></td>
                                <td><span class="badge {{ $website->status }}">{{ str_replace('_', ' ', $website->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
