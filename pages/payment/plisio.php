<?php
/**
 * Plisio Payment Processing Page
 * Handles cryptocurrency payment creation and processing
 */

require_once '../../includes/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/plisio.php';
require_once '../../includes/layout.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();
$error = '';
$success = '';
$paymentUrl = '';

// Get payment details from session or URL
$amount = floatval($_GET['amount'] ?? $_SESSION['payment_amount'] ?? 0);
$type = $_GET['type'] ?? $_SESSION['payment_type'] ?? 'deposit';
$orderId = $_GET['order_id'] ?? $_SESSION['payment_order_id'] ?? '';

if (empty($orderId) || $amount <= 0) {
    header('Location: /deposit');
    exit;
}

// Handle payment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selectedCurrency = $_POST['currency'] ?? 'BTC';
        
        // Initialize Plisio
        $plisio = new PlisioPayment();
        
        // Create payment record
        $paymentData = [
            'user_id' => $user['id'],
            'transaction_id' => $orderId,
            'amount' => $amount,
            'currency' => 'USD',
            'crypto_currency' => $selectedCurrency,
            'payment_method' => 'crypto',
            'payment_provider' => 'plisio',
            'status' => 'pending',
            'type' => $type,
            'description' => ucfirst($type) . ' via Plisio (' . $selectedCurrency . ')',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $paymentId = $db->insert('payments', $paymentData);
        
        // Create Plisio invoice
        $invoiceParams = [
            'amount' => $amount,
            'currency' => 'USD',
            'currency_to' => $selectedCurrency,
            'order_id' => $orderId,
            'description' => 'Starlink Rent ' . ucfirst($type) . ' - $' . number_format($amount, 2),
            'email' => $user['email'],
            'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/plisio/webhook.php',
            'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment/success?order_id=' . $orderId,
            'fail_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment/failed?order_id=' . $orderId
        ];
        
        $invoice = $plisio->createInvoice($invoiceParams);
        
        if (isset($invoice['data']['invoice_url'])) {
            $paymentUrl = $invoice['data']['invoice_url'];
            
            // Update payment with Plisio data
            $db->update('payments', [
                'external_id' => $invoice['data']['id'] ?? '',
                'provider_response' => json_encode($invoice),
                'crypto_amount' => $invoice['data']['amount'] ?? 0,
                'exchange_rate' => $invoice['data']['rate'] ?? 0
            ], 'id = ?', [$paymentId]);
            
            // Clear session data
            unset($_SESSION['payment_amount'], $_SESSION['payment_type'], $_SESSION['payment_order_id']);
            
            // Redirect to Plisio payment page
            header('Location: ' . $paymentUrl);
            exit;
        } else {
            throw new Exception('Failed to create payment invoice');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get supported currencies
try {
    $plisio = new PlisioPayment();
    $currencies = $plisio->getPopularCurrencies();
} catch (Exception $e) {
    $currencies = [
        'BTC' => ['name' => 'Bitcoin', 'icon' => '₿'],
        'ETH' => ['name' => 'Ethereum', 'icon' => 'Ξ'],
        'USDT' => ['name' => 'Tether', 'icon' => '₮']
    ];
}

$title = 'Cryptocurrency Payment';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <div class="bg-gradient-to-r from-orange-500 to-yellow-500 p-4 rounded-lg w-fit mx-auto mb-4">
                <i data-lucide="bitcoin" class="h-12 w-12 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Cryptocurrency Payment</h1>
            <p class="text-gray-300">Secure payment via Plisio.net</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-white mb-4">Payment Details</h3>
                <div class="bg-slate-700/50 p-4 rounded-lg space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Amount:</span>
                        <span class="text-white font-semibold">$<?php echo number_format($amount, 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Type:</span>
                        <span class="text-white"><?php echo ucfirst($type); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Order ID:</span>
                        <span class="text-white font-mono text-sm"><?php echo htmlspecialchars($orderId); ?></span>
                    </div>
                </div>
            </div>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-4">Select Cryptocurrency</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php foreach ($currencies as $code => $info): ?>
                            <label class="relative">
                                <input type="radio" name="currency" value="<?php echo $code; ?>" 
                                       class="sr-only peer" <?php echo $code === 'BTC' ? 'checked' : ''; ?>>
                                <div class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-500/10 hover:border-gray-500 transition-all">
                                    <div class="text-2xl mr-3"><?php echo $info['icon']; ?></div>
                                    <div>
                                        <div class="text-white font-medium"><?php echo $code; ?></div>
                                        <div class="text-gray-400 text-sm"><?php echo $info['name']; ?></div>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <i data-lucide="shield" class="h-5 w-5 text-blue-400"></i>
                        <span class="text-blue-400 font-medium">Secure Payment</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Powered by Plisio.net - trusted crypto payment processor</li>
                        <li>• Real-time exchange rates and automatic conversion</li>
                        <li>• Instant confirmation and processing</li>
                        <li>• No hidden fees or charges</li>
                    </ul>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-4 rounded-lg font-semibold text-lg hover:from-orange-600 hover:to-yellow-600 transition-all">
                    <i data-lucide="credit-card" class="h-5 w-5 inline mr-2"></i>
                    Proceed to Payment
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/deposit" class="text-gray-400 hover:text-white text-sm">
                    ← Back to Deposit Options
                </a>
            </div>
        </div>

        <div class="mt-8 bg-slate-800/30 p-6 rounded-xl">
            <h4 class="text-white font-semibold mb-3">How it works</h4>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                    <div class="bg-blue-500/20 p-3 rounded-lg mb-2">
                        <i data-lucide="mouse-pointer-click" class="h-6 w-6 text-blue-400 mx-auto"></i>
                    </div>
                    <h5 class="text-white font-medium mb-1">1. Select Currency</h5>
                    <p class="text-gray-400">Choose your preferred cryptocurrency</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-500/20 p-3 rounded-lg mb-2">
                        <i data-lucide="wallet" class="h-6 w-6 text-blue-400 mx-auto"></i>
                    </div>
                    <h5 class="text-white font-medium mb-1">2. Make Payment</h5>
                    <p class="text-gray-400">Send crypto to the provided address</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-500/20 p-3 rounded-lg mb-2">
                        <i data-lucide="check-circle" class="h-6 w-6 text-blue-400 mx-auto"></i>
                    </div>
                    <h5 class="text-white font-medium mb-1">3. Instant Credit</h5>
                    <p class="text-gray-400">Funds added to your account automatically</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>