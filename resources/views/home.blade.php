@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative py-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-cyan-600/20 backdrop-blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto text-center">
            <div class="mb-8">
                <div class="inline-flex items-center space-x-4 bg-slate-800/50 px-6 py-3 rounded-full border border-blue-500/20">
                    <span class="text-blue-400 font-medium">Premium Router Rental Platform</span>
                </div>
            </div>

            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6">
                <span class="gradient-text">Star Router</span><br />
                <span class="gradient-text">Rent & Earn</span>
            </h1>

            <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
                Rent premium high-performance routers and earn guaranteed daily profits. 
                Join our referral network and build passive income with cutting-edge technology.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary">
                        Start Earning Today
                    </a>
                @endauth
                <a href="{{ route('rentals.index') }}" class="bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                    View Router Plans
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">{{ number_format($stats['uptime_percentage'], 1) }}%</div>
                    <div class="text-gray-400">Uptime Guarantee</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">15%</div>
                    <div class="text-gray-400">Max Referral Bonus</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">24/7</div>
                    <div class="text-gray-400">Support Available</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">15+</div>
                    <div class="text-gray-400">Cryptocurrencies</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Why Choose Star Router Rent?</h2>
                <p class="text-xl text-gray-300">Experience the future of premium router rental</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="stat-card">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mb-4">
                        <span class="text-white text-xl">🛰️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Premium Routers</h3>
                    <p class="text-gray-300">Rent high-performance routers with guaranteed uptime and global coverage.</p>
                </div>

                <div class="stat-card">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg w-fit mb-4">
                        <span class="text-white text-xl">💰</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Daily Profits</h3>
                    <p class="text-gray-300">Earn consistent daily returns from your router rentals with transparent profit sharing.</p>
                </div>

                <div class="stat-card">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg w-fit mb-4">
                        <span class="text-white text-xl">👥</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">3-Level Referrals</h3>
                    <p class="text-gray-300">Build your network and earn up to 15% commission from referral bonuses.</p>
                </div>

                <div class="stat-card">
                    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 p-3 rounded-lg w-fit mb-4">
                        <span class="text-white text-xl">₿</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Crypto Payments</h3>
                    <p class="text-gray-300">Secure payments via Plisio with full cryptocurrency support including Bitcoin, USDT, and more.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-800/30">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to Start Earning?</h2>
            <p class="text-xl text-gray-300 mb-8">
                Join thousands of users already earning daily profits with our premium router rental platform
            </p>
            
            @guest
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn-primary">
                        Create Account
                    </a>
                    <a href="{{ route('login') }}" class="bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                        Sign In
                    </a>
                </div>
            @else
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    Go to Dashboard
                </a>
            @endguest
        </div>
    </section>
</div>
@endsection