<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

$title = 'Home';

ob_start();
?>

<section class="relative py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto text-center">
        <div class="mb-8">
            <div class="inline-flex items-center space-x-4 bg-slate-800/50 px-6 py-3 rounded-full border border-blue-500/20">
                <i data-lucide="smartphone" class="h-8 w-8 text-blue-400"></i>
                <span class="text-blue-400 font-medium">Telegram Mini App</span>
            </div>
        </div>

        <h1 class="text-5xl md:text-7xl font-bold text-white mb-6">
            <span class="gradient-text">GainsMax</span><br>
            Test <span class="gradient-text">Telegram</span>
        </h1>

        <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
            The ultimate Telegram Mini App for cryptocurrency investments, device rentals, 
            and building your referral network with guaranteed daily profits.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/rental" class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:from-blue-600 hover:to-cyan-500 transition-all transform hover:scale-105">
                Start Earning
            </a>
            <a href="/investment" class="bg-slate-800 text-white px-8 py-4 rounded-lg font-semibold text-lg border border-blue-500/20 hover:bg-slate-700 transition-all transform hover:scale-105">
                View Plans
            </a>
        </div>
    </div>
</section>

<section class="py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-white mb-4">Why Choose GainsMax?</h2>
            <p class="text-xl text-gray-300">Experience the future of Telegram-based investing</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mb-4">
                    <i data-lucide="smartphone" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Telegram Native</h3>
                <p class="text-gray-300">Seamlessly integrated Mini App with native Telegram authentication and notifications.</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mb-4">
                    <i data-lucide="dollar-sign" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Daily Profits</h3>
                <p class="text-gray-300">Earn consistent daily returns from your investments with transparent profit sharing.</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mb-4">
                    <i data-lucide="users" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">3-Level Referrals</h3>
                <p class="text-gray-300">Build your network and earn up to 15% bonus from referral commissions.</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mb-4">
                    <i data-lucide="bitcoin" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Crypto Payments</h3>
                <p class="text-gray-300">Secure payments via Plisio with full cryptocurrency support including Bitcoin, USDT, and more.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-800/30">
    <div class="max-w-4xl mx-auto text-center">
        <div class="mb-8">
            <div class="inline-flex items-center space-x-4 bg-green-500/10 px-6 py-3 rounded-full border border-green-500/20">
                <i data-lucide="zap" class="h-6 w-6 text-green-400"></i>
                <span class="text-green-400 font-medium">Telegram Mini App Features</span>
            </div>
        </div>
        
        <h2 class="text-4xl font-bold text-white mb-6">Built for Telegram</h2>
        <p class="text-xl text-gray-300 mb-8">
            Experience seamless integration with Telegram's ecosystem
        </p>
        
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-green-500/10">
                <i data-lucide="shield-check" class="h-8 w-8 text-green-400 mx-auto mb-3"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Secure Login</h3>
                <p class="text-gray-300 text-sm">Automatic authentication via your Telegram account</p>
            </div>
            
            <div class="bg-slate-800/50 p-6 rounded-xl border border-green-500/10">
                <i data-lucide="bell" class="h-8 w-8 text-green-400 mx-auto mb-3"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Push Notifications</h3>
                <p class="text-gray-300 text-sm">Real-time updates on earnings and transactions</p>
            </div>
            
            <div class="bg-slate-800/50 p-6 rounded-xl border border-green-500/10">
                <i data-lucide="mobile" class="h-8 w-8 text-green-400 mx-auto mb-3"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Mobile First</h3>
                <p class="text-gray-300 text-sm">Optimized for mobile usage within Telegram</p>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/login" class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:from-blue-600 hover:to-cyan-500 transition-all">
                Get Started Now
            </a>
            <a href="/referrals" class="text-cyan-400 px-8 py-4 rounded-lg font-semibold text-lg border border-cyan-400/20 hover:bg-cyan-400/10 transition-all">
                Learn About Referrals
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>