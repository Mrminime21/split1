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

// Handle rental form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $planType = $_POST['plan_type'] ?? '';
        $deviceId = intval($_POST['device_id'] ?? 0);
        $duration = intval($_POST['duration'] ?? 30);
        
        $plans = [
            'basic' => ['rate' => 5.00, 'cost_per_day' => 2.00],
            'standard' => ['rate' => 8.00, 'cost_per_day' => 5.00],
            'premium' => ['rate' => 12.00, 'cost_per_day' => 10.00]
        ];
        
        if (!isset($plans[$planType])) {
            throw new Exception('Invalid rental plan');
        }
        
        $plan = $plans[$planType];
        $totalCost = $plan['cost_per_day'] * $duration;
        $expectedDailyProfit = ($totalCost * $plan['rate']) / 100 / $duration;
        
        if ($totalCost > $user['balance']) {
            throw new Exception('Insufficient balance');
        }
        
        // Check device availability
        $device = $db->fetch("SELECT * FROM devices WHERE id = ? AND status = 'available'", [$deviceId]);
        if (!$device) {
            throw new Exception('Device not available');
        }
        
        // Create rental record
        $rentalData = [
            'user_id' => $user['id'],
            'device_id' => $deviceId,
            'plan_type' => $planType,
            'plan_name' => ucfirst($planType) . ' Rental Plan',
            'rental_duration' => $duration,
            'daily_profit_rate' => $plan['rate'],
            'total_cost' => $totalCost,
            'expected_daily_profit' => $expectedDailyProfit,
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+' . $duration . ' days')),
            'actual_start_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $rentalId = $db->insert('rentals', $rentalData);
        
        // Update device status
        $db->update('devices', ['status' => 'rented'], 'id = ?', [$deviceId]);
        
        // Deduct from user balance
        $db->update('users', 
            ['balance' => $user['balance'] - $totalCost, 'total_invested' => $user['total_invested'] + $totalCost],
            'id = ?', 
            [$user['id']]
        );
        
        // Create payment record
        $paymentData = [
            'user_id' => $user['id'],
            'amount' => $totalCost,
            'payment_method' => 'balance',
            'status' => 'completed',
            'type' => 'rental',
            'description' => 'Device rental: ' . $device['name'],
            'processed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('payments', $paymentData);
        
        $success = 'Device rental activated successfully! Daily profits will start tomorrow.';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get available devices
$devices = $db->fetchAll("SELECT * FROM devices WHERE status = 'available' ORDER BY name");

// Get user's active rentals
$rentals = $db->fetchAll("
    SELECT r.*, d.name as device_name, d.location 
    FROM rentals r 
    JOIN devices d ON r.device_id = d.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
", [$user['id']]);

$title = 'Device Rental';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Starlink Device Rental</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Choose your rental plan and start earning daily profits from premium Starlink satellite devices
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

        <!-- Device Specifications -->
        <div class="grid lg:grid-cols-4 gap-8 mb-12">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <i data-lucide="globe" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Global Coverage</h3>
                <p class="text-cyan-400 font-medium">99.9% Uptime</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <i data-lucide="zap" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Speed</h3>
                <p class="text-cyan-400 font-medium">Up to 200 Mbps</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <i data-lucide="shield" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Security</h3>
                <p class="text-cyan-400 font-medium">Enterprise Grade</p>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 text-center">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg w-fit mx-auto mb-4">
                    <i data-lucide="calendar" class="h-6 w-6 text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Activation</h3>
                <p class="text-cyan-400 font-medium">Instant Setup</p>
            </div>
        </div>

        <!-- Rental Plans -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Basic Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$2</span>
                        <span class="text-gray-400 ml-1">/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 5%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">1 Starlink Device</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Basic Support</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Standard Speeds</span>
                    </li>
                </ul>

                <button onclick="selectPlan('basic')" class="w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white py-3 rounded-lg font-semibold transition-all">
                    Select Basic
                </button>
            </div>

            <div class="relative bg-slate-800/50 p-8 rounded-2xl border border-blue-500 bg-slate-800/70 card-hover">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-1 rounded-full text-sm font-medium">
                        Most Popular
                    </span>
                </div>

                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Standard Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$5</span>
                        <span class="text-gray-400 ml-1">/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 8%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">3 Starlink Devices</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Priority Support</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">High-Speed Internet</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Device Monitoring</span>
                    </li>
                </ul>

                <button onclick="selectPlan('standard')" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold transition-all">
                    Select Standard
                </button>
            </div>

            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 hover:border-blue-500/30 transition-all card-hover">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Premium Plan</h3>
                    <div class="flex items-end justify-center mb-2">
                        <span class="text-4xl font-bold text-white">$10</span>
                        <span class="text-gray-400 ml-1">/day</span>
                    </div>
                    <p class="text-cyan-400 font-medium">Daily Profit: 12%</p>
                </div>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">6 Starlink Devices</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">24/7 VIP Support</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">30-day Minimum</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Ultra-High Speeds</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Advanced Analytics</span>
                    </li>
                    <li class="flex items-center">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400 mr-3"></i>
                        <span class="text-gray-300">Backup Devices</span>
                    </li>
                </ul>

                <button onclick="selectPlan('premium')" class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 rounded-lg font-semibold transition-all">
                    Select Premium
                </button>
            </div>
        </div>

        <!-- Rental Form -->
        <div class="max-w-2xl mx-auto bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 mb-8">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Complete Your Rental</h3>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-300 mb-2">Selected Plan</label>
                    <select name="plan_type" id="plan_type" required 
                            class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                        <option value="">Select a plan</option>
                        <option value="basic">Basic Plan - $2/day (5% profit)</option>
                        <option value="standard">Standard Plan - $5/day (8% profit)</option>
                        <option value="premium">Premium Plan - $10/day (12% profit)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Available Device</label>
                    <select name="device_id" required 
                            class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none">
                        <option value="">Select a device</option>
                        <?php foreach ($devices as $device): ?>
                            <option value="<?php echo $device['id']; ?>">
                                <?php echo htmlspecialchars($device['name']); ?> - <?php echo htmlspecialchars($device['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-300 mb-2">Rental Duration (Days)</label>
                    <input type="number" name="duration" id="duration" min="30" max="365" value="30" required
                           class="w-full bg-slate-700 text-white p-3 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"
                           placeholder="Enter duration in days">
                    <p class="text-gray-400 text-sm mt-1">Minimum: 30 days | Your balance: $<?php echo number_format($user['balance'], 2); ?></p>
                </div>

                <div id="calculation" class="bg-slate-700/50 p-4 rounded-lg hidden">
                    <h4 class="text-white font-semibold mb-3">Rental Summary</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Total Cost:</span>
                            <span class="text-white font-semibold" id="total-cost">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Daily Profit:</span>
                            <span class="text-green-400 font-semibold" id="daily-profit">$0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Total Expected Profit:</span>
                            <span class="text-green-400 font-semibold text-xl" id="total-profit">$0.00</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                    Start Rental
                </button>
            </form>
        </div>

        <!-- Active Rentals -->
        <?php if (!empty($rentals)): ?>
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
            <h3 class="text-2xl font-bold text-white mb-6">Your Active Rentals</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">Device</th>
                            <th class="text-left text-gray-400 pb-3">Plan</th>
                            <th class="text-left text-gray-400 pb-3">Daily Profit</th>
                            <th class="text-left text-gray-400 pb-3">Total Earned</th>
                            <th class="text-left text-gray-400 pb-3">Status</th>
                            <th class="text-left text-gray-400 pb-3">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rental): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4">
                                <div>
                                    <p class="text-white font-medium"><?php echo htmlspecialchars($rental['device_name']); ?></p>
                                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($rental['location']); ?></p>
                                
                                </div>
                            </td>
                            <td class="py-4 text-white"><?php echo ucfirst($rental['plan_type']); ?></td>
                            <td class="py-4 text-green-400">$<?php echo number_format($rental['expected_daily_profit'], 2); ?></td>
                            <td class="py-4 text-green-400">$<?php echo number_format($rental['actual_total_profit'], 2); ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $rental['status'] === 'active' ? 'bg-green-500/20 text-green-400' : 
                                         ($rental['status'] === 'completed' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400'); 
                                ?>">
                                    <?php echo ucfirst($rental['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y', strtotime($rental['end_date'])); ?></td>
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
        'basic': { rate: 5.0, cost: 2.0 },
        'standard': { rate: 8.0, cost: 5.0 },
        'premium': { rate: 12.0, cost: 10.0 }
    };

    function selectPlan(planType) {
        document.getElementById('plan_type').value = planType;
        calculateCosts();
    }

    function calculateCosts() {
        const planType = document.getElementById('plan_type').value;
        const duration = parseInt(document.getElementById('duration').value) || 30;
        
        if (planType && plans[planType]) {
            const plan = plans[planType];
            const totalCost = plan.cost * duration;
            const dailyProfit = (totalCost * plan.rate) / 100 / duration;
            const totalProfit = dailyProfit * duration;
            
            document.getElementById('total-cost').textContent = '$' + totalCost.toFixed(2);
            document.getElementById('daily-profit').textContent = '$' + dailyProfit.toFixed(2);
            document.getElementById('total-profit').textContent = '$' + totalProfit.toFixed(2);
            document.getElementById('calculation').classList.remove('hidden');
        } else {
            document.getElementById('calculation').classList.add('hidden');
        }
    }

    document.getElementById('plan_type').addEventListener('change', calculateCosts);
    document.getElementById('duration').addEventListener('input', calculateCosts);
";

echo renderLayout($title, $content, '', $additionalJS);
?>