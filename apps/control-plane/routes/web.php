<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', MarketingPageController::class)->name('marketing.home');

Route::middleware('guest')->group(function (): void {
    Route::redirect('/register', '/signup');
    Route::get('/signup', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/signup', [RegisteredUserController::class, 'store']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/top-up', [BillingController::class, 'store'])->name('billing.top-up.store');
});

Route::get('/{path}', MarketingPageController::class)
    ->where('path', '.*')
    ->name('marketing.page');
