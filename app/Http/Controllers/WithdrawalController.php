<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
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
            'method' => 'required|in:crypto,binance',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $feePercentage = 2.0; // 2%
        $fee = max(($amount * $feePercentage) / 100, 5.0); // Minimum $5 fee
        $netAmount = $amount - $fee;

        if ($amount > $user->balance) {
            return back()->withErrors(['amount' => 'Insufficient balance for this withdrawal.']);
        }

        try {
            // Create withdrawal request
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee_amount' => $fee,
                'net_amount' => $netAmount,
                'withdrawal_method' => $request->method,
                'withdrawal_address' => $request->address,
                'user_notes' => $request->notes,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // Deduct amount from user balance (hold the funds)
            $user->update([
                'balance' => $user->balance - $amount
            ]);

            $methodName = $request->method === 'crypto' ? 'Cryptocurrency' : 'Binance Pay';
            
            return redirect()->route('dashboard')->with('success', 
                "Withdrawal request submitted successfully! Your {$methodName} withdrawal of \${$netAmount} will be processed within 24-48 hours.");

        } catch (\Exception $e) {
            \Log::error('Withdrawal request error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create withdrawal request. Please try again.']);
        }
    }
}