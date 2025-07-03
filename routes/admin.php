<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\EmailTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Users Management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
        Route::post('users/{user}/adjust-balance', [UserController::class, 'adjustBalance'])->name('users.adjust-balance');

        // Payments Management
        Route::resource('payments', PaymentController::class);
        Route::post('payments/{payment}/update-status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');

        // Withdrawals Management
        Route::resource('withdrawals', WithdrawalController::class)->only(['index', 'show']);
        Route::post('withdrawals/{withdrawal}/update-status', [WithdrawalController::class, 'updateStatus'])->name('withdrawals.update-status');
        Route::post('withdrawals/bulk-approve', [WithdrawalController::class, 'bulkApprove'])->name('withdrawals.bulk-approve');
        Route::post('withdrawals/auto-approve', [WithdrawalController::class, 'autoApprove'])->name('withdrawals.auto-approve');

        // Devices Management
        Route::resource('devices', DeviceController::class);

        // Site Settings
        Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
        Route::post('site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');
        Route::get('site-settings/create', [SiteSettingsController::class, 'create'])->name('site-settings.create');
        Route::post('site-settings/store', [SiteSettingsController::class, 'store'])->name('site-settings.store');

        // Email Templates
        Route::resource('email-templates', EmailTemplateController::class);
    });
});