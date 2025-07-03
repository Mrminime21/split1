@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Withdraw Funds</h1>
            <p class="text-xl text-gray-300">
                Withdraw your earnings using cryptocurrency or Binance Pay
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Withdrawal Form -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Request Withdrawal</h3>
                
                <form action="{{ route('withdrawals.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Amount ($)</label>
                        <input type="number" name="amount" min="20" max="{{ auth()->user()->balance }}" step="1" 
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('amount') border-red-500 @enderror"
                               placeholder="Enter amount (min $20)" value="{{ old('amount') }}" required>
                        <p class="text-gray-400 text-sm mt-1">
                            Available: ${{ number_format(auth()->user()->balance, 2) }}
                        </p>
                        @error('amount')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Method</label>
                        <select name="method" class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('method') border-red-500 @enderror" required>
                            <option value="">Select method</option>
                            <option value="crypto" {{ old('method') == 'crypto' ? 'selected' : '' }}>Cryptocurrency - 2-6 hours</option>
                            <option value="binance" {{ old('method') == 'binance' ? 'selected' : '' }}>Binance Pay - 1-2 hours</option>
                        </select>
                        @error('method')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Address/Details</label>
                        <textarea name="address" rows="3" 
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('address') border-red-500 @enderror"
                                  placeholder="Enter your crypto wallet address or Binance account details" required>{{ old('address') }}</textarea>
                        <p class="text-gray-400 text-sm mt-1">
                            For crypto: Enter your wallet address (BTC, ETH, USDT, etc.)<br>
                            For Binance: Enter your Binance account email or user ID
                        </p>
                        @error('address')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="2" 
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                  placeholder="Additional notes for the withdrawal">{{ old('notes') }}</textarea>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg" id="withdrawal-summary">
                        <h4 class="text-white font-semibold mb-3">Withdrawal Summary</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Withdrawal Amount:</span>
                                <span class="text-white font-semibold" id="withdrawal-amount">$0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Processing Fee (2%):</span>
                                <span class="text-red-400" id="processing-fee">-$0.00</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-600 pt-2">
                                <span class="text-gray-300">Net Amount:</span>
                                <span class="text-green-400 font-semibold text-lg" id="net-amount">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-yellow-400 text-xl">⚠️</span>
                            <span class="text-yellow-400 font-medium">Withdrawal Fees</span>
                        </div>
                        <p class="text-gray-300 text-sm">
                            A 2% fee (minimum $5) will be deducted from your withdrawal amount for processing costs.
                        </p>
                    </div>

                    <button type="submit" class="w-full btn-primary">
                        Submit Withdrawal Request
                    </button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="space-y-6">
                <div class="stat-card">
                    <h4 class="text-lg font-semibold text-white mb-4">Account Summary</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Available Balance:</span>
                            <span class="text-green-400 font-semibold">${{ number_format(auth()->user()->balance, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Earnings:</span>
                            <span class="text-white">${{ number_format(auth()->user()->total_earnings, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Withdrawn:</span>
                            <span class="text-white">${{ number_format(auth()->user()->total_withdrawn, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h4 class="text-lg font-semibold text-white mb-4">Withdrawal Information</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Minimum Withdrawal:</span>
                            <span class="text-white">$20</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Processing Time:</span>
                            <span class="text-white">24-48 hours</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Withdrawal Fee:</span>
                            <span class="text-white">2% (min $5)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Daily Limit:</span>
                            <span class="text-white">$5,000</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-blue-400 text-xl">⏰</span>
                        <span class="text-blue-400 font-medium">Processing Times</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• <strong>Cryptocurrency:</strong> 2-6 hours</li>
                        <li>• <strong>Binance Pay:</strong> 1-2 hours</li>
                    </ul>
                </div>

                <div class="bg-orange-500/10 border border-orange-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-orange-400 text-xl">₿</span>
                        <span class="text-orange-400 font-medium">Supported Cryptocurrencies</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Bitcoin (BTC), Ethereum (ETH), Tether (USDT)</li>
                        <li>• Litecoin (LTC), Bitcoin Cash (BCH), USDC</li>
                        <li>• And 15+ more cryptocurrencies via Plisio.net</li>
                    </ul>
                </div>

                <div class="bg-green-500/10 border border-green-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-green-400 text-xl">🛡️</span>
                        <span class="text-green-400 font-medium">Security Notice</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        All withdrawals are manually reviewed for security. You'll receive email confirmation once processed. Never share your account details with anyone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    const withdrawalAmountSpan = document.getElementById('withdrawal-amount');
    const processingFeeSpan = document.getElementById('processing-fee');
    const netAmountSpan = document.getElementById('net-amount');

    function updateSummary() {
        const amount = parseFloat(amountInput.value) || 0;
        const fee = Math.max(amount * 0.02, 5); // 2% fee, minimum $5
        const netAmount = Math.max(amount - fee, 0);

        withdrawalAmountSpan.textContent = '$' + amount.toFixed(2);
        processingFeeSpan.textContent = '-$' + fee.toFixed(2);
        netAmountSpan.textContent = '$' + netAmount.toFixed(2);
    }

    amountInput.addEventListener('input', updateSummary);
    updateSummary(); // Initial calculation
});
</script>
@endsection