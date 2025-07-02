<?php
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/layout.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user = $auth->getCurrentUser();

// Get referral statistics
$referralStats = [
    'total_referrals' => $db->fetch("SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ?", [$user['id']])['count'],
    'level1_referrals' => $db->fetch("SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ? AND level = 1", [$user['id']])['count'],
    'level2_referrals' => $db->fetch("SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ? AND level = 2", [$user['id']])['count'],
    'level3_referrals' => $db->fetch("SELECT COUNT(*) as count FROM referrals WHERE referrer_id = ? AND level = 3", [$user['id']])['count']
];

// Get recent referrals
$recentReferrals = $db->fetchAll("
    SELECT r.*, u.username, u.created_at as join_date, u.total_earnings, u.status 
    FROM referrals r 
    JOIN users u ON r.referred_id = u.id 
    WHERE r.referrer_id = ? 
    ORDER BY u.created_at DESC 
    LIMIT 10
", [$user['id']]);

// Get referral earnings
$referralEarnings = $db->fetchAll("
    SELECT re.*, u.username 
    FROM referral_earnings re 
    JOIN users u ON re.referred_id = u.id 
    WHERE re.referrer_id = ? 
    ORDER BY re.earning_date DESC 
    LIMIT 10
", [$user['id']]);

$title = 'Referral System';

ob_start();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Referral System</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Build your network and earn up to 15% commission from 3 levels of referrals
            </p>
        </div>

        <!-- Referral Stats -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $referralStats['total_referrals']; ?></p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-3 rounded-lg">
                        <i data-lucide="users" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 1 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $referralStats['level1_referrals']; ?></p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-lg">
                        <i data-lucide="share-2" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 2 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $referralStats['level2_referrals']; ?></p>
                    </div>
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-lg">
                        <i data-lucide="gift" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/50 p-6 rounded-xl border border-blue-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Level 3 Referrals</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo $referralStats['level3_referrals']; ?></p>
                    </div>
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 p-3 rounded-lg">
                        <i data-lucide="trophy" class="h-6 w-6 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Referral Code -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
                <h3 class="text-2xl font-bold text-white mb-6">Your Referral Code</h3>
                
                <div class="bg-slate-700/50 p-4 rounded-lg mb-6">
                    <div class="flex items-center justify-between">
                        <code class="text-2xl font-mono text-cyan-400 font-bold"><?php echo htmlspecialchars($user['referral_code']); ?></code>
                        <button onclick="copyReferralCode()" id="copy-btn"
                                class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-all">
                            <i data-lucide="copy" class="h-4 w-4"></i>
                            <span>Copy</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-white">Share Your Link</h4>
                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <p class="text-gray-300 text-sm mb-2">Referral Link:</p>
                        <code class="text-cyan-400 text-sm break-all" id="referral-link">
                            <?php echo $_SERVER['HTTP_HOST']; ?>/login?ref=<?php echo htmlspecialchars($user['referral_code']); ?>
                        </code>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button onclick="shareToTelegram()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-all">
                            Share on Telegram
                        </button>
                        <button onclick="shareToWhatsApp()" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-all">
                            Share on WhatsApp
                        </button>
                    </div>
                </div>
            </div>

            <!-- Commission Structure -->
            <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10">
                <h3 class="text-2xl font-bold text-white mb-6">Commission Structure</h3>
                
                <div class="space-y-4">
                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 1</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-cyan-400 bg-clip-text text-transparent">7%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Direct referrals from people you invite</p>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 2</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-green-500 to-blue-500 bg-clip-text text-transparent">5%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Referrals from your level 1 referrals</p>
                    </div>

                    <div class="bg-slate-700/50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-semibold">Level 3</span>
                            <span class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent">3%</span>
                        </div>
                        <p class="text-gray-300 text-sm">Referrals from your level 2 referrals</p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-lg border border-green-500/20">
                    <div class="flex items-center space-x-2 mb-2">
                        <i data-lucide="dollar-sign" class="h-5 w-5 text-green-400"></i>
                        <span class="text-white font-semibold">Total Possible Commission: 15%</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        Maximum earning potential when you have active referrals in all 3 levels
                    </p>
                </div>
            </div>
        </div>

        <!-- Recent Referrals -->
        <?php if (!empty($recentReferrals)): ?>
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 mb-8">
            <h3 class="text-2xl font-bold text-white mb-6">Recent Referrals</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">Username</th>
                            <th class="text-left text-gray-400 pb-3">Level</th>
                            <th class="text-left text-gray-400 pb-3">Join Date</th>
                            <th class="text-left text-gray-400 pb-3">Total Earnings</th>
                            <th class="text-left text-gray-400 pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReferrals as $referral): ?>
                        <tr class="border-b border-gray-800 hover:bg-slate-700/30 transition-colors">
                            <td class="py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 p-2 rounded-full">
                                        <i data-lucide="user" class="h-4 w-4 text-white"></i>
                                    </div>
                                    <span class="text-white font-medium">@<?php echo htmlspecialchars($referral['username']); ?></span>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $referral['level'] === 1 ? 'bg-blue-500/20 text-blue-400' :
                                         ($referral['level'] === 2 ? 'bg-green-500/20 text-green-400' : 'bg-purple-500/20 text-purple-400'); 
                                ?>">
                                    Level <?php echo $referral['level']; ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y', strtotime($referral['join_date'])); ?></td>
                            <td class="py-4 text-green-400 font-semibold">$<?php echo number_format($referral['total_earnings'], 2); ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                    <?php echo ucfirst($referral['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Referral Earnings -->
        <?php if (!empty($referralEarnings)): ?>
        <div class="bg-slate-800/50 p-8 rounded-2xl border border-blue-500/10 mb-8">
            <h3 class="text-2xl font-bold text-white mb-6">Recent Commissions</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left text-gray-400 pb-3">From User</th>
                            <th class="text-left text-gray-400 pb-3">Level</th>
                            <th class="text-left text-gray-400 pb-3">Source</th>
                            <th class="text-left text-gray-400 pb-3">Commission</th>
                            <th class="text-left text-gray-400 pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referralEarnings as $earning): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 text-white">@<?php echo htmlspecialchars($earning['username']); ?></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    echo $earning['level'] === 1 ? 'bg-blue-500/20 text-blue-400' :
                                         ($earning['level'] === 2 ? 'bg-green-500/20 text-green-400' : 'bg-purple-500/20 text-purple-400'); 
                                ?>">
                                    Level <?php echo $earning['level']; ?>
                                </span>
                            </td>
                            <td class="py-4 text-gray-300"><?php echo ucfirst($earning['source_type']); ?></td>
                            <td class="py-4 text-green-400 font-semibold">+$<?php echo number_format($earning['commission_amount'], 2); ?></td>
                            <td class="py-4 text-gray-300"><?php echo date('M j, Y', strtotime($earning['earning_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-blue-500/10 to-cyan-400/10 p-8 rounded-2xl border border-blue-500/20">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-white mb-4">Boost Your Earnings</h3>
                <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
                    The more people you refer, the more you earn. Share your referral code with friends, 
                    family, and social networks to maximize your passive income potential.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="downloadMaterials()" class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-600 hover:to-cyan-500 transition-all">
                        Download Referral Materials
                    </button>
                    <button onclick="viewGuide()" class="bg-slate-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-slate-600 transition-all">
                        View Referral Guide
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJS = "
    function copyReferralCode() {
        const code = '" . htmlspecialchars($user['referral_code']) . "';
        navigator.clipboard.writeText(code).then(function() {
            const btn = document.getElementById('copy-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i data-lucide=\"check\" class=\"h-4 w-4\"></i><span>Copied!</span>';
            lucide.createIcons();
            setTimeout(function() {
                btn.innerHTML = originalText;
                lucide.createIcons();
            }, 2000);
        });
    }

    function shareToTelegram() {
        const link = document.getElementById('referral-link').textContent.trim();
        const text = 'Join Starlink Rent and start earning daily profits! Use my referral link: ' + link;
        const url = 'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    function shareToWhatsApp() {
        const link = document.getElementById('referral-link').textContent.trim();
        const text = 'Join Starlink Rent and start earning daily profits! Use my referral link: ' + link;
        const url = 'https://wa.me/?text=' + encodeURIComponent(text);
        window.open(url, '_blank');
    }

    function downloadMaterials() {
        alert('Referral materials will be available soon!');
    }

    function viewGuide() {
        alert('Referral guide will be available soon!');
    }
";

echo renderLayout($title, $content, '', $additionalJS);
?>