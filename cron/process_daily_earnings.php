<?php
/**
 * Daily Earnings Processing Cron Job
 * Run this script daily to process investment and rental earnings
 */

require_once '../includes/database.php';
require_once '../includes/email.php';

$db = Database::getInstance();
$emailService = new EmailService();

try {
    echo "Starting daily earnings processing...\n";
    
    // Process investment earnings
    $activeInvestments = $db->fetchAll("
        SELECT * FROM investments 
        WHERE status = 'active' 
        AND actual_start_date <= CURDATE()
        AND end_date >= CURDATE()
    ");
    
    foreach ($activeInvestments as $investment) {
        // Check if earnings already processed for today
        $existingEarning = $db->fetch("
            SELECT id FROM investment_earnings 
            WHERE investment_id = ? AND earning_date = CURDATE()
        ", [$investment['id']]);
        
        if (!$existingEarning) {
            $dailyProfit = $investment['expected_daily_profit'];
            
            // Create earnings record
            $earningData = [
                'investment_id' => $investment['id'],
                'user_id' => $investment['user_id'],
                'earning_date' => date('Y-m-d'),
                'base_amount' => $investment['investment_amount'],
                'daily_rate' => $investment['daily_rate'],
                'profit_amount' => $dailyProfit,
                'paid_amount' => $dailyProfit,
                'processed' => true,
                'processed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('investment_earnings', $earningData);
            
            // Update user balance and total earnings
            $db->query("
                UPDATE users 
                SET balance = balance + ?, 
                    total_earnings = total_earnings + ?,
                    investment_earnings = investment_earnings + ?
                WHERE id = ?
            ", [$dailyProfit, $dailyProfit, $dailyProfit, $investment['user_id']]);
            
            // Update investment total earned
            $db->query("
                UPDATE investments 
                SET total_earned = total_earned + ?,
                    total_days_active = total_days_active + 1,
                    last_profit_date = CURDATE()
                WHERE id = ?
            ", [$dailyProfit, $investment['id']]);
            
            echo "Processed investment earnings for user {$investment['user_id']}: $" . number_format($dailyProfit, 2) . "\n";
        }
    }
    
    // Process rental earnings
    $activeRentals = $db->fetchAll("
        SELECT r.*, d.uptime_percentage 
        FROM rentals r 
        JOIN devices d ON r.device_id = d.id 
        WHERE r.status = 'active' 
        AND r.actual_start_date <= CURDATE()
        AND r.end_date >= CURDATE()
    ");
    
    foreach ($activeRentals as $rental) {
        // Check if earnings already processed for today
        $existingEarning = $db->fetch("
            SELECT id FROM rental_earnings 
            WHERE rental_id = ? AND earning_date = CURDATE()
        ", [$rental['id']]);
        
        if (!$existingEarning) {
            $baseProfit = $rental['expected_daily_profit'];
            $performanceFactor = $rental['uptime_percentage'] / 100;
            $totalProfit = $baseProfit * $performanceFactor;
            
            // Create earnings record
            $earningData = [
                'rental_id' => $rental['id'],
                'user_id' => $rental['user_id'],
                'device_id' => $rental['device_id'],
                'earning_date' => date('Y-m-d'),
                'base_profit_amount' => $baseProfit,
                'total_profit_amount' => $totalProfit,
                'device_uptime' => $rental['uptime_percentage'],
                'performance_factor' => $performanceFactor,
                'processed' => true,
                'processed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('rental_earnings', $earningData);
            
            // Update user balance and total earnings
            $db->query("
                UPDATE users 
                SET balance = balance + ?, 
                    total_earnings = total_earnings + ?,
                    rental_earnings = rental_earnings + ?
                WHERE id = ?
            ", [$totalProfit, $totalProfit, $totalProfit, $rental['user_id']]);
            
            // Update rental total profit
            $db->query("
                UPDATE rentals 
                SET actual_total_profit = actual_total_profit + ?,
                    total_days_active = total_days_active + 1,
                    last_profit_date = CURDATE()
                WHERE id = ?
            ", [$totalProfit, $rental['id']]);
            
            echo "Processed rental earnings for user {$rental['user_id']}: $" . number_format($totalProfit, 2) . "\n";
        }
    }
    
    // Process referral commissions
    $todayEarnings = $db->fetchAll("
        SELECT ie.*, u.referred_by, 'investment' as earning_type
        FROM investment_earnings ie 
        JOIN users u ON ie.user_id = u.id 
        WHERE ie.earning_date = CURDATE() 
        AND ie.processed = 1 
        AND u.referred_by IS NOT NULL
        
        UNION ALL
        
        SELECT re.user_id, re.total_profit_amount as profit_amount, u.referred_by, 'rental' as earning_type
        FROM rental_earnings re 
        JOIN users u ON re.user_id = u.id 
        WHERE re.earning_date = CURDATE() 
        AND re.processed = 1 
        AND u.referred_by IS NOT NULL
    ");
    
    foreach ($todayEarnings as $earning) {
        // Process referral commissions for 3 levels
        $referrals = $db->fetchAll("
            SELECT r.*, u.username as referred_username 
            FROM referrals r
            JOIN users u ON r.referred_id = u.id
            WHERE r.referred_id = ? 
            AND r.status = 'active' 
            ORDER BY r.level ASC
        ", [$earning['user_id']]);
        
        foreach ($referrals as $referral) {
            $commissionAmount = ($earning['profit_amount'] * $referral['commission_rate']) / 100;
            
            // Check if commission already processed
            $existingCommission = $db->fetch("
                SELECT id FROM referral_earnings 
                WHERE referral_id = ? 
                AND source_type = ? 
                AND earning_date = CURDATE()
            ", [$referral['id'], $earning['earning_type']]);
            
            if (!$existingCommission) {
                // Create referral earning record
                $commissionData = [
                    'referral_id' => $referral['id'],
                    'referrer_id' => $referral['referrer_id'],
                    'referred_id' => $referral['referred_id'],
                    'source_type' => $earning['earning_type'],
                    'source_id' => $earning['investment_id'] ?? $earning['rental_id'],
                    'level' => $referral['level'],
                    'commission_rate' => $referral['commission_rate'],
                    'base_amount' => $earning['profit_amount'],
                    'commission_amount' => $commissionAmount,
                    'earning_date' => date('Y-m-d'),
                    'processed' => true,
                    'processed_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('referral_earnings', $commissionData);
                
                // Update referrer balance
                $db->query("
                    UPDATE users 
                    SET balance = balance + ?, 
                        total_earnings = total_earnings + ?,
                        referral_earnings = referral_earnings + ?
                    WHERE id = ?
                ", [$commissionAmount, $commissionAmount, $commissionAmount, $referral['referrer_id']]);
                
                // Send referral bonus email
                try {
                    $referrer = $db->fetch("SELECT * FROM users WHERE id = ?", [$referral['referrer_id']]);
                    if ($referrer) {
                        $referralEarning = array_merge($commissionData, ['referred_username' => $referral['referred_username']]);
                        $emailService->sendReferralBonus($referrer, $referralEarning);
                    }
                } catch (Exception $e) {
                    error_log('Failed to send referral bonus email: ' . $e->getMessage());
                }
                
                echo "Processed referral commission for user {$referral['referrer_id']} (Level {$referral['level']}): $" . number_format($commissionAmount, 2) . "\n";
            }
        }
    }
    
    echo "Daily earnings processing completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error processing daily earnings: " . $e->getMessage() . "\n";
}
?>