@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Withdraw Funds</h1>
            <p class="text-xl text-gray-300">
                Withdraw your earnings to your preferred payment method
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Withdrawal Form -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Request Withdrawal</h3>
                
                <form class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Amount ($)</label>
                        <input type="number" min="20" max="{{ auth()->user()->balance }}" step="1" 
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter amount (min $20)">
                        <p class="text-gray-400 text-sm mt-1">
                            Available: ${{ number_format(auth()->user()->balance, 2) }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Method</label>
                        <select class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                            <option value="">Select method</option>
                            <option value="crypto">Cryptocurrency - 2-6 hours</option>
                            <option value="binance">Binance Pay - 1-2 hours</option>
                            <option value="bank_transfer">Bank Transfer - 1-3 business days</option>
                            <option value="paypal">PayPal - 1-2 hours</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Address/Details</label>
                        <textarea rows="3" 
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                  placeholder="Enter your wallet address, bank details, or PayPal email"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea rows="2" 
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                  placeholder="Additional notes for the withdrawal"></textarea>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <h4 class="text-white font-semibold mb-3">Withdrawal Summary</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-300">Withdrawal Amount:</span>
                                <span class="text-white font-semibold">$0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-300">Processing Fee (2%):</span>
                                <span class="text-red-400">-$0.00</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-600 pt-2">
                                <span class="text-gray-300">Net Amount:</span>
                                <span class="text-green-400 font-semibold text-lg">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-yellow-400 text-xl">⚠️</span>
                            <span class="text-yellow-400 font-medium">Withdrawal Fees</span>
                        </div>
                        <p class="text-gray-300 text-sm">
                            A 2% fee (minimum $5) will be deducted from your withdrawal amount.
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
                        <li>• Crypto: 2-6 hours</li>
                        <li>• Binance Pay: 1-2 hours</li>
                        <li>• Bank Transfer: 1-3 business days</li>
                        <li>• PayPal: 1-2 hours</li>
                    </ul>
                </div>

                <div class="bg-green-500/10 border border-green-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-green-400 text-xl">🛡️</span>
                        <span class="text-green-400 font-medium">Security Notice</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        All withdrawals are manually reviewed for security. You'll receive email confirmation once processed.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection