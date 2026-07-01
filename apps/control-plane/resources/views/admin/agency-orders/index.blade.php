@extends('layouts.app', ['title' => 'Agency Order Review | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Order Review <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Agency Order Review</h2>
                <p class="muted">Approve submitted agency deliveries when admin intervention is needed.</p>
            </div>
            <form method="GET" action="{{ route('admin.agency-orders.index') }}">
                <select name="status" onchange="this.form.submit()">
                    @foreach (['submitted' => 'Submitted', 'pending_agency' => 'Pending agency', 'completed' => 'Completed', 'all' => 'All'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table class="website-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Agency</th>
                        <th>Amount</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $order->service->title }}</span></td>
                            <td>{{ $order->clientAccount->name }}</td>
                            <td>{{ $order->agencyAccount->name }}</td>
                            <td><span class="price">{{ $order->formattedAmount() }}</span></td>
                            <td>@if ($order->delivery_url)<a href="{{ $order->delivery_url }}" target="_blank" rel="noopener">View delivery</a>@else<span class="muted">Waiting</span>@endif</td>
                            <td><span class="badge {{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td>
                                @if ($order->status === 'submitted')
                                    <form method="POST" action="{{ route('admin.agency-orders.approve', $order) }}">@csrf<button class="buy-btn" type="submit">Approve</button></form>
                                @else
                                    <span class="muted">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No orders match this status.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
