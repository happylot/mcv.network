@extends('layouts.app', ['title' => 'Advertiser Orders | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Guest Post Orders <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>My Guest Post Orders</h2>
                <p class="muted">Review publisher submissions and approve completed placements to release payout.</p>
            </div>
            <a class="button" href="{{ route('marketplace.websites.index') }}"><i class="fa-solid fa-store"></i> Buy More</a>
        </div>

        <div class="table-wrap">
            <table class="website-table">
                <thead>
                    <tr>
                        <th>Website</th>
                        <th>Publisher</th>
                        <th>Amount</th>
                        <th>Target</th>
                        <th>Published URL</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $order->website->domain }}</span></td>
                            <td>{{ $order->publisherAccount->name }}</td>
                            <td><span class="price">{{ $order->formattedAmount() }}</span></td>
                            <td><a href="{{ $order->target_url }}" target="_blank" rel="noopener">Target</a></td>
                            <td>
                                @if ($order->published_url)
                                    <a href="{{ $order->published_url }}" target="_blank" rel="noopener">View post</a>
                                @else
                                    <span class="muted">Waiting</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td>
                                @if ($order->status === 'submitted')
                                    <form method="POST" action="{{ route('marketplace.orders.approve', $order) }}">
                                        @csrf
                                        <button class="buy-btn" type="submit">Approve</button>
                                    </form>
                                @else
                                    <span class="muted">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No guest post orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
