@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Profile Settings</h1>
            <p class="text-gray-300">Manage your account information and preferences</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="stat-card text-center">
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-4 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                        <span class="text-white text-2xl">👤</span>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">{{ auth()->user()->username }}</h2>
                    <p class="text-gray-400 mb-4">{{ auth()->user()->email }}</p>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Status:</span>
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs">
                                {{ ucfirst(auth()->user()->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Member Since:</span>
                            <span class="text-white">
                                {{ auth()->user()->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Referral Code:</span>
                            <code class="text-cyan-400 font-mono">{{ auth()->user()->referral_code }}</code>
                        </div>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="stat-card mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Account Stats</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-green-400 text-xl">💳</span>
                                <span class="text-gray-400">Balance</span>
                            </div>
                            <span class="text-green-400 font-semibold">${{ number_format(auth()->user()->balance, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-blue-400 text-xl">📈</span>
                                <span class="text-gray-400">Total Earnings</span>
                            </div>
                            <span class="text-white">${{ number_format(auth()->user()->total_earnings, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-purple-400 text-xl">💰</span>
                                <span class="text-gray-400">Total Invested</span>
                            </div>
                            <span class="text-white">${{ number_format(auth()->user()->total_invested, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-white">Personal Information</h3>
                        <button class="flex items-center space-x-2 text-cyan-400 hover:text-cyan-300">
                            <span class="text-sm">✏️</span>
                            <span>Edit</span>
                        </button>
                    </div>

                    <form class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-300 mb-2">Username</label>
                                <div class="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                                    <span class="text-gray-400">👤</span>
                                    <span class="text-white">{{ auth()->user()->username }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">Email</label>
                                <div class="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                                    <span class="text-gray-400">📧</span>
                                    <span class="text-white">{{ auth()->user()->email }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">Phone</label>
                                <input type="tel" value="{{ auth()->user()->phone }}" 
                                       class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                       placeholder="Enter phone number">
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">Country</label>
                                <input type="text" value="{{ auth()->user()->country }}" 
                                       class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                       placeholder="Enter country">
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">Timezone</label>
                                <select class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                                    <option value="UTC" {{ auth()->user()->timezone == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ auth()->user()->timezone == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ auth()->user()->timezone == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ auth()->user()->timezone == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ auth()->user()->timezone == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ auth()->user()->timezone == 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ auth()->user()->timezone == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ auth()->user()->timezone == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">Language</label>
                                <div class="flex items-center space-x-2 p-3 bg-slate-700/50 rounded-lg">
                                    <span class="text-white">{{ auth()->user()->language ?? 'en' }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Security Section -->
                <div class="stat-card mt-6">
                    <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                        <span class="text-xl mr-2">🛡️</span>
                        Security
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                            <div>
                                <h4 class="text-white font-medium">Email Verification</h4>
                                <p class="text-gray-400 text-sm">Verify your email address for security</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm {{ auth()->user()->email_verified_at ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                {{ auth()->user()->email_verified_at ? 'Verified' : 'Pending' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                            <div>
                                <h4 class="text-white font-medium">Two-Factor Authentication</h4>
                                <p class="text-gray-400 text-sm">Add an extra layer of security</p>
                            </div>
                            <button class="text-cyan-400 hover:text-cyan-300 text-sm">
                                Enable 2FA
                            </button>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                            <div>
                                <h4 class="text-white font-medium">Change Password</h4>
                                <p class="text-gray-400 text-sm">Update your account password</p>
                            </div>
                            <button class="text-cyan-400 hover:text-cyan-300 text-sm">
                                Change
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection