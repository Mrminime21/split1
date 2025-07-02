@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->username }}!</h1>
            <p class="text-gray-300">Monitor your earnings and router performance</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Earnings</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            ${{ number_format(auth()->user()->total_earnings, 2) }}
                        </p>
                        <p class="text-green-400 text-sm mt-1">+12.5%</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <span class="text-white text-xl">💰</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Active Rentals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->rentals()->where('status', 'active')->count() }}</p>
                        <p class="text-green-400 text-sm mt-1">+2</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                        <span class="text-white text-xl">🛰️</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->referralRelationships()->count() }}</p>
                        <p class="text-green-400 text-sm mt-1">+5</p>
                    </div>
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                        <span class="text-white text-xl">👥</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Current Balance</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            ${{ number_format(auth()->user()->balance, 2) }}
                        </p>
                        <p class="text-green-400 text-sm mt-1">Available</p>
                    </div>
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                        <span class="text-white text-xl">💳</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 stat-card">
                <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <a href="{{ route('rentals.index') }}" class="btn-primary text-center">
                        🛰️ Rent New Router
                    </a>
                    <a href="{{ route('investments.index') }}" class="btn-primary text-center">
                        📈 Make Investment
                    </a>
                    <a href="{{ route('deposits.create') }}" class="bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all text-center">
                        💰 Deposit Funds
                    </a>
                    <a href="{{ route('withdrawals.create') }}" class="bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all text-center">
                        💸 Withdraw Earnings
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <h3 class="text-xl font-semibold text-white mb-4">Account Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Current Balance:</span>
                        <span class="text-green-400 font-semibold">${{ number_format(auth()->user()->balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Invested:</span>
                        <span class="text-white">${{ number_format(auth()->user()->total_invested, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Withdrawn:</span>
                        <span class="text-white">${{ number_format(auth()->user()->total_withdrawn, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Referral Code:</span>
                        <code class="text-cyan-400 font-mono">{{ auth()->user()->referral_code }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection