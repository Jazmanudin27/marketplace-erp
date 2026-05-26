<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::prefix('users')->group(function () {
        Route::get('/', 'Api\V1\UserController@index');
        Route::post('/', 'Api\V1\UserController@store');
        Route::get('/{user}', 'Api\V1\UserController@show');
        Route::put('/{user}', 'Api\V1\UserController@update');
        Route::delete('/{user}', 'Api\V1\UserController@destroy');
    });

    // Marketplace routes
    Route::prefix('marketplaces')->group(function () {
        Route::get('/', 'Api\V1\MarketplaceController@index');
        Route::post('/', 'Api\V1\MarketplaceController@store');
        Route::get('/{marketplace}', 'Api\V1\MarketplaceController@show');
        Route::put('/{marketplace}', 'Api\V1\MarketplaceController@update');
        Route::delete('/{marketplace}', 'Api\V1\MarketplaceController@destroy');
    });

    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', 'Api\V1\ProductController@index');
        Route::post('/', 'Api\V1\ProductController@store');
        Route::get('/{product}', 'Api\V1\ProductController@show');
        Route::put('/{product}', 'Api\V1\ProductController@update');
        Route::delete('/{product}', 'Api\V1\ProductController@destroy');
    });

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/', 'Api\V1\OrderController@index');
        Route::post('/', 'Api\V1\OrderController@store');
        Route::get('/{order}', 'Api\V1\OrderController@show');
        Route::put('/{order}', 'Api\V1\OrderController@update');
        Route::delete('/{order}', 'Api\V1\OrderController@destroy');
    });

    // Company routes
    Route::prefix('companies')->group(function () {
        Route::get('/', 'Api\V1\CompanyController@index');
        Route::post('/', 'Api\V1\CompanyController@store');
        Route::get('/{company}', 'Api\V1\CompanyController@show');
        Route::put('/{company}', 'Api\V1\CompanyController@update');
        Route::delete('/{company}', 'Api\V1\CompanyController@destroy');
    });

    // Analytics routes
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', 'Api\V1\AnalyticsController@dashboard');
        Route::get('/sales', 'Api\V1\AnalyticsController@sales');
        Route::get('/inventory', 'Api\V1\AnalyticsController@inventory');
    });
});

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', 'Api\V1\AuthController@login');
    Route::post('/register', 'Api\V1\AuthController@register');
});

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::post('/marketplace/{account}', 'Api\V1\WebhookController@handle');
});
