<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Investment;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('referral_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->withCount(['rentals', 'investments', 'payments'])
                      ->latest()
                      ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['rentals', 'investments', 'payments', 'withdrawalRequests', 'referralRelationships']);
        
        $stats = [
            'total_deposits' => $user->payments()->where('type', 'deposit')->where('status', 'completed')->sum('amount'),
            'total_withdrawals' => $user->withdrawalRequests()->where('status', 'completed')->sum('amount'),
            'active_rentals' => $user->rentals()->where('status', 'active')->count(),
            'active_investments' => $user->investments()->where('status', 'active')->count(),
            'total_referrals' => $user->referralRelationships()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'status' => 'required|in:active,suspended,pending,banned',
            'balance' => 'required|numeric|min:0',
            'total_earnings' => 'required|numeric|min:0',
            'total_invested' => 'required|numeric|min:0',
            'total_withdrawn' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'kyc_status' => 'required|in:none,pending,approved,rejected',
        ]);

        $user->update($request->only([
            'username', 'email', 'status', 'balance', 'total_earnings',
            'total_invested', 'total_withdrawn', 'phone', 'country', 'kyc_status'
        ]));

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully!');
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:add,subtract',
            'reason' => 'required|string|max:255',
        ]);

        $amount = $request->amount;
        if ($request->type === 'subtract') {
            $amount = -$amount;
        }

        $user->update([
            'balance' => $user->balance + $amount,
        ]);

        // Create payment record for audit
        Payment::create([
            'user_id' => $user->id,
            'transaction_id' => 'ADJ_' . time() . '_' . $user->id,
            'amount' => abs($amount),
            'currency' => 'USD',
            'payment_method' => 'manual',
            'payment_provider' => 'admin',
            'status' => 'completed',
            'type' => $request->type === 'add' ? 'deposit' : 'withdrawal',
            'description' => 'Admin balance adjustment: ' . $request->reason,
            'processed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Balance adjusted successfully!');
    }
}