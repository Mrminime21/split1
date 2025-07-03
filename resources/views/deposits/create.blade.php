@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Deposit Funds</h1>
            <p class="text-xl text-gray-300">
                Add funds to your account to start investing and renting routers
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Deposit Form -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Make Deposit</h3>
                
                <form class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Deposit Amount ($)</label>
                        <input type="number" min="50" max="10000" step="10" 
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter amount (min $50)">
                        <p class="text-gray-400 text-sm mt-1">Minimum: $50 | Maximum: $10,000</p>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Payment Method</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="crypto" class="mr-3">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 p-2 rounded-lg">
                                        <span class="text-white">₿</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">Cryptocurrency</h4>
                                        <p class="text-gray-400 text-sm">Bitcoin, Ethereum, USDT and more via Plisio.net</p>
                                        <div class="flex space-x-2 mt-1">
                                            <span class="text-green-400 text-xs">✓ Instant processing</span>
                                            <span class="text-green-400 text-xs">✓ Low fees</span>
                                            <span class="text-green-400 text-xs">✓ Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="binance" class="mr-3">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-2 rounded-lg">
                                        <span class="text-white">💳</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">Binance Pay</h4>
                                        <p class="text-gray-400 text-sm">Direct from Binance account</p>
                                        <div class="flex space-x-2 mt-1">
                                            <span class="text-green-400 text-xs">✓ Fast transfer</span>
                                            <span class="text-green-400 text-xs">✓ Low fees</span>
                                            <span class="text-green-400 text-xs">✓ Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="card" class="mr-3">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                                        <span class="text-white">💳</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">Credit/Debit Card</h4>
                                        <p class="text-gray-400 text-sm">Visa, Mastercard, American Express</p>
                                        <div class="flex space-x-2 mt-1">
                                            <span class="text-green-400 text-xs">✓ Instant</span>
                                            <span class="text-green-400 text-xs">✓ Widely accepted</span>
                                            <span class="text-green-400 text-xs">✓ Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="bank_transfer" class="mr-3">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-2 rounded-lg">
                                        <span class="text-white">🏦</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">Bank Transfer</h4>
                                        <p class="text-gray-400 text-sm">Direct bank wire transfer</p>
                                        <div class="flex space-x-2 mt-1">
                                            <span class="text-green-400 text-xs">✓ Large amounts</span>
                                            <span class="text-green-400 text-xs">✓ Secure</span>
                                            <span class="text-green-400 text-xs">✓ Reliable</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full btn-primary">
                        Create Deposit Request
                    </button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="space-y-6">
                <div class="stat-card">
                    <h4 class="text-lg font-semibold text-white mb-4">Account Balance</h4>
                    <div class="text-3xl font-bold text-green-400 mb-2">
                        ${{ number_format(auth()->user()->balance, 2) }}
                    </div>
                    <p class="text-gray-400">Available for investment and rentals</p>
                </div>

                <div class="stat-card">
                    <h4 class="text-lg font-semibold text-white mb-4">Deposit Information</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Minimum Deposit:</span>
                            <span class="text-white">$50</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Maximum Deposit:</span>
                            <span class="text-white">$10,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Crypto Processing:</span>
                            <span class="text-white">5-30 minutes</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Other Methods:</span>
                            <span class="text-white">1-3 business days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Deposit Fee:</span>
                            <span class="text-green-400">Free</span>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-500/10 border border-orange-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-orange-400 text-xl">₿</span>
                        <span class="text-orange-400 font-medium">Crypto Payments via Plisio.net</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Instant processing and confirmation</li>
                        <li>• Support for 15+ cryptocurrencies</li>
                        <li>• Real-time exchange rates</li>
                        <li>• Secure and encrypted transactions</li>
                    </ul>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-blue-400 text-xl">🛡️</span>
                        <span class="text-blue-400 font-medium">Secure Deposits</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        All deposits are processed through secure payment gateways with bank-level encryption and fraud protection.
                    </p>
                </div>
            </div>
        </div>

        <!-- Processing Times -->
        <div class="mt-12 stat-card">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Processing Times</h3>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="bg-orange-500/20 p-4 rounded-lg mb-3">
                        <span class="text-orange-400 text-2xl">₿</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Cryptocurrency</h4>
                    <p class="text-green-400 font-medium">5-30 minutes</p>
                </div>

                <div class="text-center">
                    <div class="bg-yellow-500/20 p-4 rounded-lg mb-3">
                        <span class="text-yellow-400 text-2xl">💳</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Binance Pay</h4>
                    <p class="text-green-400 font-medium">1-2 hours</p>
                </div>

                <div class="text-center">
                    <div class="bg-blue-500/20 p-4 rounded-lg mb-3">
                        <span class="text-blue-400 text-2xl">💳</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Credit Card</h4>
                    <p class="text-yellow-400 font-medium">Instant</p>
                </div>

                <div class="text-center">
                    <div class="bg-green-500/20 p-4 rounded-lg mb-3">
                        <span class="text-green-400 text-2xl">🏦</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Bank Transfer</h4>
                    <p class="text-yellow-400 font-medium">1-3 days</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection