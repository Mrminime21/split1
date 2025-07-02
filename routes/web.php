<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile.show');
    
    // Rentals
    Route::prefix('rentals')->name('rentals.')->group(function () {
        Route::get('/', function () {
            return view('rentals.index');
        })->name('index');
    });
    
    // Investments
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', function () {
            return view('investments.index');
        })->name('index');
    });
    
    // Referrals
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', function () {
            return view('referrals.index');
        })->name('index');
    });
    
    // Deposits
    Route::prefix('deposits')->name('deposits.')->group(function () {
        Route::get('/create', function () {
            return view('deposits.create');
        })->name('create');
    });
    
    // Withdrawals
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/create', function () {
            return view('withdrawals.create');
        })->name('create');
    });
});