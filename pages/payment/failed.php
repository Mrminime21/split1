<?php
/**
 * Payment Failed Page
 */

require_once '../../includes/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

$orderId = $_GET['order_id'] ?? '';
$payment = null;

if ($orderId) {
    $payment = $db->fetch("
        SELECT * FROM payments 
        WHERE transaction_id = ? AND user_id = ?
    ", [$orderId, $user['id']]);
}

$title = 'Payment Failed';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full text-center">
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-red-500/20">
            <div class="bg-gradient-to-r from-red-500 to-orange-500 p-4 rounded-lg w-fit mx-auto mb-6">
                <i data-lucide="x-circle" class="h-12 w-12 text-white"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-white mb-4">Payment Failed</h1>
            <p class="text-gray-300 mb-6">
                Unfortunately, your cryptocurrency payment could not be processed.
            </p>

            <?php if ($payment): ?>
                <div class="bg-slate-700/50 p-4 rounded-lg mb-6 text-left">
                    <h3 class="text-white font-semibold mb-3">Payment Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Amount:</span>
                            <span class="text-white">$<?php echo number_format($payment['amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Currency:</span>
                            <span class="text-white"><?php echo strtoupper($payment['crypto_currency'] ?? 'BTC'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="text-red-400">Failed</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Order ID:</span>
                            <span class="text-white font-mono text-xs"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-lg mb-6">
                <h4 class="text-yellow-400 font-medium mb-2">Common Issues:</h4>
                <ul class="text-yellow-300 text-sm text-left space-y-1">
                    <li>• Payment timeout or expiration</li>
                    <li>• Insufficient network fees</li>
                    <li>• Network congestion</li>
                    <li>• Incorrect payment amount</li>
                </ul>
            </div>

            <div class="space-y-3">
                <a href="/deposit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all block">
                    Try Again
                </a>
                <a href="/dashboard" class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all block">
                    Go to Dashboard
                </a>
                <a href="#" onclick="openSupport()" class="w-full text-cyan-400 py-3 rounded-lg font-medium hover:text-cyan-300 transition-all block">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = "
    function openSupport() {
        alert('Support contact: support@starlink-rent.com\\nPlease include your Order ID: " . htmlspecialchars($orderId) . "');
    }
";

echo renderLayout($title, $content, '', $additionalJS);
?>