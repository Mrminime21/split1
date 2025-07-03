<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'total_earnings' => $user->total_earnings,
            'active_rentals' => $user->rentals()->where('status', 'active')->count(),
            'total_referrals' => $user->referralRelationships()->count(),
            'current_balance' => $user->balance,
        ];

        return view('dashboard', compact('stats'));
    }
}