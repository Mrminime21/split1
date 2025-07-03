@extends('admin.layouts.app')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="admin-card p-6 rounded-xl">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search users..." 
                       class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <select name="status" class="bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="admin-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-700/50">
                    <tr>
                        <th class="text-left p-4 text-gray-300">User</th>
                        <th class="text-left p-4 text-gray-300">Status</th>
                        <th class="text-left p-4 text-gray-300">Balance</th>
                        <th class="text-left p-4 text-gray-300">Total Earnings</th>
                        <th class="text-left p-4 text-gray-300">Rentals</th>
                        <th class="text-left p-4 text-gray-300">Joined</th>
                        <th class="text-left p-4 text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-700/30">
                        <td class="p-4">
                            <div>
                                <p class="text-white font-medium">{{ $user->username }}</p>
                                <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                                <p class="text-gray-500 text-xs">{{ $user->referral_code }}</p>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ 
                                $user->status === 'active' ? 'bg-green-500/20 text-green-400' : 
                                ($user->status === 'suspended' ? 'bg-red-500/20 text-red-400' : 
                                ($user->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-500/20 text-gray-400'))
                            }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="text-green-400 font-semibold">${{ number_format($user->balance, 2) }}</span>
                        </td>
                        <td class="p-4">
                            <span class="text-white">${{ number_format($user->total_earnings, 2) }}</span>
                        </td>
                        <td class="p-4">
                            <span class="text-white">{{ $user->rentals_count }}</span>
                        </td>
                        <td class="p-4">
                            <span class="text-gray-400">{{ $user->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="p-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-700">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection