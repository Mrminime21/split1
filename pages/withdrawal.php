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

// Handle withdrawal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $amount = floatval($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? '';
        $address = $_POST['address'] ?? '';
        
        if ($amount < 20) {
            throw new Exception('Minimum withdrawal amount is $20');
        }
        
        if ($amount > $user['balance']) {
            throw new Exception('Insufficient balance');
        }
        
        $validMethods = ['crypto', 'bank_transfer', 'paypal', 'binance'];
        if (!in_array($method, $validMethods)) {
            throw new Exception('Invalid withdrawal method');
        }
        
        if (empty($address)) {
            throw new Exception('Withdrawal address/details are required');
        }
        
        // Calculate fees
        $feePercentage = 2.0; // 2%
        $feeAmount = max(($amount * $feePercentage) / 100, 5.0); // Minimum $5 fee
        $netAmount = $amount - $feeAmount;
        
        // Create withdrawal request
        $withdrawalData = [
            'user_id' => $user['id'],
            'amount' => $amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'withdrawal_method' => $method,
            'withdrawal_address' => $address,
            'status' => 'pending',
            'user_notes' => $_POST['notes'] ?? '',
            'requested_at' => date('Y-m-d H:i:s')
        ];
        
        $withdrawalId = $db->insert('withdrawal_requests', $withdrawalData);
        
        // Deduct from user balance (hold the funds)
        $db->update('users', 
            ['balance' => $user['balance'] - $amount],
            'id = ?', 
            [$user['id']]
        );
        
        // Create payment record
        $paymentData = [
            'user_id' => $user['id'],
            'amount' => $amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'payment_method' => $method,
            'status' => 'pending',
            'type' => 'withdrawal',
            'description' => 'Withdrawal request via ' . ucfirst($method),
            'transaction_id' => 'WD_' . time() . '_' . $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('payments', $paymentData);
        
        $success = 'Withdrawal request submitted successfully! It will be processed within 24 hours.';
        
        // Refresh user data
        $user = $auth->getCurrentUser();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get recent withdrawals
$withdrawals = $db->fetchAll("
    SELECT * FROM withdrawal_requests 
    WHERE user_id = ? 
    ORDER BY requested_at DESC 
    LIMIT 10
", [$user['id']]);

$title = 'Withdraw Funds';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Withdraw Funds</h1>
            <p class="text-xl text-gray-300">
                Withdraw your earnings to your preferred payment method
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
            <!-- Withdrawal Form -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
                <h3 class="text-2xl font-bold text-white mb-6">Request Withdrawal</h3>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Amount ($)</label>
                        <input type="number" name="amount" min="20" step="1" required
                               max="<?php echo $user['balance']; ?>"
                               class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                               placeholder="Enter amount (min $20)">
                        <p class="text-gray-400 text-sm mt-1">Available: $<?php echo number_format($user['balance'], 2); ?></p>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Withdrawal Method</label>
                        <select name="method" required onchange="updateAddressLabel()"
                                class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                            <option value="">Select method</option>
                            <option value="crypto">Cryptocurrency</option>
                            <option value="binance">Binance Pay</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2" id="address-label">Withdrawal Address/Details</label>
                        <textarea name="address" required rows="3"
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                  placeholder="Enter your wallet address, bank details, or PayPal email"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="2"
                                  class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                                  placeholder="Additional notes for the withdrawal"></textarea>
                    </div>

                    <div class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-lg">
                        <h4 class="text-yellow-400 font-medium mb-2">Withdrawal Fees</h4>
                        <p class="text-gray-300 text-sm">
                            A 2% fee (minimum $5) will be deducted from your withdrawal amount.
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                        Submit Withdrawal Request
                    </button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="space-y-6">
                <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                    <h4 class="text-lg font-semibold text-white mb-4">Account Summary</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Available Balance:</span>
                            <span class="text-green-400 font-semibold">$<?php echo number_format($user['balance'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Earnings:</span>
                            <span class="text-white">$<?php echo number_format($user['total_earnings'], 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Withdrawn:</span>
                            <span class="text-white">$<?php echo number_format($user['total_withdrawn'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                    <h4 class="text-lg font-semibold text-white mb-4">Withdrawal Information</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Minimum Withdrawal:</span>
                            <span class="text-white">$20</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Processing Time:</span>
                            <span class="text-white">24-48 hours</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Withdrawal Fee:</span>
                            <span class="text-white">2% (min $5)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Daily Limit:</span>
                            <span class="text-white">$5,000</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <i data-lucide="clock" class="h-5 w-5 text-blue-400"></i>
                        <span class="text-blue-400 font-medium">Processing Times</span>
                    </div>
                    <ul class="text-gray-300 text-sm space-y-1">
                        <li>• Crypto: 2-6 hours</li>
                        <li>• Binance Pay: 1-2 hours</li>
                        <li>• Bank Transfer: 1-3 business days</li>
                        <li>• PayPal: 24-48 hours</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Withdrawals -->
        <?php if (!empty($withdrawals)): ?>
        <div class="mt-12 bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <h3 class="text-2xl font-bold text-white mb-6">Recent Withdrawals</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">Amount</th>
                            <th class="text-left text-gray-400 pb-3">Fee</th>
                            <th class="text-left text-gray-400 pb-3">Net Amount</th>
                            <th class="text-left text-gray-400 pb-3">Method</th>
                            <th class="text-left text-gray-400 pb-3">Status</th>
                            <th class="text-left text-gray-400 pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 text-white">$<?php echo number_format($withdrawal['amount'], 2); ?></td>
                            <td class="py-4 text-red-400">-$<?php echo number_format($withdrawal['fee_amount'], 2); ?></td>
                            <td class="py-4 text-green-400">$<?php echo number_format($withdrawal['net_amount'], 2); ?></td>
                            <td class="py-4 text-gray-300"><?php echo ucfirst($withdrawal['withdrawal_method']); ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $withdrawal['status'] === 'completed' ? 'bg-green-500/20 text-green-400' : 
                                         ($withdrawal['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 
                                         ($withdrawal['status'] === 'processing' ? 'bg-blue-500/20 text-blue-400' : 'bg-red-500/20 text-red-400')); 
                                ?>">
                                    <?php echo ucfirst($withdrawal['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y H:i', strtotime($withdrawal['requested_at'])); ?></td>
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

$additionalJS = "
    function updateAddressLabel() {
        const method = document.querySelector('select[name=\"method\"]').value;
        const label = document.getElementById('address-label');
        const textarea = document.querySelector('textarea[name=\"address\"]');
        
        switch(method) {
            case 'crypto':
                label.textContent = 'Cryptocurrency Wallet Address';
                textarea.placeholder = 'Enter your wallet address (BTC, ETH, USDT, etc.)';
                break;
            case 'binance':
                label.textContent = 'Binance Email/ID';
                textarea.placeholder = 'Enter your Binance account email or user ID';
                break;
            case 'bank_transfer':
                label.textContent = 'Bank Account Details';
                textarea.placeholder = 'Bank name, account number, routing number, SWIFT code, etc.';
                break;
            case 'paypal':
                label.textContent = 'PayPal Email Address';
                textarea.placeholder = 'Enter your PayPal email address';
                break;
            default:
                label.textContent = 'Withdrawal Address/Details';
                textarea.placeholder = 'Enter your wallet address, bank details, or PayPal email';
        }
    }
";

echo renderLayout($title, $content, '', $additionalJS);
?>