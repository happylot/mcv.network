<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['sometimes', 'string', 'in:advertiser,publisher,agency'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $account = Account::create([
                'owner_user_id' => $user->id,
                'type' => $validated['account_type'] ?? 'advertiser',
                'can_buy' => true,
                'can_sell_inventory' => ($validated['account_type'] ?? 'advertiser') === 'publisher',
                'can_sell_services' => ($validated['account_type'] ?? 'advertiser') === 'agency',
                'name' => $validated['name'].' Account',
                'status' => 'pending',
                'currency' => 'USD',
            ]);

            $account->users()->attach($user->id, ['role' => 'owner']);
            $account->wallet()->create(['currency' => 'USD']);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Account created. Your profile is pending approval.');
    }
}
