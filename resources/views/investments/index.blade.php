@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Investment Plans</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Invest in our Starlink router network and earn guaranteed daily profits with transparent returns
            </p>
        </div>

        <!-- Investment Plans -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="stat-card">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">3 Months Plan</h3>
                    <div class="text-3xl font-bold text-green-400 mb-2">24%</div>
                    <p class="text-gray-300 text-sm">Short-term investment with steady returns</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">0.27% Daily Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">24% Total Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">$500 Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Guaranteed Profits</span>
                    </li>
                </ul>

                <button class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    Select Plan
                </button>
            </div>

            <div class="stat-card border-blue-500 scale-105 relative">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                        Best Value
                    </span>
                </div>

                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">6 Months Plan</h3>
                    <div class="text-3xl font-bold text-green-400 mb-2">72%</div>
                    <p class="text-gray-300 text-sm">Balanced investment for optimal growth</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">0.40% Daily Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">72% Total Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">$1,000 Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Premium Support</span>
                    </li>
                </ul>

                <button class="w-full btn-primary">
                    Select Plan
                </button>
            </div>

            <div class="stat-card">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">12 Months Plan</h3>
                    <div class="text-3xl font-bold text-green-400 mb-2">216%</div>
                    <p class="text-gray-300 text-sm">Long-term investment for maximum returns</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">0.60% Daily Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">216% Total Return</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">$2,000 Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">VIP Benefits</span>
                    </li>
                </ul>

                <button class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    Select Plan
                </button>
            </div>
        </div>

        <!-- Investment Benefits -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div class="stat-card">
                <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                    <span class="text-green-400 mr-2">💰</span>
                    Guaranteed Returns
                </h3>
                <ul class="space-y-2 text-gray-300">
                    <li>• Fixed daily profit rates</li>
                    <li>• Automatic profit distribution</li>
                    <li>• No hidden fees or charges</li>
                    <li>• Transparent profit calculation</li>
                </ul>
            </div>

            <div class="stat-card">
                <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                    <span class="text-blue-400 mr-2">📊</span>
                    Investment Features
                </h3>
                <ul class="space-y-2 text-gray-300">
                    <li>• Real-time profit tracking</li>
                    <li>• Compound growth options</li>
                    <li>• Flexible withdrawal terms</li>
                    <li>• 24/7 customer support</li>
                </ul>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center stat-card">
            <span class="text-purple-400 text-4xl">📅</span>
            <h3 class="text-2xl font-bold text-white mb-4 mt-4">Need More Funds?</h3>
            <p class="text-gray-300 mb-6">
                Deposit funds to start investing in our premium plans and earning guaranteed daily returns
            </p>
            <a href="{{ route('deposits.create') }}" class="btn-primary inline-flex items-center">
                <span class="mr-2">💰</span>
                Deposit Funds
            </a>
        </div>
    </div>
</div>
@endsection