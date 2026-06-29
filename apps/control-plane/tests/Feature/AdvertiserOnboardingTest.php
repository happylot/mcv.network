<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertiserOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_advertiser_account_and_wallet(): void
    {
        $response = $this->post('/register', [
            'name' => 'Minh Nguyen',
            'company_name' => 'Softel Ads',
            'email' => 'minh@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'minh@example.com')->firstOrFail();
        $account = Account::where('owner_user_id', $user->id)->firstOrFail();

        $this->assertSame('advertiser', $account->type);
        $this->assertSame('pending', $account->status);
        $this->assertDatabaseHas('account_user', [
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
        $this->assertDatabaseHas('wallets', [
            'account_id' => $account->id,
            'currency' => 'USD',
            'available_balance_cents' => 0,
            'pending_balance_cents' => 0,
        ]);
    }

    public function test_authenticated_user_can_create_bank_transfer_top_up_request(): void
    {
        $user = User::create([
            'name' => 'Lan Tran',
            'email' => 'lan@example.com',
            'password' => 'password123',
        ]);

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => 'advertiser',
            'name' => 'Lan Media',
            'status' => 'pending',
            'currency' => 'USD',
        ]);
        $account->users()->attach($user->id, ['role' => 'owner']);
        $wallet = $account->wallet()->create(['currency' => 'USD']);

        $response = $this->actingAs($user)->post('/billing/top-up', [
            'amount' => '125.50',
            'method' => 'bank_transfer',
            'reference_note' => 'Transfer memo MCV-001',
        ]);

        $response->assertRedirect('/billing');

        $this->assertDatabaseHas('payments', [
            'account_id' => $account->id,
            'provider' => 'bank_transfer',
            'amount_cents' => 12550,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('wallet_ledger_entries', [
            'wallet_id' => $wallet->id,
            'type' => 'topup',
            'direction' => 'credit',
            'amount_cents' => 12550,
            'status' => 'pending',
            'reference_type' => 'payment',
        ]);
        $this->assertSame(12550, $wallet->refresh()->pending_balance_cents);
        $this->assertSame(0, $wallet->available_balance_cents);
    }
}
