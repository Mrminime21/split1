<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('withdrawals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:20|max:' . auth()->user()->balance,
            'method' => 'required|in:crypto,bank_transfer,paypal,binance',
            'address' => 'required|string',
        ]);

        // Implementation for creating withdrawal request
        // This would handle withdrawal processing
        
        return redirect()->route('dashboard')->with('success', 'Withdrawal request submitted successfully!');
    }
}