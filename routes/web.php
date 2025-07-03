<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Auth::routes();

// Override registration route to handle referral codes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile.show');
    
    // Rentals
    Route::prefix('rentals')->name('rentals.')->group(function () {
        Route::get('/', [RentalController::class, 'index'])->name('index');
        Route::post('/', [RentalController::class, 'store'])->name('store');
    });
    
    // Investments
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', [InvestmentController::class, 'index'])->name('index');
        Route::post('/', [InvestmentController::class, 'store'])->name('store');
    });
    
    // Referrals
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
    });
    
    // Deposits
    Route::prefix('deposits')->name('deposits.')->group(function () {
        Route::get('/create', [DepositController::class, 'create'])->name('create');
        Route::post('/', [DepositController::class, 'store'])->name('store');
        Route::get('/success', [DepositController::class, 'success'])->name('success');
        Route::get('/failed', [DepositController::class, 'failed'])->name('failed');
    });
    
    // Withdrawals
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/create', [WithdrawalController::class, 'create'])->name('create');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
    });
});

// Webhook Routes (public)
Route::post('/webhooks/plisio', [WebhookController::class, 'plisio'])->name('webhooks.plisio');