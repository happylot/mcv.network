@extends('layouts.app', ['title' => 'Order Review | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Order Review <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Guest Post Order Review</h2>
                <p class="muted">Approve submitted fulfillments when admin intervention is needed.</p>
            </div>
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <select name="status" onchange="this.form.submit()">
                    @foreach (['submitted' => 'Submitted', 'pending_publisher' => 'Pending publisher', 'completed' => 'Completed', 'all' => 'All'] as $value => $label)
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
                        <th>Advertiser</th>
                        <th>Publisher</th>
                        <th>Amount</th>
                        <th>Published URL</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $order->website->domain }}</span></td>
                            <td>{{ $order->advertiserAccount->name }}</td>
                            <td>{{ $order->publisherAccount->name }}</td>
                            <td><span class="price">{{ $order->formattedAmount() }}</span></td>
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
                                    <form method="POST" action="{{ route('admin.orders.approve', $order) }}">
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
                            <td colspan="7">No orders match this status.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
