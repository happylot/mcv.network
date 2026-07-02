<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_sends_user_to_provider(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['openid', 'profile', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new SymfonyRedirectResponse('https://accounts.google.com/o/oauth2/v2/auth'));

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);

        $this->get('/auth/google')
            ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_google_callback_creates_user_account_and_wallet(): void
    {
        $googleUser = Mockery::mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getId')->andReturn('google-user-123');
        $googleUser->shouldReceive('getEmail')->andReturn('mai@example.com');
        $googleUser->shouldReceive('getName')->andReturn('Mai Nguyen');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $this->app->instance(SocialiteFactory::class, $socialite);

        $response = $this->withSession(['google_account_type' => 'publisher'])
            ->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'mai@example.com')->firstOrFail();

        $this->assertSame('google-user-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('accounts', [
            'owner_user_id' => $user->id,
            'type' => 'publisher',
            'can_buy' => true,
            'can_sell_inventory' => true,
            'can_sell_services' => false,
            'name' => 'Mai Nguyen Account',
            'status' => 'pending',
            'currency' => 'USD',
        ]);
        $this->assertDatabaseHas('wallets', [
            'account_id' => $user->ownedAccounts()->firstOrFail()->id,
            'currency' => 'USD',
        ]);
    }
}
