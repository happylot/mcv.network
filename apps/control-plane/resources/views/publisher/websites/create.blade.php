@extends('layouts.app', ['title' => 'Add Website | MCV Ads'])

@section('content')
    <div class="dashboard-crumb">Add Publisher Website <span aria-hidden="true">»</span></div>

    <section class="two-col">
        <div class="panel">
            <h1>Add a website</h1>
            <p class="muted">Submit the metrics advertisers need to evaluate a guest post placement. New websites start in review.</p>

            <ul class="checklist">
                <li>Use the root domain only, without paths.</li>
                <li>Set a realistic editorial turnaround time.</li>
                <li>Add guidelines so buyers know what content is accepted.</li>
            </ul>
        </div>

        <div class="panel">
            <form method="POST" action="{{ route('publisher.websites.store') }}">
                @csrf

                <div class="field">
                    <label for="domain">Domain</label>
                    <input id="domain" name="domain" value="{{ old('domain') }}" placeholder="example.com" required>
                    @error('domain') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="name">Website name</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Example Magazine">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="grid">
                    <div class="field">
                        <label for="niche">Niche</label>
                        <select id="niche" name="niche" required>
                            @foreach (['Business', 'Finance', 'Health', 'Technology', 'Travel', 'Lifestyle', 'News and Media', 'General'] as $niche)
                                <option value="{{ $niche }}" @selected(old('niche', 'Business') === $niche)>{{ $niche }}</option>
                            @endforeach
                        </select>
                        @error('niche') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="language">Language</label>
                        <input id="language" name="language" value="{{ old('language', 'en') }}" maxlength="16" required>
                        @error('language') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="country">Country</label>
                        <input id="country" name="country" value="{{ old('country', 'US') }}" maxlength="2" required>
                        @error('country') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label for="monthly_traffic">Monthly traffic</label>
                        <input id="monthly_traffic" type="number" min="0" name="monthly_traffic" value="{{ old('monthly_traffic', 0) }}" required>
                        @error('monthly_traffic') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="domain_rating">DR</label>
                        <input id="domain_rating" type="number" min="0" max="100" name="domain_rating" value="{{ old('domain_rating', 0) }}" required>
                        @error('domain_rating') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="domain_authority">DA</label>
                        <input id="domain_authority" type="number" min="0" max="100" name="domain_authority" value="{{ old('domain_authority', 0) }}" required>
                        @error('domain_authority') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label for="guest_post_price">Guest post price USD</label>
                        <input id="guest_post_price" type="number" min="5" step="0.01" name="guest_post_price" value="{{ old('guest_post_price', 25) }}" required>
                        @error('guest_post_price') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="turnaround_days">Turnaround days</label>
                        <input id="turnaround_days" type="number" min="1" max="60" name="turnaround_days" value="{{ old('turnaround_days', 3) }}" required>
                        @error('turnaround_days') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="sample_url">Sample URL</label>
                        <input id="sample_url" type="url" name="sample_url" value="{{ old('sample_url') }}" placeholder="https://example.com/sample-post">
                        @error('sample_url') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="guidelines">Editorial guidelines</label>
                    <textarea id="guidelines" name="guidelines" placeholder="Topics accepted, link policy, word count, dofollow/nofollow rules...">{{ old('guidelines') }}</textarea>
                    @error('guidelines') <div class="error">{{ $message }}</div> @enderror
                </div>

                <button class="button" type="submit">Submit for Review</button>
            </form>
        </div>
    </section>
@endsection
