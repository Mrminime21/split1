@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Deposit Funds</h1>
            <p class="text-xl text-gray-300">
                Add funds to your account using cryptocurrency via Plisio.net
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Deposit Form -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Make Deposit</h3>
                
                <form action="{{ route('deposits.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-gray-300 mb-2">Deposit Amount ($)</label>
                        <input type="number" name="amount" min="50" max="10000" step="10" 
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('amount') border-red-500 @enderror"
                               placeholder="Enter amount (min $50)" value="{{ old('amount') }}" required>
                        <p class="text-gray-400 text-sm mt-1">Minimum: $50 | Maximum: $10,000</p>
                        @error('amount')
                            <span class="text-red-400 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Payment Method</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="crypto" class="mr-3" checked required>
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 p-2 rounded-lg">
                                        <span class="text-white">₿</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-white font-medium">Cryptocurrency via Plisio.net</h4>
                                        <p class="text-gray-400 text-sm">Bitcoin, Ethereum, USDT, Litecoin, and 15+ more cryptocurrencies</p>
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
                        </div>
                    </div>

                    <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-blue-400 text-xl">ℹ️</span>
                            <span class="text-blue-400 font-medium">Cryptocurrency Information</span>
                        </div>
                        <ul class="text-gray-300 text-sm space-y-1">
                            <li>• Powered by Plisio.net - trusted crypto payment processor</li>
                            <li>• Supports Bitcoin, Ethereum, USDT, Litecoin, and 15+ cryptocurrencies</li>
                            <li>• Real-time exchange rates and instant confirmations</li>
                            <li>• Secure and encrypted transactions</li>
                        </ul>
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
                            <span class="text-gray-400">Binance Processing:</span>
                            <span class="text-white">1-2 hours</span>
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
                        <span class="text-orange-400 font-medium">Plisio.net Crypto Gateway</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• <strong>Bitcoin (BTC)</strong> - The original cryptocurrency</li>
                        <li>• <strong>Ethereum (ETH)</strong> - Smart contract platform</li>
                        <li>• <strong>Tether (USDT)</strong> - Stable coin pegged to USD</li>
                        <li>• <strong>Litecoin (LTC)</strong> - Fast and low-cost transactions</li>
                        <li>• <strong>15+ more cryptocurrencies</strong> supported</li>
                    </ul>
                </div>

                <div class="bg-green-500/10 border border-green-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-green-400 text-xl">🛡️</span>
                        <span class="text-green-400 font-medium">Security & Trust</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Bank-level encryption and security</li>
                        <li>• Real-time transaction monitoring</li>
                        <li>• Instant payment confirmations</li>
                        <li>• 24/7 fraud protection</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Supported Cryptocurrencies -->
        <div class="mt-12 stat-card">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Supported Cryptocurrencies</h3>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="bg-orange-500/20 p-4 rounded-lg mb-3">
                        <span class="text-orange-400 text-2xl">₿</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Bitcoin (BTC)</h4>
                    <p class="text-green-400 font-medium">5-30 minutes</p>
                </div>

                <div class="text-center">
                    <div class="bg-blue-500/20 p-4 rounded-lg mb-3">
                        <span class="text-blue-400 text-2xl">Ξ</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Ethereum (ETH)</h4>
                    <p class="text-green-400 font-medium">2-15 minutes</p>
                </div>

                <div class="text-center">
                    <div class="bg-green-500/20 p-4 rounded-lg mb-3">
                        <span class="text-green-400 text-2xl">₮</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Tether (USDT)</h4>
                    <p class="text-green-400 font-medium">2-15 minutes</p>
                </div>

                <div class="text-center">
                    <div class="bg-gray-400/20 p-4 rounded-lg mb-3">
                        <span class="text-gray-400 text-2xl">Ł</span>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Litecoin (LTC)</h4>
                    <p class="text-green-400 font-medium">5-20 minutes</p>
                </div>
            </div>

            <div class="text-center mt-6">
                <p class="text-gray-300">And 15+ more cryptocurrencies including USDC, BNB, ADA, DOT, and more!</p>
            </div>
        </div>
    </div>
</div>
@endsection