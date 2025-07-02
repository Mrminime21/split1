<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Get user statistics
$stats = [
    'total_earnings' => $user['total_earnings'],
    'active_devices' => $db->fetch("SELECT COUNT(*) as count FROM rentals WHERE user_id = ? AND status = 'active'", [$user['id']])['count'],
    'referrals' => $db->fetch("SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ?", [$user['id']])['count'],
    'daily_profit' => $db->fetch("SELECT COALESCE(SUM(total_profit_amount), 0) as total FROM rental_earnings WHERE user_id = ? AND earning_date = CURDATE()", [$user['id']])['total']
];

// Get recent activities
$activities = $db->fetchAll("
    SELECT 'rental' as type, 'Device activation' as action, d.name as device, re.created_at as time, re.total_profit_amount as profit
    FROM rental_earnings re 
    JOIN rentals r ON re.rental_id = r.id 
    JOIN devices d ON r.device_id = d.id 
    WHERE re.user_id = ? 
    UNION ALL
    SELECT 'referral' as type, 'Referral bonus' as action, CONCAT('User @', u.username) as device, ref_e.created_at as time, ref_e.commission_amount as profit
    FROM referral_earnings ref_e 
    JOIN users u ON ref_e.referred_id = u.id 
    WHERE ref_e.referrer_id = ?
    ORDER BY time DESC LIMIT 5
", [$user['id'], $user['id']]);

$title = 'Dashboard';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard</h1>
            <p class="text-gray-300">Monitor your earnings and device performance</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Earnings</p>
                        <p class="text-2xl font-bold text-white mt-1">$<?php echo number_format($stats['total_earnings'], 2); ?></p>
                        <p class="text-green-400 text-sm mt-1">+12.5%</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <i data-lucide="dollar-sign" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Active Devices</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $stats['active_devices']; ?></p>
                        <p class="text-green-400 text-sm mt-1">+2</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <i data-lucide="satellite" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $stats['referrals']; ?></p>
                        <p class="text-green-400 text-sm mt-1">+5</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <i data-lucide="users" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10 hover:border-blue-500/30 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Daily Profit</p>
                        <p class="text-2xl font-bold text-white mt-1">$<?php echo number_format($stats['daily_profit'], 2); ?></p>
                        <p class="text-green-400 text-sm mt-1">+8.2%</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <i data-lucide="trending-up" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <h3 class="text-xl font-semibold text-white mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    <?php foreach ($activities as $activity): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                            <div>
                                <p class="text-white font-medium"><?php echo htmlspecialchars($activity['action']); ?></p>
                                <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($activity['device']); ?> • <?php echo date('M j, Y', strtotime($activity['time'])); ?></p>
                            </div>
                            <span class="text-green-400 font-semibold">+$<?php echo number_format($activity['profit'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <h3 class="text-xl font-semibold text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="/rental" class="w-full bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-3 rounded-lg font-medium hover:from-blue-600 hover:to-cyan-500 transition-all block text-center">
                        Rent New Device
                    </a>
                    <button class="w-full bg-slate-700 text-white p-3 rounded-lg font-medium hover:bg-slate-600 transition-all">
                        Withdraw Earnings
                    </button>
                    <a href="/referrals" class="w-full bg-slate-700 text-white p-3 rounded-lg font-medium hover:bg-slate-600 transition-all block text-center">
                        View Referrals
                    </a>
                    <button class="w-full bg-slate-700 text-white p-3 rounded-lg font-medium hover:bg-slate-600 transition-all">
                        Support Center
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderLayout($title, $content);
?>