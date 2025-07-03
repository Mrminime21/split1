@extends('admin.layouts.app')

@section('title', 'Withdrawals Management')
@section('page-title', 'Withdrawals Management')

@section('content')
<div class="space-y-6">
    <!-- Auto Approval Settings -->
    <div class="admin-card p-6 rounded-xl">
        <h3 class="text-lg font-semibold text-white mb-4">Auto Approval Settings</h3>
        <form method="POST" action="{{ route('admin.withdrawals.auto-approve') }}" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div>
                <label class="block text-gray-300 mb-2">Max Auto-Approve Amount ($)</label>
                <input type="number" name="max_amount" value="{{ old('max_amount', 100) }}" step="0.01" min="0"
                       class="bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-300 mb-2">Enable Auto-Approval</label>
                <select name="enabled" class="bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                    <option value="0">Disabled</option>
                    <option value="1">Enabled</option>
                </select>
            </div>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-colors">
                Update Settings
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="admin-card p-6 rounded-xl">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by username or email..." 
                       class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <select name="status" class="bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <select name="withdrawal_method" class="bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                    <option value="">All Methods</option>
                    <option value="crypto" {{ request('withdrawal_method') === 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                    <option value="binance" {{ request('withdrawal_method') === 'binance' ? 'selected' : '' }}>Binance Pay</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="admin-card p-6 rounded-xl">
        <form method="POST" action="{{ route('admin.withdrawals.bulk-approve') }}" id="bulk-form">
            @csrf
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="select-all" class="text-gray-300">Select All Pending</label>
                </div>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-colors">
                    Bulk Approve Selected
                </button>
            </div>
        </form>
    </div>

    <!-- Withdrawals Table -->
    <div class="admin-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-700/50">
                    <tr>
                        <th class="text-left p-4 text-gray-300">
                            <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </th>
                        <th class="text-left p-4 text-gray-300">User</th>
                        <th class="text-left p-4 text-gray-300">Amount</th>
                        <th class="text-left p-4 text-gray-300">Method</th>
                        <th class="text-left p-4 text-gray-300">Status</th>
                        <th class="text-left p-4 text-gray-300">Requested</th>
                        <th class="text-left p-4 text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($withdrawals as $withdrawal)
                    <tr class="hover:bg-slate-700/30">
                        <td class="p-4">
                            @if($withdrawal->status === 'pending')
                            <input type="checkbox" name="withdrawal_ids[]" value="{{ $withdrawal->id }}" 
                                   class="withdrawal-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   form="bulk-form">
                            @endif
                        </td>
                        <td class="p-4">
                            <div>
                                <p class="text-white font-medium">{{ $withdrawal->user->username }}</p>
                                <p class="text-gray-400 text-sm">{{ $withdrawal->user->email }}</p>
                            </div>
                        </td>
                        <td class="p-4">
                            <div>
                                <p class="text-white font-semibold">${{ number_format($withdrawal->amount, 2) }}</p>
                                <p class="text-gray-400 text-sm">Net: ${{ number_format($withdrawal->net_amount, 2) }}</p>
                                <p class="text-gray-500 text-xs">Fee: ${{ number_format($withdrawal->fee_amount, 2) }}</p>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="text-white">{{ ucfirst($withdrawal->withdrawal_method) }}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ 
                                $withdrawal->status === 'completed' ? 'bg-green-500/20 text-green-400' : 
                                ($withdrawal->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 
                                ($withdrawal->status === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400'))
                            }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="text-gray-400">{{ $withdrawal->created_at->format('M d, Y H:i') }}</span>
                        </td>
                        <td class="p-4">
                            <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-700">
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endsection