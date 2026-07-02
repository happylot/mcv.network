<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_homepage_shows_marketing_site(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Ba vai trò. Một sàn giao dịch. Không trung gian thừa.', false)
            ->assertSee('account_type=advertiser', false)
            ->assertSee('account_type=publisher', false)
            ->assertSee('account_type=agency', false);
    }

    public function test_mcv_network_root_shows_marketing_site(): void
    {
        $response = $this->withHeader('Host', 'mcv.network')->get('/');

        $response
            ->assertOk()
            ->assertSee('Ba vai trò. Một sàn giao dịch. Không trung gian thừa.', false);
    }

    public function test_ads_network_root_redirects_guest_to_login(): void
    {
        $response = $this->withHeader('Host', 'ads.mcv.network')->get('/');

        $response->assertRedirectToRoute('login');
    }

    public function test_ads_network_root_redirects_authenticated_user_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withHeader('Host', 'ads.mcv.network')
            ->get('/');

        $response->assertRedirectToRoute('dashboard');
    }

    public function test_ads_network_marketing_paths_redirect_guest_to_login(): void
    {
        $response = $this->withHeader('Host', 'ads.mcv.network')->get('/advertisers/');

        $response->assertRedirectToRoute('login');
    }
}
