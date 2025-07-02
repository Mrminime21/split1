<?php
require_once 'includes/layout.php';

$title = '404 - Page Not Found';

ob_start();
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-4 rounded-lg w-fit mx-auto mb-6">
                <i data-lucide="satellite" class="h-12 w-12 text-white"></i>
            </div>
            <h1 class="text-6xl font-bold text-white mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-white mb-4">Page Not Found</h2>
            <p class="text-gray-300 mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <a href="/" class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                Go Home
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>