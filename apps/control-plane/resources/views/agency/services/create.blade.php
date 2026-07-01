@extends('layouts.app', ['title' => 'Add Agency Service | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Add Agency Service <span aria-hidden="true">»</span></div>

    <section class="two-col">
        <div class="panel">
            <h1>Add a service</h1>
            <p class="muted">Create a package that other roles can buy from the services marketplace.</p>
            <ul class="checklist">
                <li>Use clear deliverables and turnaround.</li>
                <li>Services go live after admin approval.</li>
                <li>Payout is released after client or admin approval.</li>
            </ul>
        </div>

        <div class="panel">
            <form method="POST" action="{{ route('agency.services.store') }}">
                @csrf
                <div class="field">
                    <label for="title">Service title</label>
                    <input id="title" name="title" value="{{ old('title') }}" placeholder="SEO article writing package" required>
                    @error('title') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        @foreach (['SEO Writing', 'Logo Design', 'Video Production', 'Video Editing', 'Ads Management', 'Creative Strategy', 'Landing Page'] as $category)
                            <option value="{{ $category }}" @selected(old('category', 'SEO Writing') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="grid">
                    <div class="field">
                        <label for="base_price">Base price USD</label>
                        <input id="base_price" type="number" min="5" step="0.01" name="base_price" value="{{ old('base_price', 99) }}" required>
                        @error('base_price') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label for="turnaround_days">Turnaround days</label>
                        <input id="turnaround_days" type="number" min="1" max="90" name="turnaround_days" value="{{ old('turnaround_days', 7) }}" required>
                        @error('turnaround_days') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required>{{ old('description') }}</textarea>
                    @error('description') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="deliverables">Deliverables</label>
                    <textarea id="deliverables" name="deliverables" placeholder="Word count, number of design concepts, video duration, campaign channels...">{{ old('deliverables') }}</textarea>
                    @error('deliverables') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button class="button" type="submit">Submit for Review</button>
            </form>
        </div>
    </section>
@endsection
