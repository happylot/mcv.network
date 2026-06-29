@extends('layouts.app', ['title' => 'Billing | MCV Ads'])

@section('content')
    <div class="stack">
        <section>
            <h1>Billing</h1>
            <p>{{ $account->name }} wallet and top-up history.</p>
        </section>

        <section class="grid" aria-label="Wallet summary">
            <div class="stat">
                <span class="stat-label">Available</span>
                <strong class="stat-value">{{ $wallet->formattedBalance() }}</strong>
            </div>
            <div class="stat">
                <span class="stat-label">Pending</span>
                <strong class="stat-value">${{ number_format($wallet->pending_balance_cents / 100, 2) }}</strong>
            </div>
            <div class="stat">
                <span class="stat-label">Account status</span>
                <strong class="stat-value">{{ ucfirst($account->status) }}</strong>
            </div>
        </section>

        <section class="two-col">
            <div class="panel">
                <h2>Request top-up</h2>
                <form method="POST" action="{{ route('billing.top-up.store') }}">
                    @csrf
                    <div class="field">
                        <label for="amount">Amount in USD</label>
                        <input id="amount" type="number" name="amount" value="{{ old('amount', '100') }}" min="10" max="50000" step="0.01" required>
                        @error('amount') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="method">Payment method</label>
                        <select id="method" name="method" required>
                            <option value="bank_transfer">Manual bank transfer</option>
                        </select>
                        @error('method') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="reference_note">Reference note</label>
                        <textarea id="reference_note" name="reference_note" placeholder="Bank name, transfer memo, or internal PO number">{{ old('reference_note') }}</textarea>
                        @error('reference_note') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <button class="button" type="submit">Create top-up request</button>
                </form>
            </div>

            <div class="panel">
                <h2>Bank transfer instructions</h2>
                <p class="muted">Use the generated payment reference in your bank transfer memo. Admin reconciliation will post the funds to available balance in Phase 2.</p>
                <p><strong>Beneficiary:</strong> MCV Network Ads</p>
                <p><strong>Currency:</strong> USD</p>
                <p><strong>Status after request:</strong> Pending</p>
            </div>
        </section>

        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Payment</th>
                        <th>Provider</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->provider_reference ?? 'Payment #'.$payment->id }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($payment->provider)) }}</td>
                            <td>{{ $payment->formattedAmount() }}</td>
                            <td><span class="badge {{ $payment->status }}">{{ $payment->status }}</span></td>
                            <td>{{ $payment->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">No payment requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ledger entry</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ledgerEntries as $entry)
                        <tr>
                            <td>{{ ucfirst($entry->type) }}</td>
                            <td>{{ ucfirst($entry->direction) }}</td>
                            <td>{{ $entry->formattedAmount() }}</td>
                            <td><span class="badge {{ $entry->status }}">{{ $entry->status }}</span></td>
                            <td>{{ $entry->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">No ledger entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
