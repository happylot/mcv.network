<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AgencyService;
use App\Models\AgencyServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyServiceMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_can_submit_service_for_admin_review(): void
    {
        [$agencyUser, $agencyAccount] = $this->userWithAccount('agency', 'Creative Agency');

        $this->actingAs($agencyUser)->post(route('agency.services.store'), [
            'title' => 'SEO article package',
            'category' => 'SEO Writing',
            'description' => 'One optimized article for a target keyword.',
            'deliverables' => 'One 1200-word article and meta title.',
            'base_price' => '150.00',
            'turnaround_days' => 5,
        ])->assertRedirect(route('agency.services.index'));

        $this->assertDatabaseHas('agency_services', [
            'agency_account_id' => $agencyAccount->id,
            'title' => 'SEO article package',
            'category' => 'SEO Writing',
            'base_price_cents' => 15000,
            'status' => 'pending_review',
        ]);
    }

    public function test_agency_service_under_100_is_auto_approved(): void
    {
        [$agencyUser, $agencyAccount] = $this->userWithAccount('agency', 'Creative Agency');

        $this->actingAs($agencyUser)->post(route('agency.services.store'), [
            'title' => 'Quick SEO brief',
            'category' => 'SEO Writing',
            'description' => 'A compact SEO content brief for one target page.',
            'deliverables' => 'Keyword angle and outline.',
            'base_price' => '99.99',
            'turnaround_days' => 2,
        ])->assertRedirect(route('agency.services.index'));

        $this->assertDatabaseHas('agency_services', [
            'agency_account_id' => $agencyAccount->id,
            'title' => 'Quick SEO brief',
            'base_price_cents' => 9999,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_approve_agency_service(): void
    {
        [$adminUser] = $this->userWithAccount('admin', 'MCV Admin');
        [, $agencyAccount] = $this->userWithAccount('agency', 'Creative Agency');
        $service = $this->agencyService($agencyAccount, ['status' => 'pending_review']);

        $this->actingAs($adminUser)
            ->post(route('admin.agency-services.approve', $service))
            ->assertRedirect();

        $this->assertSame('approved', $service->refresh()->status);
    }

    public function test_publisher_can_order_approved_agency_service_with_wallet_balance(): void
    {
        [$clientUser, $clientAccount] = $this->userWithAccount('publisher', 'Publisher Studio', 30000);
        [, $agencyAccount] = $this->userWithAccount('agency', 'Creative Agency');
        $service = $this->agencyService($agencyAccount, [
            'status' => 'approved',
            'base_price_cents' => 19900,
        ]);

        $this->actingAs($clientUser)->post(route('services.orders.store', $service), [
            'brief' => 'Design a logo for a new business section.',
            'reference_url' => 'https://example.com/brand',
        ])->assertRedirect(route('services.orders.index'));

        $this->assertSame(10100, $clientAccount->wallet->refresh()->available_balance_cents);
        $this->assertDatabaseHas('agency_service_orders', [
            'client_account_id' => $clientAccount->id,
            'agency_account_id' => $agencyAccount->id,
            'agency_service_id' => $service->id,
            'amount_cents' => 19900,
            'status' => 'pending_agency',
        ]);
        $this->assertDatabaseHas('wallet_ledger_entries', [
            'wallet_id' => $clientAccount->wallet->id,
            'type' => 'agency_service_order',
            'direction' => 'debit',
            'amount_cents' => 19900,
        ]);
    }

    public function test_agency_submits_delivery_and_client_approves_payout_once(): void
    {
        [$clientUser, $clientAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio');
        [$agencyUser, $agencyAccount] = $this->userWithAccount('agency', 'Creative Agency');
        $service = $this->agencyService($agencyAccount, ['status' => 'approved']);
        $order = $this->agencyOrder($clientAccount, $agencyAccount, $service, ['amount_cents' => 17500]);

        $this->actingAs($agencyUser)->post(route('agency.orders.submit', $order), [
            'delivery_url' => 'https://drive.example.com/final-assets',
            'agency_notes' => 'Final logo package delivered.',
        ])->assertRedirect(route('agency.orders.index'));

        $this->assertSame('submitted', $order->refresh()->status);
        $this->assertSame('https://drive.example.com/final-assets', $order->delivery_url);

        $this->actingAs($clientUser)
            ->post(route('services.orders.approve', $order))
            ->assertRedirect(route('services.orders.index'));
        $this->actingAs($clientUser)
            ->post(route('services.orders.approve', $order))
            ->assertRedirect(route('services.orders.index'));

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertSame(17500, $agencyAccount->wallet->refresh()->available_balance_cents);
        $this->assertDatabaseCount('wallet_ledger_entries', 1);
        $this->assertDatabaseHas('wallet_ledger_entries', [
            'wallet_id' => $agencyAccount->wallet->id,
            'type' => 'agency_service_payout',
            'direction' => 'credit',
            'amount_cents' => 17500,
            'idempotency_key' => 'agency_service_order:'.$order->id.':agency_payout',
        ]);
    }

    private function agencyService(Account $agencyAccount, array $overrides = []): AgencyService
    {
        return AgencyService::create(array_merge([
            'agency_account_id' => $agencyAccount->id,
            'title' => 'Logo design package',
            'category' => 'Logo Design',
            'description' => 'Brand logo design service.',
            'deliverables' => 'Two concepts and final files.',
            'base_price_cents' => 12500,
            'turnaround_days' => 7,
            'status' => 'pending_review',
        ], $overrides));
    }

    private function agencyOrder(Account $clientAccount, Account $agencyAccount, AgencyService $service, array $overrides = []): AgencyServiceOrder
    {
        return AgencyServiceOrder::create(array_merge([
            'client_account_id' => $clientAccount->id,
            'agency_account_id' => $agencyAccount->id,
            'agency_service_id' => $service->id,
            'amount_cents' => 12500,
            'currency' => 'USD',
            'status' => 'pending_agency',
            'brief' => 'Need a brand logo.',
            'due_at' => now()->addDays(7),
        ], $overrides));
    }

    private function userWithAccount(string $type, string $accountName, int $availableBalanceCents = 0): array
    {
        $user = User::create([
            'name' => $accountName.' Owner',
            'email' => str($type.'-'.$accountName)->slug()->append('@example.com')->toString(),
            'password' => 'password123',
        ]);

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => $type,
            'can_buy' => true,
            'can_sell_inventory' => $type === 'publisher',
            'can_sell_services' => $type === 'agency',
            'name' => $accountName,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $account->users()->attach($user->id, ['role' => 'owner']);
        $account->wallet()->create([
            'currency' => 'USD',
            'available_balance_cents' => $availableBalanceCents,
        ]);

        return [$user, $account->load('wallet')];
    }
}
