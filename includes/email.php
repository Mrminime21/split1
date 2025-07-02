<?php
/**
 * Email Service with SMTP Support
 * Handles all email communications with beautiful templates
 */

class EmailService {
    private $db;
    private $config;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    private function loadConfig() {
        // Get SMTP settings from database
        $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'email_%'");
        
        $config = [];
        foreach ($settings as $setting) {
            $config[$setting['setting_key']] = $setting['setting_value'];
        }

        $this->smtpHost = $config['smtp_host'] ?? 'smtp.gmail.com';
        $this->smtpPort = intval($config['smtp_port'] ?? 587);
        $this->smtpUsername = $config['smtp_username'] ?? '';
        $this->smtpPassword = $config['smtp_password'] ?? '';
        $this->smtpSecure = $config['smtp_secure'] ?? 'tls';
        $this->fromEmail = $config['email_from'] ?? 'noreply@starlink-rent.com';
        $this->fromName = $config['email_from_name'] ?? 'Starlink Rent';
    }

    /**
     * Send email using SMTP
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody = null) {
        try {
            // Create email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: Starlink Rent System'
            ];

            // Use PHP's mail function with SMTP configuration
            if ($this->smtpHost && $this->smtpUsername) {
                return $this->sendSMTP($to, $subject, $htmlBody, $headers);
            } else {
                // Fallback to PHP mail()
                return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            }

        } catch (Exception $e) {
            error_log('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email via SMTP
     */
    private function sendSMTP($to, $subject, $body, $headers) {
        $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
        
        if (!$socket) {
            throw new Exception("SMTP connection failed: $errstr ($errno)");
        }

        // SMTP conversation
        $this->smtpCommand($socket, null, '220'); // Welcome message
        $this->smtpCommand($socket, 'EHLO ' . $_SERVER['HTTP_HOST'], '250');
        
        if ($this->smtpSecure === 'tls') {
            $this->smtpCommand($socket, 'STARTTLS', '220');
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->smtpCommand($socket, 'EHLO ' . $_SERVER['HTTP_HOST'], '250');
        }

        if ($this->smtpUsername) {
            $this->smtpCommand($socket, 'AUTH LOGIN', '334');
            $this->smtpCommand($socket, base64_encode($this->smtpUsername), '334');
            $this->smtpCommand($socket, base64_encode($this->smtpPassword), '235');
        }

        $this->smtpCommand($socket, 'MAIL FROM: <' . $this->fromEmail . '>', '250');
        $this->smtpCommand($socket, 'RCPT TO: <' . $to . '>', '250');
        $this->smtpCommand($socket, 'DATA', '354');

        // Send headers and body
        fwrite($socket, implode("\r\n", $headers) . "\r\n");
        fwrite($socket, "Subject: $subject\r\n");
        fwrite($socket, "\r\n");
        fwrite($socket, $body . "\r\n");
        fwrite($socket, ".\r\n");

        $this->smtpCommand($socket, null, '250'); // Data accepted
        $this->smtpCommand($socket, 'QUIT', '221');

        fclose($socket);
        return true;
    }

    private function smtpCommand($socket, $command, $expectedCode) {
        if ($command) {
            fwrite($socket, $command . "\r\n");
        }
        
        $response = fgets($socket, 512);
        $code = substr($response, 0, 3);
        
        if ($code !== $expectedCode) {
            throw new Exception("SMTP Error: Expected $expectedCode, got $code - $response");
        }
        
        return $response;
    }

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail($user, $referralCode = null) {
        $template = $this->getTemplate('welcome');
        $variables = [
            'user_name' => $user['username'],
            'user_email' => $user['email'],
            'referral_code' => $user['referral_code'],
            'login_url' => $this->getBaseUrl() . '/login',
            'dashboard_url' => $this->getBaseUrl() . '/dashboard',
            'referrer_bonus' => $referralCode ? '$10 referral bonus' : ''
        ];

        $subject = 'Welcome to Starlink Rent - Start Earning Today!';
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'welcome');
    }

    /**
     * Send deposit confirmation email
     */
    public function sendDepositConfirmation($user, $payment) {
        $template = $this->getTemplate('deposit_confirmation');
        $variables = [
            'user_name' => $user['username'],
            'amount' => number_format($payment['amount'], 2),
            'currency' => strtoupper($payment['crypto_currency'] ?? 'USD'),
            'transaction_id' => $payment['transaction_id'],
            'payment_method' => ucfirst($payment['payment_method']),
            'status' => ucfirst($payment['status']),
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        $subject = 'Deposit Confirmation - $' . number_format($payment['amount'], 2) . ' Received';
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'deposit_confirmation');
    }

    /**
     * Send withdrawal notification email
     */
    public function sendWithdrawalNotification($user, $withdrawal) {
        $template = $this->getTemplate('withdrawal_notification');
        $variables = [
            'user_name' => $user['username'],
            'amount' => number_format($withdrawal['amount'], 2),
            'fee' => number_format($withdrawal['fee_amount'], 2),
            'net_amount' => number_format($withdrawal['net_amount'], 2),
            'method' => ucfirst($withdrawal['withdrawal_method']),
            'status' => ucfirst($withdrawal['status']),
            'processing_time' => $this->getProcessingTime($withdrawal['withdrawal_method']),
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        $subject = 'Withdrawal Request - $' . number_format($withdrawal['amount'], 2);
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'withdrawal_notification');
    }

    /**
     * Send daily earnings notification
     */
    public function sendDailyEarnings($user, $earnings) {
        $template = $this->getTemplate('daily_earnings');
        $totalEarnings = array_sum(array_column($earnings, 'amount'));
        
        $variables = [
            'user_name' => $user['username'],
            'total_earnings' => number_format($totalEarnings, 2),
            'earnings_count' => count($earnings),
            'new_balance' => number_format($user['balance'], 2),
            'earnings_list' => $this->formatEarningsList($earnings),
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        $subject = 'Daily Earnings Report - $' . number_format($totalEarnings, 2) . ' Earned';
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'daily_earnings');
    }

    /**
     * Send referral bonus notification
     */
    public function sendReferralBonus($user, $referralEarning) {
        $template = $this->getTemplate('referral_bonus');
        $variables = [
            'user_name' => $user['username'],
            'bonus_amount' => number_format($referralEarning['commission_amount'], 2),
            'referral_level' => $referralEarning['level'],
            'commission_rate' => $referralEarning['commission_rate'],
            'referred_user' => $referralEarning['referred_username'] ?? 'User',
            'total_referral_earnings' => number_format($user['referral_earnings'], 2),
            'referrals_url' => $this->getBaseUrl() . '/referrals'
        ];

        $subject = 'Referral Bonus - $' . number_format($referralEarning['commission_amount'], 2) . ' Earned';
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'referral_bonus');
    }

    /**
     * Send investment confirmation email
     */
    public function sendInvestmentConfirmation($user, $investment) {
        $template = $this->getTemplate('investment_confirmation');
        $variables = [
            'user_name' => $user['username'],
            'plan_name' => $investment['plan_name'],
            'investment_amount' => number_format($investment['investment_amount'], 2),
            'daily_profit' => number_format($investment['expected_daily_profit'], 2),
            'daily_rate' => $investment['daily_rate'],
            'duration' => $investment['plan_duration'],
            'end_date' => date('M j, Y', strtotime($investment['end_date'])),
            'total_expected' => number_format($investment['expected_daily_profit'] * $investment['plan_duration'], 2),
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        $subject = 'Investment Confirmed - ' . $investment['plan_name'];
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'investment_confirmation');
    }

    /**
     * Send rental activation email
     */
    public function sendRentalActivation($user, $rental, $device) {
        $template = $this->getTemplate('rental_activation');
        $variables = [
            'user_name' => $user['username'],
            'device_name' => $device['name'],
            'device_location' => $device['location'],
            'plan_type' => ucfirst($rental['plan_type']),
            'daily_profit' => number_format($rental['expected_daily_profit'], 2),
            'rental_duration' => $rental['rental_duration'],
            'total_cost' => number_format($rental['total_cost'], 2),
            'end_date' => date('M j, Y', strtotime($rental['end_date'])),
            'dashboard_url' => $this->getBaseUrl() . '/dashboard'
        ];

        $subject = 'Device Rental Activated - ' . $device['name'];
        $body = $this->processTemplate($template, $variables);

        return $this->sendAndLog($user['email'], $subject, $body, $user['id'], 'rental_activation');
    }

    /**
     * Get email template
     */
    private function getTemplate($templateName) {
        $templatePath = __DIR__ . '/../templates/email/' . $templateName . '.html';
        
        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }
        
        // Return basic template if file doesn't exist
        return $this->getBasicTemplate();
    }

    /**
     * Process template with variables
     */
    private function processTemplate($template, $variables) {
        // Add common variables
        $variables['site_name'] = 'Starlink Rent';
        $variables['site_url'] = $this->getBaseUrl();
        $variables['current_year'] = date('Y');
        $variables['support_email'] = 'support@starlink-rent.com';
        $variables['unsubscribe_url'] = $this->getBaseUrl() . '/unsubscribe';

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    /**
     * Send email and log to database
     */
    private function sendAndLog($email, $subject, $body, $userId = null, $template = null) {
        $success = $this->sendEmail($email, $subject, $body);
        
        // Log email to database
        $this->db->insert('email_notifications', [
            'user_id' => $userId,
            'email' => $email,
            'subject' => $subject,
            'template_name' => $template ?? 'custom',
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => $success ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $success;
    }

    /**
     * Get base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Get processing time for withdrawal method
     */
    private function getProcessingTime($method) {
        $times = [
            'crypto' => '2-6 hours',
            'binance' => '1-2 hours',
            'bank_transfer' => '1-3 business days',
            'paypal' => '24-48 hours'
        ];
        
        return $times[$method] ?? '24-48 hours';
    }

    /**
     * Format earnings list for email
     */
    private function formatEarningsList($earnings) {
        $html = '<ul style="margin: 0; padding: 0; list-style: none;">';
        
        foreach ($earnings as $earning) {
            $html .= '<li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">';
            $html .= '<strong>$' . number_format($earning['amount'], 2) . '</strong> ';
            $html .= 'from ' . ucfirst($earning['type']) . ' ';
            $html .= '<span style="color: #6b7280;">(' . date('M j', strtotime($earning['date'])) . ')</span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        return $html;
    }

    /**
     * Basic email template fallback
     */
    private function getBasicTemplate() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{site_name}}</title>
        </head>
        <body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f3f4f6;">
            <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #3b82f6, #06b6d4); padding: 20px; text-align: center;">
                    <h1 style="color: white; margin: 0;">{{site_name}}</h1>
                </div>
                <div style="padding: 30px;">
                    {{content}}
                </div>
                <div style="background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;">
                    <p>&copy; {{current_year}} {{site_name}}. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
?>