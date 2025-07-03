<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Investment;
use App\Models\WithdrawalRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_devices' => Device::count(),
            'available_devices' => Device::where('status', 'available')->count(),
            'total_payments' => Payment::count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'total_rentals' => Rental::count(),
            'active_rentals' => Rental::where('status', 'active')->count(),
            'total_investments' => Investment::count(),
            'active_investments' => Investment::where('status', 'active')->count(),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'total_revenue' => Payment::where('status', 'completed')->where('type', 'deposit')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->where('type', 'deposit')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'daily_revenue' => Payment::where('status', 'completed')
                ->where('type', 'deposit')
                ->whereDate('created_at', now()->toDateString())
                ->sum('amount'),
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_payments = Payment::with('user')->latest()->take(5)->get();
        $pending_withdrawals = WithdrawalRequest::with('user')->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_payments', 'pending_withdrawals'));
    }
}