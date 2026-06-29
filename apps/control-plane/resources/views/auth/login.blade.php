@extends('layouts.app', ['title' => 'Login | MCV Ads'])

@section('mainClass', 'auth-wrap')

@section('content')
    <div class="auth-side">
        <h2>Performance Advertising at Scale</h2>
        <p>Beyond walled gardens. Reach millions across the open web with AI targeting and first-party data.</p>
        <ul class="checklist">
            <li>600M+ daily active users</li>
            <li>AI targeting and first-party data</li>
            <li>Real-time ROAS reporting</li>
            <li>Start from just $100</li>
        </ul>
    </div>

    <div class="auth-form">
        <div class="auth-form-inner">
            <h1>Welcome back</h1>
            <p class="sub">Log in to your MCV Network dashboard.</p>

            <a class="btn google-btn" href="{{ route('auth.google.redirect') }}">
                <span class="google-mark" aria-hidden="true">G</span>
                Continue with Google
            </a>

            <div class="auth-divider">or</div>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-field">
                    <label for="email">Work email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-field">
                    <label>
                        <input type="checkbox" name="remember" value="1" style="width:auto; margin-right:8px;">
                        Remember me
                    </label>
                </div>

                <button class="btn btn-primary" type="submit">Log In <span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-alt">New to MCV? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </div>
@endsection
