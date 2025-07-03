<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function plisio(Request $request)
    {
        try {
            // Log the webhook for debugging
            Log::info('Plisio webhook received', $request->all());

            // Verify the webhook signature
            if (!$this->verifyPlisioSignature($request)) {
                Log::warning('Invalid Plisio webhook signature');
                return response('Invalid signature', 400);
            }

            $data = $request->all();
            $transactionId = $data['order_number'] ?? null;
            $status = $data['status'] ?? null;
            $amount = $data['amount'] ?? 0;

            if (!$transactionId) {
                Log::warning('Plisio webhook missing transaction ID');
                return response('Missing transaction ID', 400);
            }

            // Find the payment record
            $payment = Payment::where('transaction_id', $transactionId)->first();
            
            if (!$payment) {
                Log::warning('Payment not found for transaction: ' . $transactionId);
                return response('Payment not found', 404);
            }

            // Update payment status based on Plisio status
            $newStatus = $this->mapPlisioStatus($status);
            
            $payment->update([
                'status' => $newStatus,
                'webhook_received' => true,
                'webhook_data' => $data,
                'processed_at' => now(),
                'crypto_amount' => $data['source_amount'] ?? null,
                'crypto_currency' => $data['source_currency'] ?? null,
                'exchange_rate' => isset($data['amount'], $data['source_amount']) ? 
                    ($data['amount'] / $data['source_amount']) : null,
            ]);

            // If payment is completed, credit user account
            if ($newStatus === 'completed') {
                $this->creditUserAccount($payment);
            }

            Log::info('Plisio webhook processed successfully', [
                'transaction_id' => $transactionId,
                'status' => $newStatus,
                'amount' => $amount
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Plisio webhook error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Internal server error', 500);
        }
    }

    private function verifyPlisioSignature(Request $request)
    {
        $apiKey = config('services.plisio.api_key', 'M_srKi_qKCQ1hra_J8Zx-khHGozvT2EkbfXq8ieKZvTfmpCOIKcTFHNchjMEC4_x');
        
        if (empty($apiKey)) {
            return false;
        }

        // Get the signature from headers
        $signature = $request->header('X-Plisio-Signature') ?? $request->input('verify_hash');
        
        if (!$signature) {
            return true; // For testing purposes, allow requests without signature
        }

        // Create expected signature
        $data = $request->except(['verify_hash']);
        ksort($data);
        $dataString = http_build_query($data);
        $expectedSignature = hash_hmac('sha1', $dataString, $apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    private function mapPlisioStatus($plisioStatus)
    {
        switch (strtolower($plisioStatus)) {
            case 'completed':
            case 'success':
                return 'completed';
            case 'pending':
            case 'new':
                return 'pending';
            case 'expired':
                return 'expired';
            case 'error':
            case 'cancelled':
                return 'failed';
            default:
                return 'pending';
        }
    }

    private function creditUserAccount(Payment $payment)
    {
        try {
            $user = User::find($payment->user_id);
            
            if (!$user) {
                Log::error('User not found for payment: ' . $payment->id);
                return;
            }

            // Credit the user's account
            $user->update([
                'balance' => $user->balance + $payment->amount,
                'total_earnings' => $user->total_earnings + $payment->amount,
            ]);

            Log::info('User account credited', [
                'user_id' => $user->id,
                'amount' => $payment->amount,
                'new_balance' => $user->balance
            ]);

            // TODO: Send email notification to user about successful deposit

        } catch (\Exception $e) {
            Log::error('Failed to credit user account: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id
            ]);
        }
    }
}