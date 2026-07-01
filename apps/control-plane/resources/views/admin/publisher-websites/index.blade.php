@extends('layouts.app', ['title' => 'Publisher Website Review | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Website Review <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Publisher Website Review</h2>
                <p class="muted">Approve quality inventory before it becomes visible in the advertiser marketplace.</p>
            </div>
            <form method="GET" action="{{ route('admin.publisher-websites.index') }}">
                <select name="status" onchange="this.form.submit()">
                    @foreach (['pending_review' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-wrap">
            <table class="website-table">
                <thead>
                    <tr>
                        <th>Website</th>
                        <th>Publisher</th>
                        <th>Metrics</th>
                        <th>Niche</th>
                        <th>Price</th>
                        <th>Guidelines</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($websites as $website)
                        <tr>
                            <td>
                                <span class="website-name"><span class="domain-dot blue">●</span>{{ $website->domain }}</span>
                                @if ($website->sample_url)
                                    <a class="dash-card-link" href="{{ $website->sample_url }}" target="_blank" rel="noopener">Sample</a>
                                @endif
                            </td>
                            <td>{{ $website->account->name }}</td>
                            <td>
                                <span class="score-pill green">{{ $website->domain_rating }}</span>
                                <span class="score-pill blue">{{ $website->domain_authority }}</span>
                                <div>{{ number_format($website->monthly_traffic) }} visits/mo</div>
                            </td>
                            <td>{{ $website->niche }}</td>
                            <td><span class="price">{{ $website->formattedPrice() }}</span></td>
                            <td>{{ str($website->guidelines ?: 'No guidelines provided.')->limit(90) }}</td>
                            <td><span class="badge {{ $website->status }}">{{ str_replace('_', ' ', $website->status) }}</span></td>
                            <td>
                                <div class="action-row">
                                    <form method="POST" action="{{ route('admin.publisher-websites.approve', $website) }}">
                                        @csrf
                                        <button class="buy-btn" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.publisher-websites.reject', $website) }}">
                                        @csrf
                                        <button class="button secondary" type="submit">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No websites match this status.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
