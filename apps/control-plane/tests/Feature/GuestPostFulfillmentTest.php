<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\GuestPostOrder;
use App\Models\PublisherWebsite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPostFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_publisher_submits_url_and_advertiser_approves_releasing_payout_once(): void
    {
        [$advertiserUser, $advertiserAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio');
        [$publisherUser, $publisherAccount] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisherAccount);
        $order = $this->guestPostOrder($advertiserAccount, $publisherAccount, $website, ['amount_cents' => 12500]);

        $this->actingAs($publisherUser)
            ->post(route('publisher.orders.submit', $order), [
                'published_url' => 'https://publisher.example.com/live-article',
                'publisher_notes' => 'Published with one contextual link.',
            ])
            ->assertRedirect(route('publisher.orders.index'));

        $this->assertDatabaseHas('guest_post_orders', [
            'id' => $order->id,
            'status' => 'submitted',
            'published_url' => 'https://publisher.example.com/live-article',
        ]);

        $this->actingAs($advertiserUser)
            ->post(route('marketplace.orders.approve', $order))
            ->assertRedirect(route('marketplace.orders.index'));

        $this->actingAs($advertiserUser)
            ->post(route('marketplace.orders.approve', $order))
            ->assertRedirect(route('marketplace.orders.index'));

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertNotNull($order->approved_at);
        $this->assertSame(12500, $publisherAccount->wallet->refresh()->available_balance_cents);
        $this->assertDatabaseCount('wallet_ledger_entries', 1);
        $this->assertDatabaseHas('wallet_ledger_entries', [
            'wallet_id' => $publisherAccount->wallet->id,
            'type' => 'guest_post_payout',
            'direction' => 'credit',
            'amount_cents' => 12500,
            'status' => 'posted',
            'idempotency_key' => 'guest_post_order:'.$order->id.':publisher_payout',
        ]);
    }

    public function test_admin_can_approve_submitted_order_and_release_payout(): void
    {
        [$adminUser, $adminAccount] = $this->userWithAccount('admin', 'MCV Admin');
        [, $advertiserAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio');
        [, $publisherAccount] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisherAccount);
        $order = $this->guestPostOrder($advertiserAccount, $publisherAccount, $website, [
            'status' => 'submitted',
            'published_url' => 'https://publisher.example.com/live-article',
            'submitted_at' => now(),
            'amount_cents' => 9900,
        ]);

        $this->actingAs($adminUser)
            ->post(route('admin.orders.approve', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertSame($adminAccount->id, $order->approved_by_account_id);
        $this->assertSame(9900, $publisherAccount->wallet->refresh()->available_balance_cents);
    }

    public function test_advertiser_cannot_approve_order_before_publisher_submission(): void
    {
        [$advertiserUser, $advertiserAccount] = $this->userWithAccount('advertiser', 'Advertiser Studio');
        [, $publisherAccount] = $this->userWithAccount('publisher', 'Publisher Studio');
        $website = $this->publisherWebsite($publisherAccount);
        $order = $this->guestPostOrder($advertiserAccount, $publisherAccount, $website);

        $this->actingAs($advertiserUser)
            ->post(route('marketplace.orders.approve', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame('pending_publisher', $order->refresh()->status);
        $this->assertSame(0, $publisherAccount->wallet->refresh()->available_balance_cents);
    }

    private function guestPostOrder(Account $advertiser, Account $publisher, PublisherWebsite $website, array $overrides = []): GuestPostOrder
    {
        return GuestPostOrder::create(array_merge([
            'advertiser_account_id' => $advertiser->id,
            'publisher_account_id' => $publisher->id,
            'publisher_website_id' => $website->id,
            'amount_cents' => 12500,
            'currency' => 'USD',
            'status' => 'pending_publisher',
            'target_url' => 'https://brand.example.com/page',
            'anchor_text' => 'brand example',
            'article_title' => 'Campaign planning guide',
            'content_requirements' => 'Professional tone.',
            'due_at' => now()->addDays(3),
        ], $overrides));
    }

    private function publisherWebsite(Account $account): PublisherWebsite
    {
        return PublisherWebsite::create([
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
            'status' => 'approved',
        ]);
    }

    private function userWithAccount(string $type, string $accountName): array
    {
        $user = User::create([
            'name' => $accountName.' Owner',
            'email' => str($type.'-'.$accountName)->slug()->append('@example.com')->toString(),
            'password' => 'password123',
        ]);

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => $type,
            'name' => $accountName,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $account->users()->attach($user->id, ['role' => 'owner']);
        $account->wallet()->create(['currency' => 'USD']);

        return [$user, $account->load('wallet')];
    }
}
