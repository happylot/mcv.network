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
        $accountType = request()->query('account_type');
        if (in_array($accountType, ['advertiser', 'publisher', 'agency'], true)) {
            request()->session()->put('google_account_type', $accountType);
        } else {
            request()->session()->forget('google_account_type');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $accountType = request()->session()->pull('google_account_type', 'advertiser');

        $user = DB::transaction(function () use ($googleUser, $accountType): User {
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'google_avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                $this->ensureAccount($user, $accountType);

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

            $this->ensureAccount($user, $accountType);

            return $user;
        });

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function ensureAccount(User $user, string $accountType): void
    {
        if ($user->currentAccount()) {
            return;
        }

        $accountName = ($user->name ?: Str::before($user->email, '@')).' Account';
        $accountType = in_array($accountType, ['advertiser', 'publisher', 'agency'], true) ? $accountType : 'advertiser';

        $account = Account::create([
            'owner_user_id' => $user->id,
            'type' => $accountType,
            'name' => $accountName,
            'status' => 'pending',
            'currency' => 'USD',
        ]);

        $account->users()->attach($user->id, ['role' => 'owner']);
        $account->wallet()->create(['currency' => 'USD']);
    }
}
