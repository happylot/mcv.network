<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = DB::transaction(function () use ($googleUser): User {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'google_avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                $this->ensureAdvertiserAccount($user);

                return $user;
            }

            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($googleUser->getEmail(), '@'),
                'email' => $googleUser->getEmail(),
                'password' => Str::password(32),
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            $this->ensureAdvertiserAccount($user);

            return $user;
        });

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function ensureAdvertiserAccount(User $user): void
    {
        if ($user->currentAccount()) {
            return;
        }

        $companyName = Str::of($user->email)
            ->after('@')
            ->beforeLast('.')
            ->replace(['.', '-', '_'], ' ')
            ->title()
            ->append(' Ads')
            ->toString();

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => 'advertiser',
            'name' => $companyName,
            'status' => 'pending',
            'currency' => 'USD',
        ]);

        $account->users()->attach($user->id, ['role' => 'owner']);
        $account->wallet()->create(['currency' => 'USD']);
    }
}
