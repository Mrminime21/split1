@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Starlink Router Rental</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Choose your rental plan and start earning daily profits from premium Starlink satellite routers
            </p>
        </div>

        <!-- Router Specifications -->
        <div class="grid lg:grid-cols-4 gap-8 mb-12">
            <div class="stat-card text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-xl">🌐</span>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Global Coverage</h3>
                <p class="text-cyan-400 font-medium">99.9% Uptime</p>
            </div>

            <div class="stat-card text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-xl">⚡</span>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Speed</h3>
                <p class="text-cyan-400 font-medium">Up to 200 Mbps</p>
            </div>

            <div class="stat-card text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-xl">🛡️</span>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Security</h3>
                <p class="text-cyan-400 font-medium">Enterprise Grade</p>
            </div>

            <div class="stat-card text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-xl">📅</span>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Activation</h3>
                <p class="text-cyan-400 font-medium">Instant Setup</p>
            </div>
        </div>

        <!-- Rental Plans -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="stat-card">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Basic Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$2/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 5%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">1 Starlink Router</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Basic Support</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Standard Speeds</span>
                    </li>
                </ul>

                <button class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    Select Basic
                </button>
            </div>

            <div class="stat-card border-blue-500 scale-105 relative">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                        Most Popular
                    </span>
                </div>

                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Standard Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$5/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 8%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">3 Starlink Routers</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Priority Support</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">High-Speed Internet</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Device Monitoring</span>
                    </li>
                </ul>

                <button class="w-full btn-primary">
                    Select Standard
                </button>
            </div>

            <div class="stat-card">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Premium Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$10/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 12%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">6 Starlink Routers</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">24/7 VIP Support</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Ultra-High Speeds</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Advanced Analytics</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-green-400 mr-3">✓</span>
                        <span class="text-gray-300">Backup Routers</span>
                    </li>
                </ul>

                <button class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    Select Premium
                </button>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center stat-card">
            <span class="text-green-400 text-4xl">📈</span>
            <h3 class="text-2xl font-bold text-white mb-4 mt-4">Need More Funds?</h3>
            <p class="text-gray-300 mb-6">
                Deposit funds to start renting premium Starlink routers and earning daily profits
            </p>
            <a href="{{ route('deposits.create') }}" class="btn-primary inline-flex items-center">
                <span class="mr-2">💰</span>
                Deposit Funds
            </a>
        </div>
    </div>
</div>
@endsection