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

// Handle investment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $planType = $_POST['plan_type'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        
        $plans = [
            '3months' => ['duration' => 90, 'rate' => 0.27, 'min' => 500],
            '6months' => ['duration' => 180, 'rate' => 0.40, 'min' => 1000],
            '12months' => ['duration' => 365, 'rate' => 0.60, 'min' => 2000]
        ];
        
        if (!isset($plans[$planType])) {
            throw new Exception('Invalid investment plan');
        }
        
        $plan = $plans[$planType];
        
        if ($amount < $plan['min']) {
            throw new Exception("Minimum investment for this plan is $" . number_format($plan['min']));
        }
        
        if ($amount > $user['balance']) {
            throw new Exception('Insufficient balance');
        }
        
        // Create investment record
        $investmentData = [
            'user_id' => $user['id'],
            'plan_name' => ucfirst($planType) . ' Investment Plan',
            'plan_duration' => $plan['duration'],
            'investment_amount' => $amount,
            'daily_rate' => $plan['rate'],
            'expected_daily_profit' => ($amount * $plan['rate']) / 100,
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+' . $plan['duration'] . ' days')),
            'actual_start_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $investmentId = $db->insert('investments', $investmentData);
        
        // Deduct from user balance
        $db->update('users', 
            ['balance' => $user['balance'] - $amount, 'total_invested' => $user['total_invested'] + $amount],
            'id = ?', 
            [$user['id']]
        );
        
        // Create payment record
        $paymentData = [
            'user_id' => $user['id'],
            'amount' => $amount,
            'payment_method' => 'balance',
            'status' => 'completed',
            'type' => 'investment',
            'description' => 'Investment in ' . $investmentData['plan_name'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('payments', $paymentData);
        
        $success = 'Investment created successfully! Daily profits will start tomorrow.';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get user's active investments
$investments = $db->fetchAll("
    SELECT * FROM investments 
    WHERE user_id = ? 
    ORDER BY created_at DESC
", [$user['id']]);

$title = 'Investment Plans';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Investment Plans</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Invest in our Starlink device network and earn guaranteed daily profits with transparent returns
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6 max-w-2xl mx-auto">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mb-6 max-w-2xl mx-auto">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <!-- 3 Month Plan -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">3 Months</h3>
                    <div class="text-3xl font-bold text-green-400 mb-4">8%</div>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Daily Return:</span>
                            <span class="text-white">0.27%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Return:</span>
                            <span class="text-green-400">24%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Min Investment:</span>
                            <span class="text-white">$500</span>
                        </div>
                    </div>

                    <button onclick="selectPlan('3months', 500, 0.27)" 
                            class="w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white py-3 rounded-lg font-semibold transition-all">
                        Select Plan
                    </button>
                </div>
            </div>

            <!-- 6 Month Plan -->
            <div class="relative bg-slate-800/50 p-8 rounded-2xl border border-blue-500 bg-slate-800/70 card-hover">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                        Best Value
                    </span>
                </div>

                <div class="text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">6 Months</h3>
                    <div class="text-3xl font-bold text-green-400 mb-4">12%</div>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Daily Return:</span>
                            <span class="text-white">0.40%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Return:</span>
                            <span class="text-green-400">72%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Min Investment:</span>
                            <span class="text-white">$1,000</span>
                        </div>
                    </div>

                    <button onclick="selectPlan('6months', 1000, 0.40)" 
                            class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold transition-all">
                        Select Plan
                    </button>
                </div>
            </div>

            <!-- 12 Month Plan -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">12 Months</h3>
                    <div class="text-3xl font-bold text-green-400 mb-4">18%</div>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Daily Return:</span>
                            <span class="text-white">0.60%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Return:</span>
                            <span class="text-green-400">216%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Min Investment:</span>
                            <span class="text-white">$2,000</span>
                        </div>
                    </div>

                    <button onclick="selectPlan('12months', 2000, 0.60)" 
                            class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 rounded-lg font-semibold transition-all">
                        Select Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Investment Form -->
        <div class="max-w-2xl mx-auto bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 mb-8">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Make Investment</h3>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2">Selected Plan</label>
                    <select name="plan_type" id="plan_type" required 
                            class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                        <option value="">Select a plan</option>
                        <option value="3months">3 Months - 0.27% Daily</option>
                        <option value="6months">6 Months - 0.40% Daily</option>
                        <option value="12months">12 Months - 0.60% Daily</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Investment Amount ($)</label>
                    <input type="number" name="amount" id="amount" min="500" step="100" required
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                           placeholder="Enter investment amount">
                    <p class="text-gray-400 text-sm mt-1">Your balance: $<?php echo number_format($user['balance'], 2); ?></p>
                </div>

                <div id="calculation" class="bg-slate-700/50 p-4 rounded-lg hidden">
                    <h4 class="text-white font-semibold mb-3">Investment Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Daily Profit:</span>
                            <span class="text-green-400 font-semibold" id="daily-profit">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Monthly Profit:</span>
                            <span class="text-green-400 font-semibold" id="monthly-profit">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Total Return:</span>
                            <span class="text-green-400 font-semibold text-xl" id="total-return">$0.00</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                    Create Investment
                </button>
            </form>
        </div>

        <!-- Active Investments -->
        <?php if (!empty($investments)): ?>
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <h3 class="text-2xl font-bold text-white mb-6">Your Investments</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">Plan</th>
                            <th class="text-left text-gray-400 pb-3">Amount</th>
                            <th class="text-left text-gray-400 pb-3">Daily Profit</th>
                            <th class="text-left text-gray-400 pb-3">Total Earned</th>
                            <th class="text-left text-gray-400 pb-3">Status</th>
                            <th class="text-left text-gray-400 pb-3">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($investments as $investment): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 text-white"><?php echo htmlspecialchars($investment['plan_name']); ?></td>
                            <td class="py-4 text-white">$<?php echo number_format($investment['investment_amount'], 2); ?></td>
                            <td class="py-4 text-green-400">$<?php echo number_format($investment['expected_daily_profit'], 2); ?></td>
                            <td class="py-4 text-green-400">$<?php echo number_format($investment['total_earned'], 2); ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $investment['status'] === 'active' ? 'bg-green-500/20 text-green-400' : 
                                         ($investment['status'] === 'completed' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400'); 
                                ?>">
                                    <?php echo ucfirst($investment['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y', strtotime($investment['end_date'])); ?></td>
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
    const plans = {
        '3months': { rate: 0.27, min: 500, duration: 90 },
        '6months': { rate: 0.40, min: 1000, duration: 180 },
        '12months': { rate: 0.60, min: 2000, duration: 365 }
    };

    function selectPlan(planType, minAmount, rate) {
        document.getElementById('plan_type').value = planType;
        document.getElementById('amount').value = minAmount;
        document.getElementById('amount').min = minAmount;
        calculateReturns();
    }

    function calculateReturns() {
        const planType = document.getElementById('plan_type').value;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        
        if (planType && amount > 0 && plans[planType]) {
            const plan = plans[planType];
            const dailyProfit = (amount * plan.rate) / 100;
            const monthlyProfit = dailyProfit * 30;
            const totalReturn = amount + (dailyProfit * plan.duration);
            
            document.getElementById('daily-profit').textContent = '$' + dailyProfit.toFixed(2);
            document.getElementById('monthly-profit').textContent = '$' + monthlyProfit.toFixed(2);
            document.getElementById('total-return').textContent = '$' + totalReturn.toFixed(2);
            document.getElementById('calculation').classList.remove('hidden');
        } else {
            document.getElementById('calculation').classList.add('hidden');
        }
    }

    document.getElementById('plan_type').addEventListener('change', calculateReturns);
    document.getElementById('amount').addEventListener('input', calculateReturns);
";

echo renderLayout($title, $content, '', $additionalJS);
?>