<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = WithdrawalRequest::with('user');

        if ($request->search) {
            $query->whereHas('user', function($userQuery) use ($request) {
                $userQuery->where('username', 'like', '%' . $request->search . '%')
                         ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->withdrawal_method) {
            $query->where('withdrawal_method', $request->withdrawal_method);
        }

        $withdrawals = $query->latest()->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load('user');
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function updateStatus(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,processing,completed,rejected,cancelled',
            'admin_notes' => 'nullable|string',
            'transaction_hash' => 'nullable|string',
            'external_transaction_id' => 'nullable|string',
        ]);

        $oldStatus = $withdrawal->status;
        
        $withdrawal->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'transaction_hash' => $request->transaction_hash,
            'external_transaction_id' => $request->external_transaction_id,
            'processed_by' => auth('admin')->id(),
            'processed_at' => in_array($request->status, ['approved', 'processing', 'completed']) ? now() : $withdrawal->processed_at,
            'completed_at' => $request->status === 'completed' ? now() : $withdrawal->completed_at,
        ]);

        // If rejected, return funds to user
        if ($request->status === 'rejected' && in_array($oldStatus, ['pending', 'approved', 'processing'])) {
            $user = $withdrawal->user;
            $user->update([
                'balance' => $user->balance + $withdrawal->amount,
            ]);
        }

        // If completed, update user total withdrawn
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $user = $withdrawal->user;
            $user->update([
                'total_withdrawn' => $user->total_withdrawn + $withdrawal->net_amount,
            ]);
        }

        return redirect()->back()->with('success', 'Withdrawal status updated successfully!');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'withdrawal_ids' => 'required|array',
            'withdrawal_ids.*' => 'exists:withdrawal_requests,id',
        ]);

        WithdrawalRequest::whereIn('id', $request->withdrawal_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'processed_by' => auth('admin')->id(),
                'processed_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Selected withdrawals approved successfully!');
    }

    public function autoApprove(Request $request)
    {
        $request->validate([
            'max_amount' => 'required|numeric|min:0',
            'enabled' => 'required|boolean',
        ]);

        // Update system setting for auto-approval
        \App\Models\SystemSetting::updateOrCreate(
            ['setting_key' => 'auto_approve_withdrawals'],
            ['setting_value' => $request->enabled ? '1' : '0']
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['setting_key' => 'auto_approve_max_amount'],
            ['setting_value' => $request->max_amount]
        );

        return redirect()->back()->with('success', 'Auto-approval settings updated successfully!');
    }
}