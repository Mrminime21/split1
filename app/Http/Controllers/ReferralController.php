<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'total_referrals' => $user->referralRelationships()->count(),
            'level1_referrals' => $user->referralRelationships()->where('level', 1)->count(),
            'level2_referrals' => $user->referralRelationships()->where('level', 2)->count(),
            'level3_referrals' => $user->referralRelationships()->where('level', 3)->count(),
            'total_earnings' => $user->referral_earnings,
        ];

        $referrals = $user->referralRelationships()
            ->with('referred')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('referrals.index', compact('stats', 'referrals'));
    }
}