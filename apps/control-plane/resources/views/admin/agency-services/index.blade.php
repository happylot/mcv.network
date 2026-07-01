@extends('layouts.app', ['title' => 'Agency Service Review | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Service Review <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>Agency Service Review</h2>
                <p class="muted">Approve agency service packages before clients can buy them.</p>
            </div>
            <form method="GET" action="{{ route('admin.agency-services.index') }}">
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
                        <th>Service</th>
                        <th>Agency</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td><span class="website-name"><span class="domain-dot blue">●</span>{{ $service->title }}</span><p class="muted">{{ str($service->description)->limit(90) }}</p></td>
                            <td>{{ $service->agencyAccount->name }}</td>
                            <td>{{ $service->category }}</td>
                            <td><span class="price">{{ $service->formattedPrice() }}</span></td>
                            <td><span class="badge {{ $service->status }}">{{ str_replace('_', ' ', $service->status) }}</span></td>
                            <td>
                                <div class="action-row">
                                    <form method="POST" action="{{ route('admin.agency-services.approve', $service) }}">@csrf<button class="buy-btn" type="submit">Approve</button></form>
                                    <form method="POST" action="{{ route('admin.agency-services.reject', $service) }}">@csrf<button class="button secondary" type="submit">Reject</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No services match this status.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
