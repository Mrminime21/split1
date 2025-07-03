<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Payment::with('user');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
                  ->orWhere('external_id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('username', 'like', '%' . $request->search . '%')
                               ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest()->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load('user');
        return view('admin.payments.show', compact('payment'));
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,cancelled,refunded,expired',
            'admin_notes' => 'nullable|string',
        ]);

        $oldStatus = $payment->status;
        $payment->update([
            'status' => $request->status,
            'processed_at' => $request->status === 'completed' ? now() : $payment->processed_at,
        ]);

        // If payment is completed and was pending, credit user account
        if ($request->status === 'completed' && $oldStatus === 'pending' && $payment->type === 'deposit') {
            $user = $payment->user;
            $user->update([
                'balance' => $user->balance + $payment->amount,
                'total_earnings' => $user->total_earnings + $payment->amount,
            ]);
        }

        return redirect()->back()->with('success', 'Payment status updated successfully!');
    }

    public function create()
    {
        $users = User::where('status', 'active')->get();
        return view('admin.payments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:deposit,withdrawal,fee,refund',
            'payment_method' => 'required|in:crypto,binance,manual',
            'description' => 'required|string|max:255',
            'status' => 'required|in:pending,completed',
        ]);

        $payment = Payment::create([
            'user_id' => $request->user_id,
            'transaction_id' => 'ADMIN_' . time() . '_' . $request->user_id,
            'amount' => $request->amount,
            'currency' => 'USD',
            'payment_method' => $request->payment_method,
            'payment_provider' => 'admin',
            'status' => $request->status,
            'type' => $request->type,
            'description' => $request->description,
            'processed_at' => $request->status === 'completed' ? now() : null,
        ]);

        // If completed deposit, credit user account
        if ($request->status === 'completed' && $request->type === 'deposit') {
            $user = User::find($request->user_id);
            $user->update([
                'balance' => $user->balance + $request->amount,
                'total_earnings' => $user->total_earnings + $request->amount,
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment created successfully!');
    }
}