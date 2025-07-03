@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-card p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Users</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-green-400 text-sm mt-1">{{ number_format($stats['active_users']) }} active</p>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                    <span class="text-white text-xl">👥</span>
                </div>
            </div>
        </div>

        <div class="admin-card p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Revenue</p>
                    <p class="text-2xl font-bold text-white mt-1">${{ number_format($stats['total_revenue'], 2) }}</p>
                    <p class="text-green-400 text-sm mt-1">${{ number_format($stats['monthly_revenue'], 2) }} this month</p>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                    <span class="text-white text-xl">💰</span>
                </div>
            </div>
        </div>

        <div class="admin-card p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Active Devices</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['available_devices']) }}</p>
                    <p class="text-gray-400 text-sm mt-1">of {{ number_format($stats['total_devices']) }} total</p>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                    <span class="text-white text-xl">🛰️</span>
                </div>
            </div>
        </div>

        <div class="admin-card p-6 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending Withdrawals</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['pending_withdrawals']) }}</p>
                    <p class="text-yellow-400 text-sm mt-1">Needs attention</p>
                </div>
                <div class="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                    <span class="text-white text-xl">💸</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="admin-card p-6 rounded-xl">
            <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition-colors">
                    <span class="text-white">Manage Users</span>
                    <span class="text-cyan-400">→</span>
                </a>
                <a href="{{ route('admin.payments.index') }}" class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition-colors">
                    <span class="text-white">Review Payments</span>
                    <span class="text-cyan-400">→</span>
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition-colors">
                    <span class="text-white">Process Withdrawals</span>
                    <span class="text-cyan-400">→</span>
                </a>
                <a href="{{ route('admin.devices.create') }}" class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg hover:bg-slate-700 transition-colors">
                    <span class="text-white">Add New Device</span>
                    <span class="text-cyan-400">→</span>
                </a>
            </div>
        </div>

        <div class="admin-card p-6 rounded-xl">
            <h3 class="text-xl font-semibold text-white mb-4">Recent Users</h3>
            <div class="space-y-3">
                @foreach($recent_users as $user)
                <div class="flex items-center justify-between p-3 bg-slate-700/30 rounded-lg">
                    <div>
                        <p class="text-white font-medium">{{ $user->username }}</p>
                        <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="admin-card p-6 rounded-xl">
            <h3 class="text-xl font-semibold text-white mb-4">Pending Withdrawals</h3>
            <div class="space-y-3">
                @forelse($pending_withdrawals as $withdrawal)
                <div class="p-3 bg-slate-700/30 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-white font-medium">{{ $withdrawal->user->username }}</p>
                        <span class="text-green-400 font-semibold">${{ number_format($withdrawal->amount, 2) }}</span>
                    </div>
                    <p class="text-gray-400 text-sm">{{ $withdrawal->withdrawal_method }}</p>
                    <p class="text-gray-400 text-xs">{{ $withdrawal->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <p class="text-gray-400 text-center py-4">No pending withdrawals</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="admin-card p-6 rounded-xl">
        <h3 class="text-xl font-semibold text-white mb-4">Revenue Overview</h3>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
                <p class="text-gray-400 text-sm">Today</p>
                <p class="text-2xl font-bold text-green-400">${{ number_format($stats['daily_revenue'], 2) }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-400 text-sm">This Month</p>
                <p class="text-2xl font-bold text-blue-400">${{ number_format($stats['monthly_revenue'], 2) }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-400 text-sm">Total</p>
                <p class="text-2xl font-bold text-purple-400">${{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection