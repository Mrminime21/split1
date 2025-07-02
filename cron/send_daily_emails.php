<?php
/**
 * Daily Email Notifications Cron Job
 * Sends daily earnings reports and other scheduled emails
 */

require_once '../includes/database.php';
require_once '../includes/email.php';

$db = Database::getInstance();
$emailService = new EmailService();

try {
    echo "Starting daily email notifications...\n";
    
    // Check if daily earnings emails are enabled
    $emailEnabled = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'daily_earnings_email_enabled'");
    if (!$emailEnabled || $emailEnabled['setting_value'] !== '1') {
        echo "Daily earnings emails are disabled.\n";
        exit;
    }
    
    // Get users who earned money today
    $usersWithEarnings = $db->fetchAll("
        SELECT DISTINCT u.*, 
               (SELECT SUM(total_profit_amount) FROM rental_earnings WHERE user_id = u.id AND earning_date = CURDATE()) as rental_earnings_today,
               (SELECT SUM(profit_amount) FROM investment_earnings WHERE user_id = u.id AND earning_date = CURDATE()) as investment_earnings_today,
               (SELECT SUM(commission_amount) FROM referral_earnings WHERE referrer_id = u.id AND earning_date = CURDATE()) as referral_earnings_today
        FROM users u
        WHERE u.status = 'active' 
        AND u.email_verified = 1
        AND (
            EXISTS (SELECT 1 FROM rental_earnings WHERE user_id = u.id AND earning_date = CURDATE())
            OR EXISTS (SELECT 1 FROM investment_earnings WHERE user_id = u.id AND earning_date = CURDATE())
            OR EXISTS (SELECT 1 FROM referral_earnings WHERE referrer_id = u.id AND earning_date = CURDATE())
        )
    ");
    
    foreach ($usersWithEarnings as $user) {
        try {
            // Get detailed earnings for today
            $earnings = [];
            
            // Rental earnings
            if ($user['rental_earnings_today'] > 0) {
                $earnings[] = [
                    'type' => 'rental',
                    'amount' => $user['rental_earnings_today'],
                    'date' => date('Y-m-d')
                ];
            }
            
            // Investment earnings
            if ($user['investment_earnings_today'] > 0) {
                $earnings[] = [
                    'type' => 'investment',
                    'amount' => $user['investment_earnings_today'],
                    'date' => date('Y-m-d')
                ];
            }
            
            // Referral earnings
            if ($user['referral_earnings_today'] > 0) {
                $earnings[] = [
                    'type' => 'referral',
                    'amount' => $user['referral_earnings_today'],
                    'date' => date('Y-m-d')
                ];
            }
            
            if (!empty($earnings)) {
                $emailService->sendDailyEarnings($user, $earnings);
                echo "Sent daily earnings email to {$user['email']}\n";
                
                // Small delay to avoid overwhelming the SMTP server
                usleep(500000); // 0.5 seconds
            }
            
        } catch (Exception $e) {
            echo "Error sending email to {$user['email']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Daily email notifications completed.\n";
    
} catch (Exception $e) {
    echo "Error in daily email notifications: " . $e->getMessage() . "\n";
}
?>