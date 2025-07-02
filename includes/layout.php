<?php
/**
 * Main Layout Template for GainsMax Test Telegram
 */

function renderLayout($title, $content, $additionalCSS = '', $additionalJS = '') {
    $auth = new Auth();
    $user = $auth->getCurrentUser();
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - GainsMax Test Telegram</title>
        <meta name="description" content="Telegram Mini App for cryptocurrency investments, device rentals, and referral earnings">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <style>
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
                min-height: 100vh;
            }
            .gradient-text {
                background: linear-gradient(135deg, #60a5fa, #06b6d4);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }
            <?php echo $additionalCSS; ?>
        </style>
    </head>
    <body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 text-white">
        <!-- Header -->
        <header class="bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="/" class="flex items-center space-x-3">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                            <i data-lucide="trending-up" class="h-8 w-8 text-white"></i>
                        </div>
                        <span class="text-2xl font-bold gradient-text">GainsMax</span>
                    </a>

                    <nav class="hidden md:flex space-x-8">
                        <a href="/" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5 transition-colors">Home</a>
                        <a href="/rental" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5 transition-colors">Rental</a>
                        <a href="/investment" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5 transition-colors">Investment</a>
                        <a href="/referrals" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5 transition-colors">Referrals</a>
                        <?php if ($user): ?>
                            <a href="/dashboard" class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:text-cyan-400 hover:bg-cyan-400/5 transition-colors">Dashboard</a>
                        <?php endif; ?>
                    </nav>

                    <div class="flex items-center space-x-4">
                        <?php if ($user): ?>
                            <div class="flex items-center space-x-3">
                                <!-- Balance Display -->
                                <div class="flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg">
                                    <i data-lucide="wallet" class="h-4 w-4 text-green-400"></i>
                                    <span class="text-green-400 text-sm font-semibold">$<?php echo number_format($user['balance'], 2); ?></span>
                                </div>
                                
                                <!-- User Menu -->
                                <div class="relative group">
                                    <button class="flex items-center space-x-2 bg-slate-800 px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors">
                                        <i data-lucide="user" class="h-4 w-4 text-cyan-400"></i>
                                        <span class="text-white text-sm"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg border border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                        <div class="py-2">
                                            <a href="/dashboard" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white">
                                                <i data-lucide="layout-dashboard" class="h-4 w-4 mr-3"></i>
                                                Dashboard
                                            </a>
                                            <a href="/deposit" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white">
                                                <i data-lucide="plus-circle" class="h-4 w-4 mr-3"></i>
                                                Deposit
                                            </a>
                                            <a href="/withdrawal" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white">
                                                <i data-lucide="minus-circle" class="h-4 w-4 mr-3"></i>
                                                Withdraw
                                            </a>
                                            <div class="border-t border-gray-700 my-2"></div>
                                            <a href="/logout" class="flex items-center px-4 py-2 text-sm text-red-400 hover:bg-slate-700">
                                                <i data-lucide="log-out" class="h-4 w-4 mr-3"></i>
                                                Logout
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="/login" class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-2 rounded-lg font-medium hover:from-blue-600 hover:to-cyan-500 transition-all">
                                Login
                            </a>
                        <?php endif; ?>

                        <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white">
                            <i data-lucide="menu" class="h-6 w-6"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div id="mobile-menu" class="md:hidden hidden pb-4">
                    <div class="flex flex-col space-y-2">
                        <a href="/" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Home</a>
                        <a href="/rental" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Rental</a>
                        <a href="/investment" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Investment</a>
                        <a href="/referrals" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Referrals</a>
                        <?php if ($user): ?>
                            <a href="/dashboard" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Dashboard</a>
                            <a href="/deposit" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Deposit</a>
                            <a href="/withdrawal" class="text-gray-300 hover:text-cyan-400 px-3 py-2 rounded-md">Withdraw</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <?php echo $content; ?>
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900/50 border-t border-blue-500/20 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                                <i data-lucide="trending-up" class="h-6 w-6 text-white"></i>
                            </div>
                            <span class="text-xl font-bold gradient-text">GainsMax</span>
                        </div>
                        <p class="text-gray-400">Telegram Mini App for cryptocurrency investments, device rentals, and referral earnings.</p>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Services</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="/rental" class="hover:text-cyan-400">Device Rental</a></li>
                            <li><a href="/investment" class="hover:text-cyan-400">Investment Plans</a></li>
                            <li><a href="/referrals" class="hover:text-cyan-400">Referral Program</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Account</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="/deposit" class="hover:text-cyan-400">Deposit Funds</a></li>
                            <li><a href="/withdrawal" class="hover:text-cyan-400">Withdraw Funds</a></li>
                            <li><a href="/dashboard" class="hover:text-cyan-400">Dashboard</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold mb-4">Support</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-cyan-400">Help Center</a></li>
                            <li><a href="#" class="hover:text-cyan-400">Contact Us</a></li>
                            <li><a href="/admin" class="hover:text-cyan-400">Admin Panel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2024 GainsMax Test Telegram. All rights reserved. | Built with PHP & MySQL</p>
                </div>
            </div>
        </footer>

        <script>
            // Initialize Lucide icons
            lucide.createIcons();

            // Mobile menu toggle
            document.getElementById('mobile-menu-btn').addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });

            // Telegram WebApp initialization
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                tg.ready();
                tg.expand();
                tg.setHeaderColor('#0F172A');
                tg.setBackgroundColor('#0F172A');
                
                // Handle Telegram theme changes
                tg.onEvent('themeChanged', function() {
                    document.body.style.backgroundColor = tg.backgroundColor;
                });
                
                // Handle viewport changes
                tg.onEvent('viewportChanged', function() {
                    if (tg.isExpanded) {
                        document.body.style.paddingBottom = '0px';
                    }
                });
            }

            <?php echo $additionalJS; ?>
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>