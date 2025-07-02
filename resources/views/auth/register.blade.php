@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="stat-card">
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-2xl">🛰️</span>
                </div>
                <h2 class="text-3xl font-bold text-white">Join Starlink Router Rent</h2>
                <p class="text-gray-300 mt-2">Create your account and start earning</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="username" class="block text-gray-300 mb-2">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required 
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('username') border-red-500 @enderror">
                    @error('username')
                        <span class="text-red-400 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-gray-300 mb-2">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('email') border-red-500 @enderror">
                    @error('email')
                        <span class="text-red-400 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <input id="password" type="password" name="password" required 
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('password') border-red-500 @enderror">
                    @error('password')
                        <span class="text-red-400 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-gray-300 mb-2">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required 
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label for="referral_code" class="block text-gray-300 mb-2">Referral Code (Optional)</label>
                    <input id="referral_code" type="text" name="referral_code" value="{{ $referralCode ?? old('referral_code') }}" 
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none @error('referral_code') border-red-500 @enderror">
                    <p class="text-gray-400 text-sm mt-1">Enter a referral code to earn bonus rewards</p>
                    @error('referral_code')
                        <span class="text-red-400 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="w-full btn-primary">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-300">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 ml-2 font-medium">
                        Sign in
                    </a>
                </p>
            </div>

            <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                <h4 class="text-white font-medium mb-2">🎁 Welcome Bonus</h4>
                <p class="text-gray-300 text-sm">
                    Sign up now and get a $10 welcome bonus plus access to our 3-level referral system!
                </p>
            </div>
        </div>
    </div>
</div>
@endsection