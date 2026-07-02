<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_publisher_can_submit_guest_post_website_for_review(): void
    {
        [$user, $account] = $this->publisherUser();

        $response = $this->actingAs($user)->post('/publisher/websites', [
            'domain' => 'https://www.examplepublisher.com/write-for-us',
            'name' => 'Example Publisher',
            'niche' => 'Business',
            'language' => 'en',
            'country' => 'US',
            'monthly_traffic' => 125000,
            'domain_rating' => 72,
            'domain_authority' => 61,
            'guest_post_price' => '149.95',
            'turnaround_days' => 4,
            'guidelines' => 'Business topics only. One contextual link allowed.',
            'sample_url' => 'https://examplepublisher.com/sample',
        ]);

        $response->assertRedirect('/publisher/websites');

        $this->assertDatabaseHas('publisher_websites', [
            'account_id' => $account->id,
            'domain' => 'examplepublisher.com',
            'name' => 'Example Publisher',
            'niche' => 'Business',
            'language' => 'en',
            'country' => 'US',
            'monthly_traffic' => 125000,
            'domain_rating' => 72,
            'domain_authority' => 61,
            'guest_post_price_cents' => 14995,
            'turnaround_days' => 4,
            'status' => 'pending_review',
        ]);
    }

    public function test_advertiser_cannot_access_publisher_inventory(): void
    {
        [$user] = $this->advertiserUser();

        $this->actingAs($user)->get('/publisher/websites')->assertForbidden();
        $this->actingAs($user)->post('/publisher/websites', [
            'domain' => 'example.com',
            'niche' => 'Business',
            'language' => 'en',
            'country' => 'US',
            'monthly_traffic' => 1000,
            'domain_rating' => 10,
            'domain_authority' => 10,
            'guest_post_price' => '25.00',
            'turnaround_days' => 3,
        ])->assertForbidden();
    }

    public function test_advertiser_can_enable_publisher_capability(): void
    {
        [$user, $account] = $this->advertiserUser();

        $this->actingAs($user)
            ->post(route('account.capabilities.store'), ['capability' => 'sell_inventory'])
            ->assertRedirect();

        $this->assertTrue($account->refresh()->canSellInventory());
        $this->actingAs($user)->get('/publisher/websites')->assertOk();
    }

    public function test_publisher_dashboard_uses_unified_capability_view(): void
    {
        [$user] = $this->publisherUser();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Sell Inventory')
            ->assertSee('Manage Websites');
    }

    private function publisherUser(): array
    {
        return $this->userWithAccount('publisher', 'Publisher Studio');
    }

    private function advertiserUser(): array
    {
        return $this->userWithAccount('advertiser', 'Advertiser Studio');
    }

    private function userWithAccount(string $type, string $accountName): array
    {
        $user = User::create([
            'name' => $accountName.' Owner',
            'email' => str($accountName)->slug()->append('@example.com')->toString(),
            'password' => 'password123',
        ]);

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => $type,
            'can_buy' => true,
            'can_sell_inventory' => $type === 'publisher',
            'can_sell_services' => $type === 'agency',
            'name' => $accountName,
            'status' => 'pending',
            'currency' => 'USD',
        ]);

        $account->users()->attach($user->id, ['role' => 'owner']);
        $account->wallet()->create(['currency' => 'USD']);

        return [$user, $account];
    }
}
