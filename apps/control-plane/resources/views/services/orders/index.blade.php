@extends('layouts.app', ['title' => 'Service Orders | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Service Orders <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>My Service Orders</h2>
                <p class="muted">Review agency deliveries and approve completed work to release payout.</p>
            </div>
            <a class="button" href="{{ route('services.index') }}"><i class="fa-solid fa-briefcase"></i> Hire More</a>
        </div>
        <div class="table-wrap">
            <table class="website-table">
                <thead>
                    <tr>
                        <th>Service</th>
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
                            <td>{{ $order->agencyAccount->name }}</td>
                            <td><span class="price">{{ $order->formattedAmount() }}</span></td>
                            <td>
                                @if ($order->delivery_url)
                                    <a href="{{ $order->delivery_url }}" target="_blank" rel="noopener">View delivery</a>
                                @else
                                    <span class="muted">Waiting</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td>
                                @if ($order->status === 'submitted')
                                    <form method="POST" action="{{ route('services.orders.approve', $order) }}">
                                        @csrf
                                        <button class="buy-btn" type="submit">Approve</button>
                                    </form>
                                @else
                                    <span class="muted">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No service orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
