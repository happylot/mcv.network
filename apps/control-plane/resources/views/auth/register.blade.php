@extends('layouts.app', ['title' => 'Sign Up | MCV Network'])

@section('mainClass', 'auth-wrap')

@section('content')
    <div class="auth-side">
        <h2>Buy, sell, or do both.</h2>
        <p>Start with one marketplace mode today, then enable more capabilities whenever your workflow grows.</p>
        <ul class="checklist">
            <li>Buy guest posts and creative services</li>
            <li>Sell publisher inventory after review</li>
            <li>Sell agency services and receive orders</li>
            <li>One wallet, one dashboard, multiple capabilities</li>
        </ul>
    </div>

    <div class="auth-form">
        <div class="auth-form-inner">
            <h1>Create your account</h1>
            <p class="sub">Choose your starting intent. You can enable more capabilities later.</p>

            <div class="form-field">
                <label for="account_type">I want to start as...</label>
                <select id="account_type" name="account_type" form="register-form" data-google-account-type>
                    <option value="advertiser" @selected(old('account_type', request('account_type', 'advertiser')) === 'advertiser')>Buyer / Advertiser</option>
                    <option value="publisher" @selected(old('account_type', request('account_type')) === 'publisher')>Publisher / Inventory seller</option>
                    <option value="agency" @selected(old('account_type', request('account_type')) === 'agency')>Agency / Service seller</option>
                </select>
                @error('account_type') <div class="error">{{ $message }}</div> @enderror
            </div>

            <a class="btn google-btn" href="{{ route('auth.google.redirect') }}" data-google-signup-url="{{ route('auth.google.redirect') }}">
                <span class="google-mark" aria-hidden="true">G</span>
                Continue with Google
            </a>

            <div class="auth-divider">or</div>

            <form id="register-form" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label for="email">Work email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>

                <button class="btn btn-primary" type="submit">Create Account <span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-alt">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </div>

    <script>
        (() => {
            const roleSelect = document.querySelector('[data-google-account-type]');
            const googleLink = document.querySelector('[data-google-signup-url]');

            if (!roleSelect || !googleLink) {
                return;
            }

            const updateGoogleRole = () => {
                const url = new URL(googleLink.dataset.googleSignupUrl, window.location.origin);
                url.searchParams.set('account_type', roleSelect.value);
                googleLink.href = url.toString();
            };

            roleSelect.addEventListener('change', updateGoogleRole);
            updateGoogleRole();
        })();
    </script>
@endsection
