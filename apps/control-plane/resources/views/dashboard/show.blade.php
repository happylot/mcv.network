@extends('layouts.app', ['title' => 'Dashboard | MCV Ads'])

@section('content')
    <div class="stack">
        <section>
            <h1>{{ $account->name }}</h1>
            <p>Advertiser account <span class="badge {{ $account->status }}">{{ $account->status }}</span></p>
        </section>

        <section class="grid" aria-label="Wallet summary">
            <div class="stat">
                <span class="stat-label">Available balance</span>
                <strong class="stat-value">{{ $wallet->formattedBalance() }}</strong>
            </div>
            <div class="stat">
                <span class="stat-label">Pending balance</span>
                <strong class="stat-value">${{ number_format($wallet->pending_balance_cents / 100, 2) }}</strong>
            </div>
            <div class="stat">
                <span class="stat-label">Currency</span>
                <strong class="stat-value">{{ $wallet->currency }}</strong>
            </div>
        </section>

        <section class="two-col">
            <div class="panel">
                <h2>Add funds</h2>
                <p>Manual bank transfer top-ups are available now. Card payments will be enabled after Stripe keys and webhooks are configured.</p>
                <a class="button" href="{{ route('billing.index') }}">Open billing</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Recent transaction</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEntries as $entry)
                            <tr>
                                <td>{{ ucfirst($entry->type) }} {{ $entry->direction }}</td>
                                <td>{{ $entry->formattedAmount() }}</td>
                                <td><span class="badge {{ $entry->status }}">{{ $entry->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="muted">No wallet activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
