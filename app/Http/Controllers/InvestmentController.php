<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $plans = [
            [
                'name' => '3 Months Plan',
                'duration' => 90,
                'rate' => 0.27,
                'min_amount' => 500,
                'total_return' => 24,
            ],
            [
                'name' => '6 Months Plan',
                'duration' => 180,
                'rate' => 0.40,
                'min_amount' => 1000,
                'total_return' => 72,
            ],
            [
                'name' => '12 Months Plan',
                'duration' => 365,
                'rate' => 0.60,
                'min_amount' => 2000,
                'total_return' => 216,
            ],
        ];

        return view('investments.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:3months,6months,12months',
            'amount' => 'required|numeric|min:500',
        ]);

        // Implementation for creating investment
        // This would handle payment processing, plan assignment, etc.
        
        return redirect()->route('dashboard')->with('success', 'Investment created successfully!');
    }
}