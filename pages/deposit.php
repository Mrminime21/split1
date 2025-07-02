<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle deposit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $amount = floatval($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? '';
        
        if ($amount < 50) {
            throw new Exception('Minimum deposit amount is $50');
        }
        
        if ($amount > 10000) {
            throw new Exception('Maximum deposit amount is $10,000');
        }
        
        $validMethods = ['crypto', 'binance', 'card', 'bank_transfer'];
        if (!in_array($method, $validMethods)) {
            throw new Exception('Invalid payment method');
        }
        
        // Generate unique order ID
        $orderId = 'DEP_' . time() . '_' . $user['id'] . '_' . rand(1000, 9999);
        
        // Store payment info in session for crypto payments
        if ($method === 'crypto') {
            $_SESSION['payment_amount'] = $amount;
            $_SESSION['payment_type'] = 'deposit';
            $_SESSION['payment_order_id'] = $orderId;
            
            // Redirect to Plisio payment page
            header('Location: /pages/payment/plisio.php?amount=' . $amount . '&type=deposit&order_id=' . $orderId);
            exit;
        }
        
        // Create payment record for other methods
        $paymentData = [
            'user_id' => $user['id'],
            'amount' => $amount,
            'payment_method' => $method,
            'status' => 'pending',
            'type' => 'deposit',
            'description' => 'Account deposit via ' . ucfirst($method),
            'transaction_id' => $orderId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $paymentId = $db->insert('payments', $paymentData);
        
        $success = 'Deposit request created successfully! Payment ID: ' . $orderId;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get recent deposits
$deposits = $db->fetchAll("
    SELECT * FROM payments 
    WHERE user_id = ? AND type = 'deposit' 
    ORDER BY created_at DESC 
    LIMIT 10
", [$user['id']]);

$title = 'Deposit Funds';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Deposit Funds</h1>
            <p class="text-xl text-gray-300">
                Add funds to your account to start investing and renting devices
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Deposit Form -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
                <h3 class="text-2xl font-bold text-white mb-6">Make Deposit</h3>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Deposit Amount ($)</label>
                        <input type="number" name="amount" min="50" max="10000" step="10" required
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter amount (min $50)">
                        <p class="text-gray-400 text-sm mt-1">Minimum: $50 | Maximum: $10,000</p>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Payment Method</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="crypto" class="mr-3" required>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 p-2 rounded-lg">
                                        <i data-lucide="bitcoin" class="h-5 w-5 text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Cryptocurrency</h4>
                                        <p class="text-gray-400 text-sm">Bitcoin, Ethereum, USDT and more via Plisio.net</p>
                                        <p class="text-green-400 text-xs font-medium">✓ Instant processing • ✓ Low fees • ✓ Secure</p>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="binance" class="mr-3" required>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-2 rounded-lg">
                                        <i data-lucide="wallet" class="h-5 w-5 text-black"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Binance Pay</h4>
                                        <p class="text-gray-400 text-sm">Direct from Binance account</p>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="card" class="mr-3" required>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-lg">
                                        <i data-lucide="credit-card" class="h-5 w-5 text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Credit/Debit Card</h4>
                                        <p class="text-gray-400 text-sm">Visa, Mastercard, American Express</p>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="method" value="bank_transfer" class="mr-3" required>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-2 rounded-lg">
                                        <i data-lucide="building" class="h-5 w-5 text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">Bank Transfer</h4>
                                        <p class="text-gray-400 text-sm">Direct bank wire transfer</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                        Create Deposit Request
                    </button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="space-y-6">
                <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                    <h4 class="text-lg font-semibold text-white mb-4">Account Balance</h4>
                    <div class="text-3xl font-bold text-green-400 mb-2">
                        $<?php echo number_format($user['balance'], 2); ?>
                    </div>
                    <p class="text-gray-400">Available for investment and rentals</p>
                </div>

                <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                    <h4 class="text-lg font-semibold text-white mb-4">Deposit Information</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Minimum Deposit:</span>
                            <span class="text-white">$50</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Maximum Deposit:</span>
                            <span class="text-white">$10,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Crypto Processing:</span>
                            <span class="text-white">5-30 minutes</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Other Methods:</span>
                            <span class="text-white">1-3 business days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Deposit Fee:</span>
                            <span class="text-green-400">Free</span>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-500/10 border border-orange-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <i data-lucide="bitcoin" class="h-5 w-5 text-orange-400"></i>
                        <span class="text-orange-400 font-medium">Crypto Payments via Plisio.net</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Instant processing and confirmation</li>
                        <li>• Support for 15+ cryptocurrencies</li>
                        <li>• Real-time exchange rates</li>
                        <li>• Secure and encrypted transactions</li>
                    </ul>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <i data-lucide="shield" class="h-5 w-5 text-blue-400"></i>
                        <span class="text-blue-400 font-medium">Secure Deposits</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        All deposits are processed through secure payment gateways with bank-level encryption and fraud protection.
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent Deposits -->
        <?php if (!empty($deposits)): ?>
        <div class="mt-12 bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <h3 class="text-2xl font-bold text-white mb-6">Recent Deposits</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">Transaction ID</th>
                            <th class="text-left text-gray-400 pb-3">Amount</th>
                            <th class="text-left text-gray-400 pb-3">Method</th>
                            <th class="text-left text-gray-400 pb-3">Status</th>
                            <th class="text-left text-gray-400 pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deposits as $deposit): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 text-white font-mono text-sm"><?php echo htmlspecialchars($deposit['transaction_id']); ?></td>
                            <td class="py-4 text-white">
                                $<?php echo number_format($deposit['amount'], 2); ?>
                                <?php if ($deposit['crypto_currency']): ?>
                                    <span class="text-gray-400 text-sm">(<?php echo strtoupper($deposit['crypto_currency']); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 text-gray-300">
                                <?php echo ucfirst($deposit['payment_method']); ?>
                                <?php if ($deposit['payment_provider'] === 'plisio'): ?>
                                    <span class="text-orange-400 text-xs">via Plisio</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $deposit['status'] === 'completed' ? 'bg-green-500/20 text-green-400' : 
                                         ($deposit['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400'); 
                                ?>">
                                    <?php echo ucfirst($deposit['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y H:i', strtotime($deposit['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>