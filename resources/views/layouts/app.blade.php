<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Star Router Rent') }}</title>
    <link rel="icon" type="image/svg+xml" href="/starlink-icon.svg">
    <meta name="description" content="Rent premium routers with daily profits, referral system, and crypto payments">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Telegram WebApp -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #e2e8f0;
        }
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            color: white;
        }
        .stat-card {
            background: rgba(30, 41, 59, 0.5);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, 0.1);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <a href="{{ url('/') }}" class="flex items-center space-x-3">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                            <span class="text-white text-xl">🛰️</span>
                        </div>
                        <span class="text-2xl font-bold gradient-text">Star Router Rent</span>
                    </a>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex space-x-8">
                        <a href="{{ url('/') }}" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="{{ route('rentals.index') }}" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md text-sm font-medium">Rentals</a>
                            <a href="{{ route('investments.index') }}" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md text-sm font-medium">Investments</a>
                            <a href="{{ route('referrals.index') }}" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md text-sm font-medium">Referrals</a>
                        @endauth
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg">
                                <span class="text-green-400 text-sm font-semibold">
                                    ${{ number_format(auth()->user()->balance, 2) }}
                                </span>
                            </div>
                            
                            <div class="relative group">
                                <button class="flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                                    <span class="text-white text-sm">{{ auth()->user()->username }}</span>
                                </button>
                                
                                <div class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg border border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                    <div class="py-2">
                                        <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white">Profile</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-400 hover:bg-slate-700">Sign Out</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary">Sign In</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900/50 border-t border-blue-500/20 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                                <span class="text-white">🛰️</span>
                            </div>
                            <span class="text-xl font-bold gradient-text">Star Router Rent</span>
                        </div>
                        <p class="text-gray-400">Premium router rental platform with guaranteed daily profits and referral rewards.</p>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Services</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="{{ route('rentals.index') }}" class="hover:text-cyan-400">Router Rental</a></li>
                            <li><a href="{{ route('investments.index') }}" class="hover:text-cyan-400">Investment Plans</a></li>
                            <li><a href="{{ route('referrals.index') }}" class="hover:text-cyan-400">Referral Program</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Account</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="{{ route('deposits.create') }}" class="hover:text-cyan-400">Deposit Funds</a></li>
                            <li><a href="{{ route('withdrawals.create') }}" class="hover:text-cyan-400">Withdraw Funds</a></li>
                            <li><a href="{{ route('dashboard') }}" class="hover:text-cyan-400">Dashboard</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Support</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-cyan-400">Help Center</a></li>
                            <li><a href="#" class="hover:text-cyan-400">Contact Us</a></li>
                            <li><a href="#" class="hover:text-cyan-400">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} Star Router Rent. All rights reserved. | Built with Laravel</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>