<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\PublisherWebsite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPostMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_publisher_website_for_marketplace(): void
    {
        [$admin] = $this->userWithAccount('admin', 'MCV Admin');
        [, $publisher] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisher, ['status' => 'pending_review']);

        $this->actingAs($admin)
            ->post(route('admin.publisher-websites.approve', $website))
            ->assertRedirect();

        $this->assertSame('approved', $website->refresh()->status);
    }

    public function test_non_admin_cannot_approve_publisher_website(): void
    {
        [$advertiser] = $this->userWithAccount('advertiser', 'Advertiser Studio');
        [, $publisher] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisher, ['status' => 'pending_review']);

        $this->actingAs($advertiser)
            ->post(route('admin.publisher-websites.approve', $website))
            ->assertForbidden();

        $this->assertSame('pending_review', $website->refresh()->status);
    }

    public function test_advertiser_can_buy_approved_guest_post_with_wallet_balance(): void
    {
        [$advertiserUser, $advertiserAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio', 20000);
        [, $publisherAccount] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisherAccount, [
            'status' => 'approved',
            'guest_post_price_cents' => 14995,
            'turnaround_days' => 5,
        ]);

        $response = $this->actingAs($advertiserUser)->post(route('marketplace.orders.store', $website), [
            'target_url' => 'https://brand.example.com/landing-page',
            'anchor_text' => 'best campaign tools',
            'article_title' => 'How teams plan better campaigns',
            'content_requirements' => 'Use a professional tone and include one contextual link.',
        ]);

        $response->assertRedirect(route('marketplace.websites.index'));

        $this->assertDatabaseHas('guest_post_orders', [
            'advertiser_account_id' => $advertiserAccount->id,
            'publisher_account_id' => $publisherAccount->id,
            'publisher_website_id' => $website->id,
            'amount_cents' => 14995,
            'currency' => 'USD',
            'status' => 'pending_publisher',
            'target_url' => 'https://brand.example.com/landing-page',
        ]);
        $this->assertSame(5005, $advertiserAccount->wallet->refresh()->available_balance_cents);
        $this->assertDatabaseHas('wallet_ledger_entries', [
            'wallet_id' => $advertiserAccount->wallet->id,
            'type' => 'guest_post_order',
            'direction' => 'debit',
            'amount_cents' => 14995,
            'status' => 'posted',
            'reference_type' => 'guest_post_order',
        ]);
    }

    public function test_advertiser_needs_wallet_balance_to_buy_guest_post(): void
    {
        [$advertiserUser, $advertiserAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio', 500);
        [, $publisherAccount] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisherAccount, [
            'status' => 'approved',
            'guest_post_price_cents' => 14995,
        ]);

        $this->actingAs($advertiserUser)
            ->post(route('marketplace.orders.store', $website), [
                'target_url' => 'https://brand.example.com/landing-page',
            ])
            ->assertRedirect(route('billing.index'));

        $this->assertSame(500, $advertiserAccount->wallet->refresh()->available_balance_cents);
        $this->assertDatabaseCount('guest_post_orders', 0);
        $this->assertDatabaseMissing('wallet_ledger_entries', [
            'wallet_id' => $advertiserAccount->wallet->id,
            'type' => 'guest_post_order',
        ]);
    }

    private function publisherWebsite(Account $account, array $overrides = []): PublisherWebsite
    {
        return PublisherWebsite::create(array_merge([
            'account_id' => $account->id,
            'domain' => str($account->name)->slug()->append('.com')->toString(),
            'name' => $account->name,
            'niche' => 'Business',
            'language' => 'en',
            'country' => 'US',
            'monthly_traffic' => 100000,
            'domain_rating' => 70,
            'domain_authority' => 60,
            'guest_post_price_cents' => 12500,
            'turnaround_days' => 3,
            'guidelines' => 'Business content only.',
            'status' => 'pending_review',
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
