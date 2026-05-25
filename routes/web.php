<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MarketplaceConnectionController;
use App\Http\Controllers\WebhookController;
use App\Models\MarketplaceAccount;
use App\Services\Marketplace\MarketplaceManager;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated routes
Route::middleware(['auth', \App\Http\Middleware\CompanyMiddleware::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Marketplace connection routes
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/accounts', [MarketplaceConnectionController::class, 'index'])->name('accounts');
        Route::get('/accounts/{account}', [MarketplaceConnectionController::class, 'show'])->name('show');
        Route::post('/connect', [MarketplaceConnectionController::class, 'connect'])->name('connect');
        Route::get('/callback', [MarketplaceConnectionController::class, 'callback'])->name('callback');
        Route::delete('/accounts/{account}', [MarketplaceConnectionController::class, 'disconnect'])->name('disconnect');
    });
});

// Test routes (remove in production)
Route::get('/test-shopee', function () {
    $account = MarketplaceAccount::first();
    $service = MarketplaceManager::driver('shopee');
    return $service->getOrders($account);
});

// Webhook routes
Route::post('/webhook/shopee', [WebhookController::class, 'shopee']);

