<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('deposits.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50|max:10000',
            'method' => 'required|in:crypto,binance',
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $method = $request->method;

        // Generate unique transaction ID
        $transactionId = 'DEP_' . time() . '_' . $user->id . '_' . rand(1000, 9999);

        try {
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_method' => $method,
                'payment_provider' => $method === 'crypto' ? 'plisio' : 'binance',
                'status' => 'pending',
                'type' => 'deposit',
                'description' => "Account deposit via {$method}",
                'metadata' => [
                    'user_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);

            if ($method === 'crypto') {
                return $this->processPlisioPayment($payment);
            } else {
                return $this->processBinancePayment($payment);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create deposit request. Please try again.']);
        }
    }

    private function processPlisioPayment(Payment $payment)
    {
        $plisioApiKey = config('services.plisio.api_key', 'M_srKi_qKCQ1hra_J8Zx-khHGozvT2EkbfXq8ieKZvTfmpCOIKcTFHNchjMEC4_x');
        
        if (empty($plisioApiKey)) {
            return back()->withErrors(['error' => 'Cryptocurrency payments are temporarily unavailable.']);
        }

        try {
            // Plisio API endpoint for creating invoice
            $plisioUrl = 'https://plisio.net/api/v1/invoices/new';
            
            $params = [
                'source_amount' => $payment->amount,
                'source_currency' => 'USD',
                'order_number' => $payment->transaction_id,
                'order_name' => 'Starlink Router Rent Deposit',
                'description' => "Deposit to Starlink Router Rent account",
                'callback_url' => route('webhooks.plisio'),
                'success_callback_url' => route('deposits.success'),
                'fail_callback_url' => route('deposits.failed'),
                'email' => auth()->user()->email,
                'plugin' => 'laravel',
                'version' => '1.0',
                'api_key' => $plisioApiKey
            ];

            $response = $this->makeHttpRequest($plisioUrl, $params);

            if ($response && isset($response['status']) && $response['status'] === 'success') {
                // Update payment with Plisio data
                $payment->update([
                    'external_id' => $response['data']['txn_id'],
                    'provider_transaction_id' => $response['data']['txn_id'],
                    'provider_response' => $response,
                    'expires_at' => now()->addHours(24), // Plisio invoices expire in 24 hours
                ]);

                // Redirect to Plisio payment page
                return redirect($response['data']['invoice_url']);
            } else {
                throw new \Exception('Failed to create Plisio invoice: ' . ($response['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            \Log::error('Plisio payment error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to process cryptocurrency payment. Please try again.']);
        }
    }

    private function processBinancePayment(Payment $payment)
    {
        // For now, just create a pending payment
        // In a real implementation, you would integrate with Binance Pay API
        
        return redirect()->route('dashboard')->with('success', 
            'Binance Pay deposit request created! Please contact support to complete the payment process.');
    }

    private function makeHttpRequest($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Laravel/Starlink-Router-Rent'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }

    public function success(Request $request)
    {
        return redirect()->route('dashboard')->with('success', 
            'Payment completed successfully! Your deposit will be credited to your account shortly.');
    }

    public function failed(Request $request)
    {
        return redirect()->route('deposits.create')->with('error', 
            'Payment failed or was cancelled. Please try again.');
    }
}