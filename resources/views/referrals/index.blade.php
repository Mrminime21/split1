@extends('layouts.app')

@section('content')
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Referral System</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Build your network and earn up to 15% commission from 3 levels of referrals
            </p>
        </div>

        <!-- Referral Stats -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->referralRelationships()->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <span class="text-white text-xl">👥</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 1 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->referralRelationships()->where('level', 1)->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                        <span class="text-white text-xl">🔗</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 2 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->referralRelationships()->where('level', 2)->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                        <span class="text-white text-xl">🎁</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 3 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ auth()->user()->referralRelationships()->where('level', 3)->count() }}</p>
                    </div>
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                        <span class="text-white text-xl">📈</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Referral Code -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Your Referral Code</h3>
                
                <div class="bg-slate-700/50 p-4 rounded-lg mb-6">
                    <div class="flex items-center justify-between">
                        <code class="text-2xl font-mono text-cyan-400 font-bold">{{ auth()->user()->referral_code }}</code>
                        <button onclick="copyReferralCode()" class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all">
                            <span class="text-sm">📋</span>
                            <span id="copy-text">Copy</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-white">Share Your Link</h4>
                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <p class="text-gray-300 text-sm mb-2">Referral Link:</p>
                        <code class="text-cyan-400 text-sm break-all" id="referral-link">
                            {{ url('/register?ref=' . auth()->user()->referral_code) }}
                        </code>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button onclick="shareToTelegram()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-all">
                            Share on Telegram
                        </button>
                        <button onclick="shareToWhatsApp()" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-all">
                            Share on WhatsApp
                        </button>
                    </div>
                </div>
            </div>

            <!-- Commission Structure -->
            <div class="stat-card">
                <h3 class="text-2xl font-bold text-white mb-6">Commission Structure</h3>
                
                <div class="space-y-4">
                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 1</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text text-transparent">7%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Direct referrals from people you invite</p>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 2</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-green-500 to-blue-500 bg-clip-text text-transparent">5%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Referrals from your level 1 referrals</p>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 3</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent">3%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Referrals from your level 2 referrals</p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-lg border border-green-500/20">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-green-400 text-xl">💰</span>
                        <span class="text-white font-semibold">Total Possible Commission: 15%</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        Maximum earning potential when you have active referrals in all 3 levels
                    </p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-blue-500/10 to-cyan-400/10 p-8 rounded-2xl border border-blue-500/20">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-white mb-4">Boost Your Earnings</h3>
                <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
                    The more people you refer, the more you earn. Share your referral code with friends, 
                    family, and social networks to maximize your passive income potential.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="copyReferralCode()" class="btn-primary">
                        Copy Referral Code
                    </button>
                    <button onclick="shareToTelegram()" class="bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                        Share on Telegram
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const code = '{{ auth()->user()->referral_code }}';
    navigator.clipboard.writeText(code).then(function() {
        const copyText = document.getElementById('copy-text');
        copyText.textContent = 'Copied!';
        setTimeout(() => {
            copyText.textContent = 'Copy';
        }, 2000);
    });
}

function shareToTelegram() {
    const link = '{{ url("/register?ref=" . auth()->user()->referral_code) }}';
    const text = `Join Star Router Rent and start earning daily profits! Use my referral link: ${link}`;
    const url = `https://t.me/share/url?url=${encodeURIComponent(link)}&text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function shareToWhatsApp() {
    const link = '{{ url("/register?ref=" . auth()->user()->referral_code) }}';
    const text = `Join Star Router Rent and start earning daily profits! Use my referral link: ${link}`;
    const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}
</script>
@endsection