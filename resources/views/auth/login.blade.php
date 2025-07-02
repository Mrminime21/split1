@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="stat-card">
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <span class="text-white text-2xl">🛰️</span>
                </div>
                <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
                <p class="text-gray-300 mt-2">Sign in to your account</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-gray-300 mb-2">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-300">
                            Remember me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-cyan-400 hover:text-cyan-300" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="w-full btn-primary">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-300">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-cyan-400 hover:text-cyan-300 ml-2 font-medium">
                        Sign up
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection