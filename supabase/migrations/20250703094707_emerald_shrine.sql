-- Starlink Router Rent - Complete MySQL Database
-- Version: 2.0.0
-- Compatible with MySQL 8.0+
-- Laravel Application Database

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `starlink_router_rent` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `starlink_router_rent`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `referral_code` varchar(20) NOT NULL,
  `referred_by` char(36) DEFAULT NULL,
  `telegram_id` bigint DEFAULT NULL,
  `telegram_username` varchar(50) DEFAULT NULL,
  `telegram_first_name` varchar(100) DEFAULT NULL,
  `telegram_last_name` varchar(100) DEFAULT NULL,
  `telegram_photo_url` text,
  `balance` decimal(12,2) DEFAULT '0.00',
  `total_earnings` decimal(12,2) DEFAULT '0.00',
  `total_invested` decimal(12,2) DEFAULT '0.00',
  `total_withdrawn` decimal(12,2) DEFAULT '0.00',
  `referral_earnings` decimal(12,2) DEFAULT '0.00',
  `rental_earnings` decimal(12,2) DEFAULT '0.00',
  `investment_earnings` decimal(12,2) DEFAULT '0.00',
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `language` varchar(10) DEFAULT 'en',
  `status` enum('active','suspended','pending','banned') DEFAULT 'active',
  `telegram_verified` tinyint(1) DEFAULT '0',
  `kyc_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `kyc_documents` json DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `crypto_wallets` json DEFAULT NULL,
  `preferred_crypto` varchar(10) DEFAULT 'BTC',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  UNIQUE KEY `users_telegram_id_unique` (`telegram_id`),
  KEY `users_referred_by_foreign` (`referred_by`),
  KEY `users_referral_code_index` (`referral_code`),
  KEY `users_telegram_id_index` (`telegram_id`),
  KEY `users_status_index` (`status`),
  KEY `users_created_at_index` (`created_at`),
  CONSTRAINT `users_referred_by_foreign` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `admin_users`
-- --------------------------------------------------------

CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator','support') DEFAULT 'admin',
  `permissions` json DEFAULT NULL,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_username_unique` (`username`),
  UNIQUE KEY `admin_users_email_unique` (`email`),
  KEY `admin_users_role_index` (`role`),
  KEY `admin_users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `devices`
-- --------------------------------------------------------

CREATE TABLE `devices` (
  `id` char(36) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(50) DEFAULT 'Starlink Standard',
  `serial_number` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','rented','maintenance','offline','reserved') DEFAULT 'available',
  `daily_rate` decimal(8,2) DEFAULT '15.00',
  `setup_fee` decimal(8,2) DEFAULT '0.00',
  `max_speed_down` int DEFAULT '200',
  `max_speed_up` int DEFAULT '20',
  `uptime_percentage` decimal(5,2) DEFAULT '99.00',
  `total_earnings` decimal(12,2) DEFAULT '0.00',
  `total_rentals` int DEFAULT '0',
  `specifications` json DEFAULT NULL,
  `features` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `warranty_expires` date DEFAULT NULL,
  `maintenance_schedule` varchar(20) DEFAULT 'monthly',
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devices_device_id_unique` (`device_id`),
  UNIQUE KEY `devices_serial_number_unique` (`serial_number`),
  KEY `devices_device_id_index` (`device_id`),
  KEY `devices_status_index` (`status`),
  KEY `devices_location_index` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------

CREATE TABLE `payments` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `external_id` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `crypto_currency` varchar(20) DEFAULT NULL,
  `crypto_amount` decimal(20,8) DEFAULT NULL,
  `exchange_rate` decimal(15,8) DEFAULT NULL,
  `payment_method` enum('crypto','binance','card','bank_transfer','balance','manual') NOT NULL,
  `payment_provider` varchar(50) DEFAULT NULL,
  `provider_transaction_id` varchar(200) DEFAULT NULL,
  `provider_response` json DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled','refunded','expired') DEFAULT 'pending',
  `type` enum('rental','investment','withdrawal','referral_bonus','deposit','fee','refund') NOT NULL,
  `description` text,
  `metadata` json DEFAULT NULL,
  `fee_amount` decimal(12,2) DEFAULT '0.00',
  `net_amount` decimal(12,2) DEFAULT NULL,
  `webhook_received` tinyint(1) DEFAULT '0',
  `webhook_data` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_user_id_index` (`user_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_type_index` (`type`),
  KEY `payments_created_at_index` (`created_at`),
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `referrals`
-- --------------------------------------------------------

CREATE TABLE `referrals` (
  `id` char(36) NOT NULL,
  `referrer_id` char(36) NOT NULL,
  `referred_id` char(36) NOT NULL,
  `level` tinyint NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `total_commission_earned` decimal(12,2) DEFAULT '0.00',
  `total_referral_volume` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `first_earning_date` date DEFAULT NULL,
  `last_earning_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referrals_referrer_id_referred_id_unique` (`referrer_id`,`referred_id`),
  KEY `referrals_referred_id_foreign` (`referred_id`),
  KEY `referrals_referrer_id_index` (`referrer_id`),
  KEY `referrals_referred_id_index` (`referred_id`),
  KEY `referrals_level_index` (`level`),
  CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_chk_1` CHECK ((`level` in (1,2,3)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `rentals`
-- --------------------------------------------------------

CREATE TABLE `rentals` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `device_id` char(36) NOT NULL,
  `payment_id` char(36) DEFAULT NULL,
  `plan_type` enum('basic','standard','premium','custom') NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `rental_duration` int NOT NULL,
  `daily_profit_rate` decimal(5,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `setup_fee` decimal(8,2) DEFAULT '0.00',
  `expected_daily_profit` decimal(8,2) NOT NULL,
  `actual_total_profit` decimal(12,2) DEFAULT '0.00',
  `total_days_active` int DEFAULT '0',
  `performance_bonus` decimal(8,2) DEFAULT '0.00',
  `status` enum('pending','active','completed','cancelled','suspended','expired') DEFAULT 'pending',
  `auto_renew` tinyint(1) DEFAULT '0',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `last_profit_date` date DEFAULT NULL,
  `cancellation_reason` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rentals_user_id_foreign` (`user_id`),
  KEY `rentals_device_id_foreign` (`device_id`),
  KEY `rentals_payment_id_foreign` (`payment_id`),
  KEY `rentals_user_id_index` (`user_id`),
  KEY `rentals_device_id_index` (`device_id`),
  KEY `rentals_status_index` (`status`),
  KEY `rentals_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `rentals_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rentals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `investments`
-- --------------------------------------------------------

CREATE TABLE `investments` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `payment_id` char(36) DEFAULT NULL,
  `plan_name` varchar(100) NOT NULL,
  `plan_duration` int NOT NULL,
  `investment_amount` decimal(12,2) NOT NULL,
  `daily_rate` decimal(6,4) NOT NULL,
  `expected_daily_profit` decimal(8,2) NOT NULL,
  `total_earned` decimal(12,2) DEFAULT '0.00',
  `total_days_active` int DEFAULT '0',
  `compound_interest` tinyint(1) DEFAULT '0',
  `auto_reinvest` tinyint(1) DEFAULT '0',
  `reinvest_percentage` decimal(5,2) DEFAULT '0.00',
  `status` enum('pending','active','completed','cancelled','suspended','matured') DEFAULT 'pending',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `actual_start_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `last_profit_date` date DEFAULT NULL,
  `early_withdrawal_fee` decimal(5,2) DEFAULT '10.00',
  `withdrawal_allowed_after` int DEFAULT '30',
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investments_user_id_foreign` (`user_id`),
  KEY `investments_payment_id_foreign` (`payment_id`),
  KEY `investments_user_id_index` (`user_id`),
  KEY `investments_status_index` (`status`),
  KEY `investments_start_date_end_date_index` (`start_date`,`end_date`),
  CONSTRAINT `investments_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `investments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `withdrawal_requests`
-- --------------------------------------------------------

CREATE TABLE `withdrawal_requests` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `fee_amount` decimal(8,2) DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL,
  `withdrawal_method` enum('crypto','bank_transfer','paypal','binance') NOT NULL,
  `withdrawal_address` text,
  `bank_details` json DEFAULT NULL,
  `status` enum('pending','approved','processing','completed','rejected','cancelled') DEFAULT 'pending',
  `admin_notes` text,
  `user_notes` text,
  `processed_by` bigint unsigned DEFAULT NULL,
  `transaction_hash` varchar(200) DEFAULT NULL,
  `external_transaction_id` varchar(200) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_requests_user_id_foreign` (`user_id`),
  KEY `withdrawal_requests_processed_by_foreign` (`processed_by`),
  KEY `withdrawal_requests_user_id_index` (`user_id`),
  KEY `withdrawal_requests_status_index` (`status`),
  CONSTRAINT `withdrawal_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `withdrawal_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------

CREATE TABLE `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json','text') DEFAULT 'string',
  `category` varchar(50) DEFAULT 'general',
  `description` text,
  `is_public` tinyint(1) DEFAULT '0',
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_setting_key_unique` (`setting_key`),
  KEY `system_settings_category_index` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `personal_access_tokens`
-- --------------------------------------------------------

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert sample data
-- --------------------------------------------------------

-- Insert migrations
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_01_01_000001_create_users_table', 1),
(6, '2024_01_01_000002_create_admin_users_table', 1),
(7, '2024_01_01_000003_create_devices_table', 1),
(8, '2024_01_01_000004_create_payments_table', 1),
(9, '2024_01_01_000005_create_referrals_table', 1),
(10, '2024_01_01_000006_create_rentals_table', 1),
(11, '2024_01_01_000007_create_investments_table', 1),
(12, '2024_01_01_000008_create_withdrawal_requests_table', 1),
(13, '2024_01_01_000009_create_system_settings_table', 1);

-- Insert default admin user
INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@starlinkrouterrent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW(), NOW());

-- Insert sample devices
INSERT INTO `devices` (`id`, `device_id`, `name`, `model`, `location`, `status`, `daily_rate`, `max_speed_down`, `max_speed_up`, `uptime_percentage`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'SRR001', 'Starlink Router Alpha', 'Starlink Gen3', 'New York, USA', 'available', 15.00, 200, 20, 99.50, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440002', 'SRR002', 'Starlink Router Beta', 'Starlink Gen3', 'London, UK', 'available', 18.00, 250, 25, 99.80, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440003', 'SRR003', 'Starlink Router Gamma', 'Starlink Enterprise', 'Tokyo, Japan', 'available', 25.00, 300, 30, 99.90, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440004', 'SRR004', 'Starlink Router Delta', 'Starlink Gen3', 'Sydney, Australia', 'available', 20.00, 220, 22, 99.70, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440005', 'SRR005', 'Starlink Router Epsilon', 'Starlink Enterprise', 'Frankfurt, Germany', 'available', 22.00, 280, 28, 99.60, NOW(), NOW());

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
('site_name', 'Starlink Router Rent', 'string', 'general', 'Site name', 1, NOW(), NOW()),
('site_url', 'https://starlinkrouterrent.com', 'string', 'general', 'Site URL', 1, NOW(), NOW()),
('admin_email', 'admin@starlinkrouterrent.com', 'string', 'general', 'Admin email address', 0, NOW(), NOW()),
('min_deposit', '50', 'number', 'payments', 'Minimum deposit amount', 1, NOW(), NOW()),
('max_deposit', '10000', 'number', 'payments', 'Maximum deposit amount', 1, NOW(), NOW()),
('min_withdrawal', '20', 'number', 'payments', 'Minimum withdrawal amount', 1, NOW(), NOW()),
('withdrawal_fee', '2.0', 'number', 'payments', 'Withdrawal fee percentage', 1, NOW(), NOW()),
('plisio_api_key', '', 'string', 'payments', 'Plisio.net API key', 0, NOW(), NOW()),
('binance_api_key', '', 'string', 'payments', 'Binance API key', 0, NOW(), NOW()),
('telegram_bot_token', '', 'string', 'telegram', 'Telegram bot token', 0, NOW(), NOW()),
('email_notifications_enabled', '1', 'boolean', 'email', 'Enable email notifications', 0, NOW(), NOW()),
('welcome_email_enabled', '1', 'boolean', 'email', 'Enable welcome emails', 0, NOW(), NOW()),
('deposit_email_enabled', '1', 'boolean', 'email', 'Enable deposit confirmation emails', 0, NOW(), NOW()),
('withdrawal_email_enabled', '1', 'boolean', 'email', 'Enable withdrawal notification emails', 0, NOW(), NOW()),
('daily_earnings_email_enabled', '0', 'boolean', 'email', 'Enable daily earnings emails', 0, NOW(), NOW()),
('referral_email_enabled', '1', 'boolean', 'email', 'Enable referral bonus emails', 0, NOW(), NOW()),
('referral_level_1_rate', '7.00', 'number', 'referrals', 'Level 1 referral commission rate', 0, NOW(), NOW()),
('referral_level_2_rate', '5.00', 'number', 'referrals', 'Level 2 referral commission rate', 0, NOW(), NOW()),
('referral_level_3_rate', '3.00', 'number', 'referrals', 'Level 3 referral commission rate', 0, NOW(), NOW());

-- Insert sample demo user
INSERT INTO `users` (`id`, `username`, `email`, `password`, `referral_code`, `balance`, `total_earnings`, `total_invested`, `status`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440100', 'demo_user', 'demo@starlinkrouterrent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DEMO123456', 1250.00, 850.00, 2000.00, 'active', NOW(), NOW());

-- --------------------------------------------------------
-- Create triggers and functions
-- --------------------------------------------------------

DELIMITER $$

-- Trigger to generate referral code if not provided
CREATE TRIGGER `users_before_insert` BEFORE INSERT ON `users`
FOR EACH ROW
BEGIN
    IF NEW.referral_code IS NULL OR NEW.referral_code = '' THEN
        SET NEW.referral_code = UPPER(SUBSTRING(MD5(CONCAT(NEW.username, UNIX_TIMESTAMP(), RAND())), 1, 10));
    END IF;
    
    -- Set UUID if not provided
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

-- Trigger to create referral relationships after user creation
CREATE TRIGGER `users_after_insert` AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    DECLARE level2_referrer CHAR(36);
    DECLARE level3_referrer CHAR(36);
    
    IF NEW.referred_by IS NOT NULL THEN
        -- Level 1 referral
        INSERT INTO referrals (id, referrer_id, referred_id, level, commission_rate, status, created_at, updated_at) 
        VALUES (UUID(), NEW.referred_by, NEW.id, 1, 7.00, 'active', NOW(), NOW());
        
        -- Level 2 referral
        SELECT referred_by INTO level2_referrer FROM users WHERE id = NEW.referred_by;
        IF level2_referrer IS NOT NULL THEN
            INSERT INTO referrals (id, referrer_id, referred_id, level, commission_rate, status, created_at, updated_at) 
            VALUES (UUID(), level2_referrer, NEW.id, 2, 5.00, 'active', NOW(), NOW());
            
            -- Level 3 referral
            SELECT referred_by INTO level3_referrer FROM users WHERE id = level2_referrer;
            IF level3_referrer IS NOT NULL THEN
                INSERT INTO referrals (id, referrer_id, referred_id, level, commission_rate, status, created_at, updated_at) 
                VALUES (UUID(), level3_referrer, NEW.id, 3, 3.00, 'active', NOW(), NOW());
            END IF;
        END IF;
    END IF;
END$$

-- Trigger to set UUID for other tables
CREATE TRIGGER `devices_before_insert` BEFORE INSERT ON `devices`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

CREATE TRIGGER `payments_before_insert` BEFORE INSERT ON `payments`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

CREATE TRIGGER `referrals_before_insert` BEFORE INSERT ON `referrals`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

CREATE TRIGGER `rentals_before_insert` BEFORE INSERT ON `rentals`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

CREATE TRIGGER `investments_before_insert` BEFORE INSERT ON `investments`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

CREATE TRIGGER `withdrawal_requests_before_insert` BEFORE INSERT ON `withdrawal_requests`
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        SET NEW.id = UUID();
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Create indexes for better performance
-- --------------------------------------------------------

-- Additional indexes for optimization
CREATE INDEX idx_users_email_status ON users(email, status);
CREATE INDEX idx_devices_status_location ON devices(status, location);
CREATE INDEX idx_payments_user_status_type ON payments(user_id, status, type);
CREATE INDEX idx_rentals_user_status_dates ON rentals(user_id, status, start_date, end_date);
CREATE INDEX idx_investments_user_status_dates ON investments(user_id, status, start_date, end_date);

-- --------------------------------------------------------
-- Enable foreign key checks and commit
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- --------------------------------------------------------
-- Database setup complete
-- --------------------------------------------------------

-- Show completion message
SELECT 'Starlink Router Rent database created successfully!' as message,
       'Default admin credentials: admin@starlinkrouterrent.com / admin123' as admin_info,
       'Demo user credentials: demo@starlinkrouterrent.com / admin123' as demo_info;