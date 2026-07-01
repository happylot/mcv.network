<?php

use App\Http\Controllers\AdminAgencyServiceController;
use App\Http\Controllers\AdminAgencyServiceOrderController;
use App\Http\Controllers\AdminGuestPostOrderController;
use App\Http\Controllers\AdminPublisherWebsiteController;
use App\Http\Controllers\AdvertiserGuestPostOrderController;
use App\Http\Controllers\AgencyClientOrderController;
use App\Http\Controllers\AgencyMarketplaceController;
use App\Http\Controllers\AgencyServiceController;
use App\Http\Controllers\AgencyServiceOrderController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingPageController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PublisherGuestPostOrderController;
use App\Http\Controllers\PublisherWebsiteController;
use App\Http\Controllers\StripeWebhookController;
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
    Route::get('/billing/stripe/success', [BillingController::class, 'stripeSuccess'])->name('billing.stripe.success');
    Route::get('/billing/stripe/cancel', [BillingController::class, 'stripeCancel'])->name('billing.stripe.cancel');
    Route::get('/marketplace/websites', [MarketplaceController::class, 'index'])->name('marketplace.websites.index');
    Route::post('/marketplace/websites/{website}/orders', [MarketplaceController::class, 'store'])->name('marketplace.orders.store');
    Route::get('/marketplace/orders', [AdvertiserGuestPostOrderController::class, 'index'])->name('marketplace.orders.index');
    Route::post('/marketplace/orders/{order}/approve', [AdvertiserGuestPostOrderController::class, 'approve'])->name('marketplace.orders.approve');
    Route::get('/services', [AgencyMarketplaceController::class, 'index'])->name('services.index');
    Route::post('/services/{service}/orders', [AgencyMarketplaceController::class, 'store'])->name('services.orders.store');
    Route::get('/services/orders', [AgencyClientOrderController::class, 'index'])->name('services.orders.index');
    Route::post('/services/orders/{order}/approve', [AgencyClientOrderController::class, 'approve'])->name('services.orders.approve');
    Route::get('/agency/services', [AgencyServiceController::class, 'index'])->name('agency.services.index');
    Route::get('/agency/services/create', [AgencyServiceController::class, 'create'])->name('agency.services.create');
    Route::post('/agency/services', [AgencyServiceController::class, 'store'])->name('agency.services.store');
    Route::get('/agency/orders', [AgencyServiceOrderController::class, 'index'])->name('agency.orders.index');
    Route::post('/agency/orders/{order}/submit', [AgencyServiceOrderController::class, 'submit'])->name('agency.orders.submit');
    Route::get('/publisher/orders', [PublisherGuestPostOrderController::class, 'index'])->name('publisher.orders.index');
    Route::post('/publisher/orders/{order}/submit', [PublisherGuestPostOrderController::class, 'submit'])->name('publisher.orders.submit');
    Route::get('/publisher/websites', [PublisherWebsiteController::class, 'index'])->name('publisher.websites.index');
    Route::get('/publisher/websites/create', [PublisherWebsiteController::class, 'create'])->name('publisher.websites.create');
    Route::post('/publisher/websites', [PublisherWebsiteController::class, 'store'])->name('publisher.websites.store');
    Route::get('/admin/publisher-websites', [AdminPublisherWebsiteController::class, 'index'])->name('admin.publisher-websites.index');
    Route::post('/admin/publisher-websites/{website}/approve', [AdminPublisherWebsiteController::class, 'approve'])->name('admin.publisher-websites.approve');
    Route::post('/admin/publisher-websites/{website}/reject', [AdminPublisherWebsiteController::class, 'reject'])->name('admin.publisher-websites.reject');
    Route::get('/admin/orders', [AdminGuestPostOrderController::class, 'index'])->name('admin.orders.index');
    Route::post('/admin/orders/{order}/approve', [AdminGuestPostOrderController::class, 'approve'])->name('admin.orders.approve');
    Route::get('/admin/agency-services', [AdminAgencyServiceController::class, 'index'])->name('admin.agency-services.index');
    Route::post('/admin/agency-services/{service}/approve', [AdminAgencyServiceController::class, 'approve'])->name('admin.agency-services.approve');
    Route::post('/admin/agency-services/{service}/reject', [AdminAgencyServiceController::class, 'reject'])->name('admin.agency-services.reject');
    Route::get('/admin/agency-orders', [AdminAgencyServiceOrderController::class, 'index'])->name('admin.agency-orders.index');
    Route::post('/admin/agency-orders/{order}/approve', [AdminAgencyServiceOrderController::class, 'approve'])->name('admin.agency-orders.approve');
});

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::get('/{path}', MarketingPageController::class)
    ->where('path', '.*')
    ->name('marketing.page');
