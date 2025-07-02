-- Starlink Router Rent Database Schema
-- Version: 2.0.0
-- Database: gainsmax_testtelegram (reverted for compatibility)

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `gainsmax_testtelegram` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gainsmax_testtelegram`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `referral_code` varchar(20) NOT NULL,
  `referred_by` varchar(36) DEFAULT NULL,
  `telegram_id` bigint DEFAULT NULL,
  `telegram_username` varchar(50) DEFAULT NULL,
  `telegram_first_name` varchar(100) DEFAULT NULL,
  `telegram_last_name` varchar(100) DEFAULT NULL,
  `telegram_photo_url` text DEFAULT NULL,
  `balance` decimal(12,2) DEFAULT 0.00,
  `total_earnings` decimal(12,2) DEFAULT 0.00,
  `total_invested` decimal(12,2) DEFAULT 0.00,
  `total_withdrawn` decimal(12,2) DEFAULT 0.00,
  `referral_earnings` decimal(12,2) DEFAULT 0.00,
  `rental_earnings` decimal(12,2) DEFAULT 0.00,
  `investment_earnings` decimal(12,2) DEFAULT 0.00,
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `language` varchar(10) DEFAULT 'en',
  `status` enum('active','suspended','pending','banned') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `telegram_verified` tinyint(1) DEFAULT 0,
  `kyc_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `kyc_documents` json DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `crypto_wallets` json DEFAULT NULL,
  `preferred_crypto` varchar(10) DEFAULT 'BTC',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `referral_code` (`referral_code`),
  UNIQUE KEY `telegram_id` (`telegram_id`),
  KEY `idx_users_referral_code` (`referral_code`),
  KEY `idx_users_telegram_id` (`telegram_id`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_created_at` (`created_at`),
  CONSTRAINT `users_referred_by_fkey` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator','support') DEFAULT 'admin',
  `permissions` json DEFAULT NULL,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admin_users_role` (`role`),
  KEY `idx_admin_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `idx_user_sessions_user_id` (`user_id`),
  KEY `idx_user_sessions_expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin sessions table
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `idx_admin_sessions_admin_id` (`admin_id`),
  KEY `idx_admin_sessions_expires_at` (`expires_at`),
  CONSTRAINT `admin_sessions_admin_id_fkey` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Devices table
CREATE TABLE IF NOT EXISTS `devices` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `device_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(50) DEFAULT 'Starlink Standard',
  `serial_number` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','rented','maintenance','offline','reserved') DEFAULT 'available',
  `daily_rate` decimal(8,2) NOT NULL DEFAULT 15.00,
  `setup_fee` decimal(8,2) DEFAULT 0.00,
  `max_speed_down` int DEFAULT 200,
  `max_speed_up` int DEFAULT 20,
  `uptime_percentage` decimal(5,2) DEFAULT 99.00,
  `total_earnings` decimal(12,2) DEFAULT 0.00,
  `total_rentals` int DEFAULT 0,
  `specifications` json DEFAULT NULL,
  `features` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `warranty_expires` date DEFAULT NULL,
  `maintenance_schedule` varchar(20) DEFAULT 'monthly',
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `idx_devices_device_id` (`device_id`),
  KEY `idx_devices_status` (`status`),
  KEY `idx_devices_location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `user_id` varchar(36) NOT NULL,
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
  `description` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `fee_amount` decimal(12,2) DEFAULT 0.00,
  `net_amount` decimal(12,2) DEFAULT NULL,
  `webhook_received` tinyint(1) DEFAULT 0,
  `webhook_data` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `idx_payments_user_id` (`user_id`),
  KEY `idx_payments_status` (`status`),
  KEY `idx_payments_type` (`type`),
  KEY `idx_payments_created_at` (`created_at`),
  CONSTRAINT `payments_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals table
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `referrer_id` varchar(36) NOT NULL,
  `referred_id` varchar(36) NOT NULL,
  `level` smallint NOT NULL CHECK (`level` IN (1,2,3)),
  `commission_rate` decimal(5,2) NOT NULL,
  `total_commission_earned` decimal(12,2) DEFAULT 0.00,
  `total_referral_volume` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `first_earning_date` date DEFAULT NULL,
  `last_earning_date` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referrals_referrer_id_referred_id_key` (`referrer_id`,`referred_id`),
  KEY `idx_referrals_referrer_id` (`referrer_id`),
  KEY `idx_referrals_referred_id` (`referred_id`),
  KEY `idx_referrals_level` (`level`),
  CONSTRAINT `referrals_referrer_id_fkey` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_referred_id_fkey` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rentals table
CREATE TABLE IF NOT EXISTS `rentals` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `user_id` varchar(36) NOT NULL,
  `device_id` varchar(36) NOT NULL,
  `payment_id` varchar(36) DEFAULT NULL,
  `plan_type` enum('basic','standard','premium','custom') NOT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `rental_duration` int NOT NULL,
  `daily_profit_rate` decimal(5,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `setup_fee` decimal(8,2) DEFAULT 0.00,
  `expected_daily_profit` decimal(8,2) NOT NULL,
  `actual_total_profit` decimal(12,2) DEFAULT 0.00,
  `total_days_active` int DEFAULT 0,
  `performance_bonus` decimal(8,2) DEFAULT 0.00,
  `status` enum('pending','active','completed','cancelled','suspended','expired') DEFAULT 'pending',
  `auto_renew` tinyint(1) DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `actual_start_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `last_profit_date` date DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rentals_user_id` (`user_id`),
  KEY `idx_rentals_device_id` (`device_id`),
  KEY `idx_rentals_status` (`status`),
  KEY `idx_rentals_dates` (`start_date`,`end_date`),
  CONSTRAINT `rentals_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_device_id_fkey` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_payment_id_fkey` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Investments table
CREATE TABLE IF NOT EXISTS `investments` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `user_id` varchar(36) NOT NULL,
  `payment_id` varchar(36) DEFAULT NULL,
  `plan_name` varchar(100) NOT NULL,
  `plan_duration` int NOT NULL,
  `investment_amount` decimal(12,2) NOT NULL,
  `daily_rate` decimal(6,4) NOT NULL,
  `expected_daily_profit` decimal(8,2) NOT NULL,
  `total_earned` decimal(12,2) DEFAULT 0.00,
  `total_days_active` int DEFAULT 0,
  `compound_interest` tinyint(1) DEFAULT 0,
  `auto_reinvest` tinyint(1) DEFAULT 0,
  `reinvest_percentage` decimal(5,2) DEFAULT 0.00,
  `status` enum('pending','active','completed','cancelled','suspended','matured') DEFAULT 'pending',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `actual_start_date` date DEFAULT NULL,
  `maturity_date` date DEFAULT NULL,
  `last_profit_date` date DEFAULT NULL,
  `early_withdrawal_fee` decimal(5,2) DEFAULT 10.00,
  `withdrawal_allowed_after` int DEFAULT 30,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_investments_user_id` (`user_id`),
  KEY `idx_investments_status` (`status`),
  KEY `idx_investments_dates` (`start_date`,`end_date`),
  CONSTRAINT `investments_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investments_payment_id_fkey` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rental earnings table
CREATE TABLE IF NOT EXISTS `rental_earnings` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `rental_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `device_id` varchar(36) NOT NULL,
  `earning_date` date NOT NULL,
  `base_profit_amount` decimal(8,2) NOT NULL,
  `performance_bonus` decimal(8,2) DEFAULT 0.00,
  `total_profit_amount` decimal(8,2) NOT NULL,
  `device_uptime` decimal(5,2) DEFAULT 100.00,
  `performance_factor` decimal(4,3) DEFAULT 1.000,
  `weather_factor` decimal(4,3) DEFAULT 1.000,
  `network_quality` decimal(5,2) DEFAULT 100.00,
  `processed` tinyint(1) DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rental_earnings_rental_id_earning_date_key` (`rental_id`,`earning_date`),
  KEY `idx_rental_earnings_rental_id` (`rental_id`),
  KEY `idx_rental_earnings_user_id` (`user_id`),
  KEY `idx_rental_earnings_earning_date` (`earning_date`),
  CONSTRAINT `rental_earnings_rental_id_fkey` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_earnings_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_earnings_device_id_fkey` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Investment earnings table
CREATE TABLE IF NOT EXISTS `investment_earnings` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `investment_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `earning_date` date NOT NULL,
  `base_amount` decimal(12,2) NOT NULL,
  `daily_rate` decimal(6,4) NOT NULL,
  `profit_amount` decimal(8,2) NOT NULL,
  `compound_amount` decimal(8,2) DEFAULT 0.00,
  `reinvested_amount` decimal(8,2) DEFAULT 0.00,
  `paid_amount` decimal(8,2) NOT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `investment_earnings_investment_id_earning_date_key` (`investment_id`,`earning_date`),
  KEY `idx_investment_earnings_investment_id` (`investment_id`),
  KEY `idx_investment_earnings_user_id` (`user_id`),
  KEY `idx_investment_earnings_earning_date` (`earning_date`),
  CONSTRAINT `investment_earnings_investment_id_fkey` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investment_earnings_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referral earnings table
CREATE TABLE IF NOT EXISTS `referral_earnings` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `referral_id` varchar(36) NOT NULL,
  `referrer_id` varchar(36) NOT NULL,
  `referred_id` varchar(36) NOT NULL,
  `source_type` enum('rental','investment','deposit') NOT NULL,
  `source_id` varchar(36) NOT NULL,
  `level` smallint NOT NULL CHECK (`level` IN (1,2,3)),
  `commission_rate` decimal(5,2) NOT NULL,
  `base_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(8,2) NOT NULL,
  `earning_date` date NOT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral_earnings_referral_id` (`referral_id`),
  KEY `idx_referral_earnings_referrer_id` (`referrer_id`),
  KEY `idx_referral_earnings_earning_date` (`earning_date`),
  CONSTRAINT `referral_earnings_referral_id_fkey` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referral_earnings_referrer_id_fkey` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referral_earnings_referred_id_fkey` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawal requests table
CREATE TABLE IF NOT EXISTS `withdrawal_requests` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `user_id` varchar(36) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `fee_amount` decimal(8,2) DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL,
  `withdrawal_method` enum('crypto','bank_transfer','paypal','binance') NOT NULL,
  `withdrawal_address` text DEFAULT NULL,
  `bank_details` json DEFAULT NULL,
  `status` enum('pending','approved','processing','completed','rejected','cancelled') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `user_notes` text DEFAULT NULL,
  `processed_by` int DEFAULT NULL,
  `transaction_hash` varchar(200) DEFAULT NULL,
  `external_transaction_id` varchar(200) DEFAULT NULL,
  `requested_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_withdrawal_requests_user_id` (`user_id`),
  KEY `idx_withdrawal_requests_status` (`status`),
  CONSTRAINT `withdrawal_requests_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawal_requests_processed_by_fkey` FOREIGN KEY (`processed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json','text') DEFAULT 'string',
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_system_settings_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email notifications table
CREATE TABLE IF NOT EXISTS `email_notifications` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `user_id` varchar(36) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_data` json DEFAULT NULL,
  `status` enum('pending','sent','failed','delivered') DEFAULT 'pending',
  `provider` varchar(50) DEFAULT NULL,
  `provider_message_id` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_notifications_user_id` (`user_id`),
  KEY `idx_email_notifications_status` (`status`),
  CONSTRAINT `email_notifications_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment webhooks table
CREATE TABLE IF NOT EXISTS `payment_webhooks` (
  `id` varchar(36) NOT NULL DEFAULT (UUID()),
  `provider` varchar(50) NOT NULL,
  `webhook_id` varchar(255) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `payment_id` varchar(36) DEFAULT NULL,
  `raw_data` json NOT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `processing_attempts` int DEFAULT 0,
  `last_processing_error` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payment_webhooks_provider` (`provider`),
  KEY `idx_payment_webhooks_processed` (`processed`),
  CONSTRAINT `payment_webhooks_payment_id_fkey` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT IGNORE INTO `admin_users` (`username`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
('admin', 'admin@starlinkrouterrent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW());

-- Insert sample devices
INSERT IGNORE INTO `devices` (`device_id`, `name`, `model`, `location`, `status`, `daily_rate`, `max_speed_down`, `max_speed_up`, `uptime_percentage`, `created_at`) VALUES
('SRR001', 'Starlink Router Alpha', 'Starlink Gen3', 'New York, USA', 'available', 15.00, 200, 20, 99.5, NOW()),
('SRR002', 'Starlink Router Beta', 'Starlink Gen3', 'London, UK', 'available', 18.00, 250, 25, 99.8, NOW()),
('SRR003', 'Starlink Router Gamma', 'Starlink Enterprise', 'Tokyo, Japan', 'available', 25.00, 300, 30, 99.9, NOW()),
('SRR004', 'Starlink Router Delta', 'Starlink Gen3', 'Sydney, Australia', 'available', 20.00, 220, 22, 99.7, NOW()),
('SRR005', 'Starlink Router Epsilon', 'Starlink Enterprise', 'Frankfurt, Germany', 'available', 22.00, 280, 28, 99.6, NOW());

-- Insert default system settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`, `created_at`) VALUES
('site_name', 'Starlink Router Rent', 'string', 'general', 'Site name', 1, NOW()),
('site_url', 'https://starlinkrouterrent.com', 'string', 'general', 'Site URL', 1, NOW()),
('admin_email', 'admin@starlinkrouterrent.com', 'string', 'general', 'Admin email address', 0, NOW()),
('min_deposit', '50', 'number', 'payments', 'Minimum deposit amount', 1, NOW()),
('max_deposit', '10000', 'number', 'payments', 'Maximum deposit amount', 1, NOW()),
('min_withdrawal', '20', 'number', 'payments', 'Minimum withdrawal amount', 1, NOW()),
('withdrawal_fee', '2.0', 'number', 'payments', 'Withdrawal fee percentage', 1, NOW()),
('plisio_api_key', '', 'string', 'payments', 'Plisio.net API key', 0, NOW()),
('plisio_webhook_url', '', 'string', 'payments', 'Plisio webhook URL', 0, NOW()),
('binance_api_key', '', 'string', 'payments', 'Binance API key', 0, NOW()),
('binance_secret', '', 'string', 'payments', 'Binance API secret', 0, NOW()),
('telegram_bot_token', '', 'string', 'telegram', 'Telegram bot token', 0, NOW()),
('email_notifications_enabled', '1', 'boolean', 'email', 'Enable email notifications', 0, NOW()),
('welcome_email_enabled', '1', 'boolean', 'email', 'Enable welcome emails', 0, NOW()),
('deposit_email_enabled', '1', 'boolean', 'email', 'Enable deposit confirmation emails', 0, NOW()),
('withdrawal_email_enabled', '1', 'boolean', 'email', 'Enable withdrawal notification emails', 0, NOW()),
('daily_earnings_email_enabled', '0', 'boolean', 'email', 'Enable daily earnings emails', 0, NOW()),
('referral_email_enabled', '1', 'boolean', 'email', 'Enable referral bonus emails', 0, NOW());

-- Create function for generating referral codes
DROP FUNCTION IF EXISTS generate_referral_code;
CREATE FUNCTION generate_referral_code() 
RETURNS VARCHAR(20) 
READS SQL DATA 
DETERMINISTIC
BEGIN
    DECLARE code VARCHAR(20);
    DECLARE done INT DEFAULT 0;
    
    REPEAT
        SET code = UPPER(SUBSTRING(MD5(RAND()), 1, 10));
        SELECT COUNT(*) INTO done FROM users WHERE referral_code = code;
    UNTIL done = 0 END REPEAT;
    
    RETURN code;
END;

-- Create trigger for automatic referral code generation
DROP TRIGGER IF EXISTS auto_generate_referral_code;
CREATE TRIGGER auto_generate_referral_code
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.referral_code IS NULL OR NEW.referral_code = '' THEN
        SET NEW.referral_code = generate_referral_code();
    END IF;
END;

-- Create stored procedure for referral relationship creation
DROP PROCEDURE IF EXISTS create_referral_relationships;
CREATE PROCEDURE create_referral_relationships(IN user_id VARCHAR(36), IN referred_by VARCHAR(36))
BEGIN
    DECLARE level2_referrer VARCHAR(36) DEFAULT NULL;
    DECLARE level3_referrer VARCHAR(36) DEFAULT NULL;
    
    IF referred_by IS NOT NULL THEN
        -- Level 1 referral
        INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
        VALUES (referred_by, user_id, 1, 7.00, 'active');
        
        -- Level 2 referral
        SELECT referred_by INTO level2_referrer FROM users WHERE id = referred_by;
        IF level2_referrer IS NOT NULL THEN
            INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
            VALUES (level2_referrer, user_id, 2, 5.00, 'active');
            
            -- Level 3 referral
            SELECT referred_by INTO level3_referrer FROM users WHERE id = level2_referrer;
            IF level3_referrer IS NOT NULL THEN
                INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
                VALUES (level3_referrer, user_id, 3, 3.00, 'active');
            END IF;
        END IF;
    END IF;
END;

-- Create trigger for automatic referral relationship creation
DROP TRIGGER IF EXISTS create_referral_relationships_trigger;
CREATE TRIGGER create_referral_relationships_trigger
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    CALL create_referral_relationships(NEW.id, NEW.referred_by);
END;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Show completion message
SELECT 'Starlink Router Rent database schema created successfully!' as message;