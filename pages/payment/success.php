<?php
/**
 * Payment Success Page
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

$title = 'Payment Successful';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full text-center">
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-green-500/20">
            <div class="bg-gradient-to-r from-green-500 to-blue-500 p-4 rounded-lg w-fit mx-auto mb-6">
                <i data-lucide="check-circle" class="h-12 w-12 text-white"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-white mb-4">Payment Successful!</h1>
            <p class="text-gray-300 mb-6">
                Your cryptocurrency payment has been received and is being processed.
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
                            <span class="text-green-400">Processing</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Order ID:</span>
                            <span class="text-white font-mono text-xs"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg mb-6">
                <p class="text-blue-400 text-sm">
                    <i data-lucide="clock" class="h-4 w-4 inline mr-1"></i>
                    Your funds will be credited to your account within 10-30 minutes after blockchain confirmation.
                </p>
            </div>

            <div class="space-y-3">
                <a href="/dashboard" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all block">
                    Go to Dashboard
                </a>
                <a href="/deposit" class="w-full bg-slate-700 text-white py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all block">
                    Make Another Deposit
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>