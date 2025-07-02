-- GainsMax Test Telegram Database Schema
-- Version: 2.0.0
-- Database: gainsmax_testtelegram

-- Create database
CREATE DATABASE IF NOT EXISTS `gainsmax_testtelegram` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gainsmax_testtelegram`;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Create custom types (enums)
-- User status enum
CREATE TABLE IF NOT EXISTS user_status_enum (
    value ENUM('active', 'suspended', 'pending', 'banned') PRIMARY KEY
);

-- Admin role enum  
CREATE TABLE IF NOT EXISTS admin_role_enum (
    value ENUM('super_admin', 'admin', 'moderator', 'support') PRIMARY KEY
);

-- Admin status enum
CREATE TABLE IF NOT EXISTS admin_status_enum (
    value ENUM('active', 'suspended', 'inactive') PRIMARY KEY
);

-- Device status enum
CREATE TABLE IF NOT EXISTS device_status_enum (
    value ENUM('available', 'rented', 'maintenance', 'offline', 'reserved') PRIMARY KEY
);

-- Payment method enum
CREATE TABLE IF NOT EXISTS payment_method_enum (
    value ENUM('crypto', 'binance', 'card', 'bank_transfer', 'balance', 'manual') PRIMARY KEY
);

-- Payment status enum
CREATE TABLE IF NOT EXISTS payment_status_enum (
    value ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'expired') PRIMARY KEY
);

-- Payment type enum
CREATE TABLE IF NOT EXISTS payment_type_enum (
    value ENUM('rental', 'investment', 'withdrawal', 'referral_bonus', 'deposit', 'fee', 'refund') PRIMARY KEY
);

-- Plan type enum
CREATE TABLE IF NOT EXISTS plan_type_enum (
    value ENUM('basic', 'standard', 'premium', 'custom') PRIMARY KEY
);

-- Rental status enum
CREATE TABLE IF NOT EXISTS rental_status_enum (
    value ENUM('pending', 'active', 'completed', 'cancelled', 'suspended', 'expired') PRIMARY KEY
);

-- Investment status enum
CREATE TABLE IF NOT EXISTS investment_status_enum (
    value ENUM('pending', 'active', 'completed', 'cancelled', 'suspended', 'matured') PRIMARY KEY
);

-- Referral status enum
CREATE TABLE IF NOT EXISTS referral_status_enum (
    value ENUM('active', 'inactive', 'suspended') PRIMARY KEY
);

-- KYC status enum
CREATE TABLE IF NOT EXISTS kyc_status_enum (
    value ENUM('none', 'pending', 'approved', 'rejected') PRIMARY KEY
);

-- Withdrawal method enum
CREATE TABLE IF NOT EXISTS withdrawal_method_enum (
    value ENUM('crypto', 'bank_transfer', 'paypal', 'binance') PRIMARY KEY
);

-- Withdrawal status enum
CREATE TABLE IF NOT EXISTS withdrawal_status_enum (
    value ENUM('pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled') PRIMARY KEY
);

-- Notification type enum
CREATE TABLE IF NOT EXISTS notification_type_enum (
    value ENUM('email', 'telegram', 'sms', 'push', 'system') PRIMARY KEY
);

-- Notification status enum
CREATE TABLE IF NOT EXISTS notification_status_enum (
    value ENUM('pending', 'sent', 'delivered', 'failed', 'bounced') PRIMARY KEY
);

-- Setting type enum
CREATE TABLE IF NOT EXISTS setting_type_enum (
    value ENUM('string', 'number', 'boolean', 'json', 'text') PRIMARY KEY
);

-- Source type enum
CREATE TABLE IF NOT EXISTS source_type_enum (
    value ENUM('rental', 'investment', 'deposit') PRIMARY KEY
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    referral_code VARCHAR(20) NOT NULL UNIQUE,
    referred_by INT NULL,
    telegram_id BIGINT NULL UNIQUE,
    telegram_username VARCHAR(50) NULL,
    telegram_first_name VARCHAR(100) NULL,
    telegram_last_name VARCHAR(100) NULL,
    telegram_photo_url TEXT NULL,
    balance DECIMAL(12,2) DEFAULT 0.00,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    total_invested DECIMAL(12,2) DEFAULT 0.00,
    total_withdrawn DECIMAL(12,2) DEFAULT 0.00,
    referral_earnings DECIMAL(12,2) DEFAULT 0.00,
    rental_earnings DECIMAL(12,2) DEFAULT 0.00,
    investment_earnings DECIMAL(12,2) DEFAULT 0.00,
    phone VARCHAR(20) NULL,
    country VARCHAR(50) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    status ENUM('active', 'suspended', 'pending', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    telegram_verified BOOLEAN DEFAULT FALSE,
    kyc_status ENUM('none', 'pending', 'approved', 'rejected') DEFAULT 'none',
    kyc_documents JSON NULL,
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    crypto_wallets JSON NULL,
    preferred_crypto VARCHAR(10) DEFAULT 'BTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_referral_code (referral_code),
    INDEX idx_users_telegram_id (telegram_id),
    INDEX idx_users_status (status),
    INDEX idx_users_created_at (created_at)
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator', 'support') DEFAULT 'admin',
    permissions JSON NULL,
    two_factor_secret VARCHAR(32) NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    ip_address VARCHAR(45) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_admin_users_role (role),
    INDEX idx_admin_users_status (status)
);

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    device_info TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_sessions_user_id (user_id),
    INDEX idx_user_sessions_expires_at (expires_at)
);

-- Admin sessions table
CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_admin_sessions_admin_id (admin_id),
    INDEX idx_admin_sessions_expires_at (expires_at)
);

-- Devices table
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(50) DEFAULT 'Starlink Standard',
    serial_number VARCHAR(100) NULL UNIQUE,
    location VARCHAR(100) NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    status ENUM('available', 'rented', 'maintenance', 'offline', 'reserved') DEFAULT 'available',
    daily_rate DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    setup_fee DECIMAL(8,2) DEFAULT 0.00,
    max_speed_down INT DEFAULT 200,
    max_speed_up INT DEFAULT 20,
    uptime_percentage DECIMAL(5,2) DEFAULT 99.00,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    total_rentals INT DEFAULT 0,
    specifications JSON NULL,
    features JSON NULL,
    images JSON NULL,
    installation_date DATE NULL,
    warranty_expires DATE NULL,
    maintenance_schedule VARCHAR(20) DEFAULT 'monthly',
    last_maintenance DATE NULL,
    next_maintenance DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_devices_device_id (device_id),
    INDEX idx_devices_status (status),
    INDEX idx_devices_location (location)
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_id VARCHAR(100) NULL UNIQUE,
    external_id VARCHAR(100) NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    crypto_currency VARCHAR(20) NULL,
    crypto_amount DECIMAL(20,8) NULL,
    exchange_rate DECIMAL(15,8) NULL,
    payment_method ENUM('crypto', 'binance', 'card', 'bank_transfer', 'balance', 'manual') NOT NULL,
    payment_provider VARCHAR(50) NULL,
    provider_transaction_id VARCHAR(200) NULL,
    provider_response JSON NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'expired') DEFAULT 'pending',
    type ENUM('rental', 'investment', 'withdrawal', 'referral_bonus', 'deposit', 'fee', 'refund') NOT NULL,
    description TEXT NULL,
    metadata JSON NULL,
    fee_amount DECIMAL(12,2) DEFAULT 0.00,
    net_amount DECIMAL(12,2) NULL,
    webhook_received BOOLEAN DEFAULT FALSE,
    webhook_data JSON NULL,
    processed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payments_user_id (user_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_type (type),
    INDEX idx_payments_created_at (created_at)
);

-- Referrals table
CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    level SMALLINT NOT NULL CHECK (level IN (1, 2, 3)),
    commission_rate DECIMAL(5,2) NOT NULL,
    total_commission_earned DECIMAL(12,2) DEFAULT 0.00,
    total_referral_volume DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    first_earning_date DATE NULL,
    last_earning_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referral (referrer_id, referred_id),
    INDEX idx_referrals_referrer_id (referrer_id),
    INDEX idx_referrals_referred_id (referred_id),
    INDEX idx_referrals_level (level)
);

-- Rentals table
CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_id INT NOT NULL,
    payment_id INT NULL,
    plan_type ENUM('basic', 'standard', 'premium', 'custom') NOT NULL,
    plan_name VARCHAR(100) NULL,
    rental_duration INT NOT NULL,
    daily_profit_rate DECIMAL(5,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    setup_fee DECIMAL(8,2) DEFAULT 0.00,
    expected_daily_profit DECIMAL(8,2) NOT NULL,
    actual_total_profit DECIMAL(12,2) DEFAULT 0.00,
    total_days_active INT DEFAULT 0,
    performance_bonus DECIMAL(8,2) DEFAULT 0.00,
    status ENUM('pending', 'active', 'completed', 'cancelled', 'suspended', 'expired') DEFAULT 'pending',
    auto_renew BOOLEAN DEFAULT FALSE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    actual_start_date DATE NULL,
    actual_end_date DATE NULL,
    last_profit_date DATE NULL,
    cancellation_reason TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_rentals_user_id (user_id),
    INDEX idx_rentals_device_id (device_id),
    INDEX idx_rentals_status (status),
    INDEX idx_rentals_dates (start_date, end_date)
);

-- Investments table
CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_id INT NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_duration INT NOT NULL,
    investment_amount DECIMAL(12,2) NOT NULL,
    daily_rate DECIMAL(6,4) NOT NULL,
    expected_daily_profit DECIMAL(8,2) NOT NULL,
    total_earned DECIMAL(12,2) DEFAULT 0.00,
    total_days_active INT DEFAULT 0,
    compound_interest BOOLEAN DEFAULT FALSE,
    auto_reinvest BOOLEAN DEFAULT FALSE,
    reinvest_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('pending', 'active', 'completed', 'cancelled', 'suspended', 'matured') DEFAULT 'pending',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    actual_start_date DATE NULL,
    maturity_date DATE NULL,
    last_profit_date DATE NULL,
    early_withdrawal_fee DECIMAL(5,2) DEFAULT 10.00,
    withdrawal_allowed_after INT DEFAULT 30,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_investments_user_id (user_id),
    INDEX idx_investments_status (status),
    INDEX idx_investments_dates (start_date, end_date)
);

-- Rental earnings table
CREATE TABLE IF NOT EXISTS rental_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    user_id INT NOT NULL,
    device_id INT NOT NULL,
    earning_date DATE NOT NULL,
    base_profit_amount DECIMAL(8,2) NOT NULL,
    performance_bonus DECIMAL(8,2) DEFAULT 0.00,
    total_profit_amount DECIMAL(8,2) NOT NULL,
    device_uptime DECIMAL(5,2) DEFAULT 100.00,
    performance_factor DECIMAL(4,3) DEFAULT 1.000,
    weather_factor DECIMAL(4,3) DEFAULT 1.000,
    network_quality DECIMAL(5,2) DEFAULT 100.00,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rental_earning (rental_id, earning_date),
    INDEX idx_rental_earnings_rental_id (rental_id),
    INDEX idx_rental_earnings_user_id (user_id),
    INDEX idx_rental_earnings_earning_date (earning_date)
);

-- Investment earnings table
CREATE TABLE IF NOT EXISTS investment_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investment_id INT NOT NULL,
    user_id INT NOT NULL,
    earning_date DATE NOT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    daily_rate DECIMAL(6,4) NOT NULL,
    profit_amount DECIMAL(8,2) NOT NULL,
    compound_amount DECIMAL(8,2) DEFAULT 0.00,
    reinvested_amount DECIMAL(8,2) DEFAULT 0.00,
    paid_amount DECIMAL(8,2) NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (investment_id) REFERENCES investments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_investment_earning (investment_id, earning_date),
    INDEX idx_investment_earnings_investment_id (investment_id),
    INDEX idx_investment_earnings_user_id (user_id),
    INDEX idx_investment_earnings_earning_date (earning_date)
);

-- Referral earnings table
CREATE TABLE IF NOT EXISTS referral_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referral_id INT NOT NULL,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    source_type ENUM('rental', 'investment', 'deposit') NOT NULL,
    source_id INT NOT NULL,
    level SMALLINT NOT NULL CHECK (level IN (1, 2, 3)),
    commission_rate DECIMAL(5,2) NOT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    commission_amount DECIMAL(8,2) NOT NULL,
    earning_date DATE NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (referral_id) REFERENCES referrals(id) ON DELETE CASCADE,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referral_earnings_referral_id (referral_id),
    INDEX idx_referral_earnings_referrer_id (referrer_id),
    INDEX idx_referral_earnings_earning_date (earning_date)
);

-- Withdrawal requests table
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    fee_amount DECIMAL(8,2) DEFAULT 0.00,
    net_amount DECIMAL(12,2) NOT NULL,
    withdrawal_method ENUM('crypto', 'bank_transfer', 'paypal', 'binance') NOT NULL,
    withdrawal_address TEXT NULL,
    bank_details JSON NULL,
    status ENUM('pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT NULL,
    user_notes TEXT NULL,
    processed_by INT NULL,
    transaction_hash VARCHAR(200) NULL,
    external_transaction_id VARCHAR(200) NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_withdrawal_requests_user_id (user_id),
    INDEX idx_withdrawal_requests_status (status)
);

-- Telegram sessions table
CREATE TABLE IF NOT EXISTS telegram_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    telegram_id BIGINT NOT NULL,
    session_data JSON NULL,
    init_data TEXT NULL,
    init_data_hash VARCHAR(64) NULL,
    auth_date INT NULL,
    query_id VARCHAR(100) NULL,
    chat_type VARCHAR(20) NULL,
    chat_instance VARCHAR(50) NULL,
    start_param VARCHAR(100) NULL,
    is_premium BOOLEAN DEFAULT FALSE,
    language_code VARCHAR(10) DEFAULT 'en',
    platform VARCHAR(20) NULL,
    version VARCHAR(20) NULL,
    theme_params JSON NULL,
    viewport_height INT NULL,
    viewport_stable_height INT NULL,
    header_color VARCHAR(7) NULL,
    background_color VARCHAR(7) NULL,
    is_expanded BOOLEAN DEFAULT FALSE,
    is_closing_confirmation_enabled BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_telegram_sessions_user_id (user_id),
    INDEX idx_telegram_sessions_telegram_id (telegram_id)
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_system_settings_category (category)
);

-- Email notifications table
CREATE TABLE IF NOT EXISTS email_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    template_data JSON NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'sent', 'failed', 'delivered')),
    provider VARCHAR(50) NULL,
    provider_message_id VARCHAR(255) NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_email_notifications_user_id (user_id),
    INDEX idx_email_notifications_status (status)
);

-- API logs table
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    admin_id INT NULL,
    endpoint VARCHAR(200) NOT NULL,
    method VARCHAR(10) NOT NULL,
    request_data JSON NULL,
    response_data JSON NULL,
    status_code INT NOT NULL,
    response_time DECIMAL(8,3) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_api_logs_user_id (user_id),
    INDEX idx_api_logs_created_at (created_at)
);

-- Notification logs table
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    type ENUM('email', 'telegram', 'sms', 'push', 'system') NOT NULL,
    channel VARCHAR(50) NULL,
    recipient VARCHAR(200) NOT NULL,
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    template VARCHAR(100) NULL,
    template_data JSON NULL,
    status ENUM('pending', 'sent', 'delivered', 'failed', 'bounced') DEFAULT 'pending',
    provider VARCHAR(50) NULL,
    provider_id VARCHAR(100) NULL,
    provider_response JSON NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    next_retry_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_notification_logs_user_id (user_id),
    INDEX idx_notification_logs_status (status)
);

-- Insert default admin user
INSERT IGNORE INTO admin_users (username, email, password_hash, role, status, created_at) 
VALUES ('admin', 'admin@gainsmax.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW());

-- Insert sample devices
INSERT IGNORE INTO devices (device_id, name, model, location, status, daily_rate, max_speed_down, max_speed_up, uptime_percentage, created_at) VALUES
('GMTG001', 'GainsMax Satellite Alpha', 'Starlink Gen3', 'New York, USA', 'available', 15.00, 200, 20, 99.5, NOW()),
('GMTG002', 'GainsMax Satellite Beta', 'Starlink Gen3', 'London, UK', 'available', 18.00, 250, 25, 99.8, NOW()),
('GMTG003', 'GainsMax Satellite Gamma', 'Starlink Enterprise', 'Tokyo, Japan', 'available', 25.00, 300, 30, 99.9, NOW()),
('GMTG004', 'GainsMax Satellite Delta', 'Starlink Gen3', 'Sydney, Australia', 'available', 20.00, 220, 22, 99.7, NOW()),
('GMTG005', 'GainsMax Satellite Epsilon', 'Starlink Enterprise', 'Frankfurt, Germany', 'available', 22.00, 280, 28, 99.6, NOW());

-- Insert default system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, category, description, is_public, created_at) VALUES
('site_name', 'GainsMax Test Telegram', 'string', 'general', 'Site name', TRUE, NOW()),
('site_url', 'https://gainsmax-testtelegram.com', 'string', 'general', 'Site URL', TRUE, NOW()),
('admin_email', 'admin@gainsmax.com', 'string', 'general', 'Admin email address', FALSE, NOW()),
('min_deposit', '50', 'number', 'payments', 'Minimum deposit amount', TRUE, NOW()),
('max_deposit', '10000', 'number', 'payments', 'Maximum deposit amount', TRUE, NOW()),
('min_withdrawal', '20', 'number', 'payments', 'Minimum withdrawal amount', TRUE, NOW()),
('withdrawal_fee', '2.0', 'number', 'payments', 'Withdrawal fee percentage', TRUE, NOW()),
('plisio_api_key', '', 'string', 'payments', 'Plisio.net API key', FALSE, NOW()),
('plisio_webhook_url', '', 'string', 'payments', 'Plisio webhook URL', FALSE, NOW()),
('binance_api_key', '', 'string', 'payments', 'Binance API key', FALSE, NOW()),
('binance_secret', '', 'string', 'payments', 'Binance API secret', FALSE, NOW()),
('telegram_bot_token', '', 'string', 'telegram', 'Telegram bot token', FALSE, NOW()),
('email_notifications_enabled', '1', 'boolean', 'email', 'Enable email notifications', FALSE, NOW()),
('welcome_email_enabled', '1', 'boolean', 'email', 'Enable welcome emails', FALSE, NOW()),
('deposit_email_enabled', '1', 'boolean', 'email', 'Enable deposit confirmation emails', FALSE, NOW()),
('withdrawal_email_enabled', '1', 'boolean', 'email', 'Enable withdrawal notification emails', FALSE, NOW()),
('daily_earnings_email_enabled', '0', 'boolean', 'email', 'Enable daily earnings emails', FALSE, NOW()),
('referral_email_enabled', '1', 'boolean', 'email', 'Enable referral bonus emails', FALSE, NOW());

-- Create triggers for automatic referral code generation
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS auto_generate_referral_code
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.referral_code IS NULL OR NEW.referral_code = '' THEN
        SET NEW.referral_code = UPPER(SUBSTRING(MD5(CONCAT(NEW.username, UNIX_TIMESTAMP(), RAND())), 1, 10));
    END IF;
END$$

DELIMITER ;

-- Create trigger for referral relationships
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS create_referral_relationships
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    DECLARE referrer_id INT;
    DECLARE level2_referrer INT;
    DECLARE level3_referrer INT;
    
    IF NEW.referred_by IS NOT NULL THEN
        -- Level 1 referral
        INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status, created_at)
        VALUES (NEW.referred_by, NEW.id, 1, 7.00, 'active', NOW());
        
        -- Level 2 referral
        SELECT referred_by INTO level2_referrer FROM users WHERE id = NEW.referred_by;
        IF level2_referrer IS NOT NULL THEN
            INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status, created_at)
            VALUES (level2_referrer, NEW.id, 2, 5.00, 'active', NOW());
            
            -- Level 3 referral
            SELECT referred_by INTO level3_referrer FROM users WHERE id = level2_referrer;
            IF level3_referrer IS NOT NULL THEN
                INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status, created_at)
                VALUES (level3_referrer, NEW.id, 3, 3.00, 'active', NOW());
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Create function for updating timestamps
DELIMITER $$

CREATE FUNCTION IF NOT EXISTS update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql$$

DELIMITER ;

-- Enable row level security (for future PostgreSQL migration)
-- ALTER TABLE users ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE rentals ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE investments ENABLE ROW LEVEL SECURITY;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_payments_transaction_id ON payments(transaction_id);
CREATE INDEX IF NOT EXISTS idx_devices_name ON devices(name);

-- Set database charset and collation
ALTER DATABASE `gainsmax_testtelegram` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Optimize tables
OPTIMIZE TABLE users, admin_users, devices, payments, rentals, investments;

-- Show completion message
SELECT 'GainsMax Test Telegram database schema created successfully!' as message;