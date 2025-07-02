<?php
/**
 * Authentication and Authorization
 */

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($email, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s'), 'ip_address' => $_SERVER['REMOTE_ADDR']], 
                'id = ?', 
                [$user['id']]
            );
            
            return true;
        }
        return false;
    }

    public function register($data) {
        // Check if user exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? OR username = ?", 
            [$data['email'], $data['username']]);
        
        if ($existing) {
            throw new Exception("User already exists");
        }

        // Generate referral code
        $referralCode = $this->generateReferralCode();
        
        // Handle referral
        $referredBy = null;
        if (!empty($data['referral_code'])) {
            $referrer = $this->db->fetch("SELECT id FROM users WHERE referral_code = ?", [$data['referral_code']]);
            if ($referrer) {
                $referredBy = $referrer['id'];
            }
        }

        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'referral_code' => $referralCode,
            'referred_by' => $referredBy,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $userId = $this->db->insert('users', $userData);
        
        // Create referral relationships
        $this->createReferralRelationships($userId, $referredBy);
        
        // Send welcome email
        try {
            require_once 'email.php';
            $emailService = new EmailService();
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            $emailService->sendWelcomeEmail($user, $data['referral_code']);
        } catch (Exception $e) {
            error_log('Failed to send welcome email: ' . $e->getMessage());
        }
        
        return $userId;
    }

    private function generateReferralCode() {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 10));
            $exists = $this->db->fetch("SELECT id FROM users WHERE referral_code = ?", [$code]);
        } while ($exists);
        
        return $code;
    }

    private function createReferralRelationships($userId, $referredBy) {
        if (!$referredBy) return;

        // Level 1
        $this->db->insert('referrals', [
            'referrer_id' => $referredBy,
            'referred_id' => $userId,
            'level' => 1,
            'commission_rate' => 7.00,
            'status' => 'active'
        ]);

        // Level 2
        $level2 = $this->db->fetch("SELECT referred_by FROM users WHERE id = ?", [$referredBy]);
        if ($level2 && $level2['referred_by']) {
            $this->db->insert('referrals', [
                'referrer_id' => $level2['referred_by'],
                'referred_id' => $userId,
                'level' => 2,
                'commission_rate' => 5.00,
                'status' => 'active'
            ]);

            // Level 3
            $level3 = $this->db->fetch("SELECT referred_by FROM users WHERE id = ?", [$level2['referred_by']]);
            if ($level3 && $level3['referred_by']) {
                $this->db->insert('referrals', [
                    'referrer_id' => $level3['referred_by'],
                    'referred_id' => $userId,
                    'level' => 3,
                    'commission_rate' => 3.00,
                    'status' => 'active'
                ]);
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function adminLogin($username, $password) {
        $admin = $this->db->fetch(
            "SELECT * FROM admin_users WHERE username = ? AND status = 'active'",
            [$username]
        );

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Update last login
            $this->db->update('admin_users', 
                ['last_login' => date('Y-m-d H:i:s'), 'ip_address' => $_SERVER['REMOTE_ADDR']], 
                'id = ?', 
                [$admin['id']]
            );
            
            return true;
        }
        return false;
    }

    public function isAdmin() {
        return isset($_SESSION['admin_id']);
    }

    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: /admin/login');
            exit;
        }
    }
}
?>