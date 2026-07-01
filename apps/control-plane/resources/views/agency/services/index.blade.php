@extends('layouts.app', ['title' => 'Agency Services | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Agency Services <span aria-hidden="true">»</span></div>

    <section class="dash-card">
        <div class="dash-panel-header">
            <div>
                <h2>My Services</h2>
                <p class="muted">Package services such as SEO writing, logo design, video production, or ads management.</p>
            </div>
            <a class="button" href="{{ route('agency.services.create') }}"><i class="fa-solid fa-plus"></i> Add Service</a>
        </div>

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
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <span class="website-name"><span class="domain-dot blue">●</span>{{ $service->title }}</span>
                                <p class="muted">{{ str($service->description)->limit(100) }}</p>
                            </td>
                            <td>{{ $service->category }}</td>
                            <td><span class="price">{{ $service->formattedPrice() }}</span></td>
                            <td>{{ $service->turnaround_days }} days</td>
                            <td><span class="badge {{ $service->status }}">{{ str_replace('_', ' ', $service->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No services yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
