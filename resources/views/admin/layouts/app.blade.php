<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .sidebar {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }
        .admin-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="bg-slate-900 text-white">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 flex-shrink-0 border-r border-blue-500/20">
            <div class="p-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                        <span class="text-white text-xl">🛰️</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                        <p class="text-gray-400 text-sm">Starlink Router Rent</p>
                    </div>
                </div>
            </div>

            <nav class="px-4 pb-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>👥</span>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.payments.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.payments.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>💳</span>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.withdrawals.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.withdrawals.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>💸</span>
                            <span>Withdrawals</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.devices.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.devices.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>🛰️</span>
                            <span>Devices</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.site-settings.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.site-settings.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>⚙️</span>
                            <span>Site Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.email-templates.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-500/20 transition-colors {{ request()->routeIs('admin.email-templates.*') ? 'bg-blue-500/30 text-cyan-400' : 'text-gray-300' }}">
                            <span>📧</span>
                            <span>Email Templates</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-slate-800/50 border-b border-blue-500/20 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h2>
                    
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-300">
                            Welcome, {{ auth('admin')->user()->username }}
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>