<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * API V1 Routes
 * Base URL: /api/v1/
 */
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

// Add other API versions here as needed
// Route::prefix('v2')->group(base_path('routes/api/v2.php'));
