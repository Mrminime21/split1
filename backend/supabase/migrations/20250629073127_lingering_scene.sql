/*
  # Starlink Router Rent Database Schema
  # Version: 2.0.0 - PostgreSQL Compatible

  1. New Tables
    - `users` - User accounts with referral system
    - `admin_users` - Admin user accounts
    - `user_sessions` - User session management
    - `admin_sessions` - Admin session management
    - `devices` - Starlink device inventory
    - `payments` - Payment transactions
    - `referrals` - Multi-level referral system
    - `rentals` - Device rental records
    - `investments` - Investment plans
    - `rental_earnings` - Daily rental profits
    - `investment_earnings` - Daily investment profits
    - `referral_earnings` - Referral commissions
    - `withdrawal_requests` - Withdrawal requests
    - `system_settings` - System configuration
    - `email_notifications` - Email notification logs
    - `payment_webhooks` - Payment webhook logs

  2. Security
    - Enable RLS on all user-related tables
    - Add policies for user data access
    - Secure admin access controls

  3. Features
    - UUID primary keys for scalability
    - Automatic referral code generation
    - Multi-level referral tracking
    - Comprehensive audit trails
    - Payment webhook processing
*/

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Create custom types
DO $$ BEGIN
    CREATE TYPE user_status AS ENUM ('active', 'suspended', 'pending', 'banned');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE admin_role AS ENUM ('super_admin', 'admin', 'moderator', 'support');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE admin_status AS ENUM ('active', 'suspended', 'inactive');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE device_status AS ENUM ('available', 'rented', 'maintenance', 'offline', 'reserved');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_method AS ENUM ('crypto', 'binance', 'card', 'bank_transfer', 'balance', 'manual');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_status AS ENUM ('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'expired');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE payment_type AS ENUM ('rental', 'investment', 'withdrawal', 'referral_bonus', 'deposit', 'fee', 'refund');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE plan_type AS ENUM ('basic', 'standard', 'premium', 'custom');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE rental_status AS ENUM ('pending', 'active', 'completed', 'cancelled', 'suspended', 'expired');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE investment_status AS ENUM ('pending', 'active', 'completed', 'cancelled', 'suspended', 'matured');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE referral_status AS ENUM ('active', 'inactive', 'suspended');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE kyc_status AS ENUM ('none', 'pending', 'approved', 'rejected');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE withdrawal_method AS ENUM ('crypto', 'bank_transfer', 'paypal', 'binance');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE withdrawal_status AS ENUM ('pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE notification_status AS ENUM ('pending', 'sent', 'failed', 'delivered');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE setting_type AS ENUM ('string', 'number', 'boolean', 'json', 'text');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE source_type AS ENUM ('rental', 'investment', 'deposit');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    referral_code VARCHAR(20) NOT NULL UNIQUE,
    referred_by UUID REFERENCES users(id) ON DELETE SET NULL,
    telegram_id BIGINT UNIQUE,
    telegram_username VARCHAR(50),
    telegram_first_name VARCHAR(100),
    telegram_last_name VARCHAR(100),
    telegram_photo_url TEXT,
    balance DECIMAL(12,2) DEFAULT 0.00,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    total_invested DECIMAL(12,2) DEFAULT 0.00,
    total_withdrawn DECIMAL(12,2) DEFAULT 0.00,
    referral_earnings DECIMAL(12,2) DEFAULT 0.00,
    rental_earnings DECIMAL(12,2) DEFAULT 0.00,
    investment_earnings DECIMAL(12,2) DEFAULT 0.00,
    phone VARCHAR(20),
    country VARCHAR(50),
    timezone VARCHAR(50) DEFAULT 'UTC',
    language VARCHAR(10) DEFAULT 'en',
    status user_status DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    telegram_verified BOOLEAN DEFAULT FALSE,
    kyc_status kyc_status DEFAULT 'none',
    kyc_documents JSONB,
    last_login TIMESTAMPTZ,
    last_activity TIMESTAMPTZ,
    ip_address INET,
    user_agent TEXT,
    crypto_wallets JSONB,
    preferred_crypto VARCHAR(10) DEFAULT 'BTC',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role admin_role DEFAULT 'admin',
    permissions JSONB,
    two_factor_secret VARCHAR(32),
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    status admin_status DEFAULT 'active',
    last_login TIMESTAMPTZ,
    last_activity TIMESTAMPTZ,
    login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMPTZ,
    ip_address INET,
    created_by INTEGER REFERENCES admin_users(id),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id SERIAL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    device_info TEXT,
    ip_address INET,
    user_agent TEXT,
    expires_at TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Admin sessions table
CREATE TABLE IF NOT EXISTS admin_sessions (
    id SERIAL PRIMARY KEY,
    admin_id INTEGER NOT NULL REFERENCES admin_users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address INET,
    user_agent TEXT,
    expires_at TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Devices table
CREATE TABLE IF NOT EXISTS devices (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    device_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(50) DEFAULT 'Starlink Standard',
    serial_number VARCHAR(100) UNIQUE,
    location VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    status device_status DEFAULT 'available',
    daily_rate DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    setup_fee DECIMAL(8,2) DEFAULT 0.00,
    max_speed_down INTEGER DEFAULT 200,
    max_speed_up INTEGER DEFAULT 20,
    uptime_percentage DECIMAL(5,2) DEFAULT 99.00,
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    total_rentals INTEGER DEFAULT 0,
    specifications JSONB,
    features JSONB,
    images JSONB,
    installation_date DATE,
    warranty_expires DATE,
    maintenance_schedule VARCHAR(20) DEFAULT 'monthly',
    last_maintenance DATE,
    next_maintenance DATE,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    transaction_id VARCHAR(100) UNIQUE,
    external_id VARCHAR(100),
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    crypto_currency VARCHAR(20),
    crypto_amount DECIMAL(20,8),
    exchange_rate DECIMAL(15,8),
    payment_method payment_method NOT NULL,
    payment_provider VARCHAR(50),
    provider_transaction_id VARCHAR(200),
    provider_response JSONB,
    status payment_status DEFAULT 'pending',
    type payment_type NOT NULL,
    description TEXT,
    metadata JSONB,
    fee_amount DECIMAL(12,2) DEFAULT 0.00,
    net_amount DECIMAL(12,2),
    webhook_received BOOLEAN DEFAULT FALSE,
    webhook_data JSONB,
    processed_at TIMESTAMPTZ,
    expires_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Referrals table
CREATE TABLE IF NOT EXISTS referrals (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    referrer_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    referred_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    level SMALLINT NOT NULL CHECK (level IN (1, 2, 3)),
    commission_rate DECIMAL(5,2) NOT NULL,
    total_commission_earned DECIMAL(12,2) DEFAULT 0.00,
    total_referral_volume DECIMAL(12,2) DEFAULT 0.00,
    status referral_status DEFAULT 'active',
    first_earning_date DATE,
    last_earning_date DATE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(referrer_id, referred_id)
);

-- Rentals table
CREATE TABLE IF NOT EXISTS rentals (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_id UUID NOT NULL REFERENCES devices(id) ON DELETE CASCADE,
    payment_id UUID REFERENCES payments(id) ON DELETE SET NULL,
    plan_type plan_type NOT NULL,
    plan_name VARCHAR(100),
    rental_duration INTEGER NOT NULL,
    daily_profit_rate DECIMAL(5,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    setup_fee DECIMAL(8,2) DEFAULT 0.00,
    expected_daily_profit DECIMAL(8,2) NOT NULL,
    actual_total_profit DECIMAL(12,2) DEFAULT 0.00,
    total_days_active INTEGER DEFAULT 0,
    performance_bonus DECIMAL(8,2) DEFAULT 0.00,
    status rental_status DEFAULT 'pending',
    auto_renew BOOLEAN DEFAULT FALSE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    actual_start_date DATE,
    actual_end_date DATE,
    last_profit_date DATE,
    cancellation_reason TEXT,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Investments table
CREATE TABLE IF NOT EXISTS investments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    payment_id UUID REFERENCES payments(id) ON DELETE SET NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_duration INTEGER NOT NULL,
    investment_amount DECIMAL(12,2) NOT NULL,
    daily_rate DECIMAL(6,4) NOT NULL,
    expected_daily_profit DECIMAL(8,2) NOT NULL,
    total_earned DECIMAL(12,2) DEFAULT 0.00,
    total_days_active INTEGER DEFAULT 0,
    compound_interest BOOLEAN DEFAULT FALSE,
    auto_reinvest BOOLEAN DEFAULT FALSE,
    reinvest_percentage DECIMAL(5,2) DEFAULT 0.00,
    status investment_status DEFAULT 'pending',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    actual_start_date DATE,
    maturity_date DATE,
    last_profit_date DATE,
    early_withdrawal_fee DECIMAL(5,2) DEFAULT 10.00,
    withdrawal_allowed_after INTEGER DEFAULT 30,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Rental earnings table
CREATE TABLE IF NOT EXISTS rental_earnings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    rental_id UUID NOT NULL REFERENCES rentals(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_id UUID NOT NULL REFERENCES devices(id) ON DELETE CASCADE,
    earning_date DATE NOT NULL,
    base_profit_amount DECIMAL(8,2) NOT NULL,
    performance_bonus DECIMAL(8,2) DEFAULT 0.00,
    total_profit_amount DECIMAL(8,2) NOT NULL,
    device_uptime DECIMAL(5,2) DEFAULT 100.00,
    performance_factor DECIMAL(4,3) DEFAULT 1.000,
    weather_factor DECIMAL(4,3) DEFAULT 1.000,
    network_quality DECIMAL(5,2) DEFAULT 100.00,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMPTZ,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(rental_id, earning_date)
);

-- Investment earnings table
CREATE TABLE IF NOT EXISTS investment_earnings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    investment_id UUID NOT NULL REFERENCES investments(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    earning_date DATE NOT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    daily_rate DECIMAL(6,4) NOT NULL,
    profit_amount DECIMAL(8,2) NOT NULL,
    compound_amount DECIMAL(8,2) DEFAULT 0.00,
    reinvested_amount DECIMAL(8,2) DEFAULT 0.00,
    paid_amount DECIMAL(8,2) NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMPTZ,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(investment_id, earning_date)
);

-- Referral earnings table
CREATE TABLE IF NOT EXISTS referral_earnings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    referral_id UUID NOT NULL REFERENCES referrals(id) ON DELETE CASCADE,
    referrer_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    referred_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    source_type source_type NOT NULL,
    source_id UUID NOT NULL,
    level SMALLINT NOT NULL CHECK (level IN (1, 2, 3)),
    commission_rate DECIMAL(5,2) NOT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    commission_amount DECIMAL(8,2) NOT NULL,
    earning_date DATE NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMPTZ,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Withdrawal requests table
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(12,2) NOT NULL,
    fee_amount DECIMAL(8,2) DEFAULT 0.00,
    net_amount DECIMAL(12,2) NOT NULL,
    withdrawal_method withdrawal_method NOT NULL,
    withdrawal_address TEXT,
    bank_details JSONB,
    status withdrawal_status DEFAULT 'pending',
    admin_notes TEXT,
    user_notes TEXT,
    processed_by INTEGER REFERENCES admin_users(id) ON DELETE SET NULL,
    transaction_hash VARCHAR(200),
    external_transaction_id VARCHAR(200),
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ,
    completed_at TIMESTAMPTZ
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type setting_type DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by INTEGER REFERENCES admin_users(id),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Email notifications table
CREATE TABLE IF NOT EXISTS email_notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id),
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    template_data JSONB,
    status notification_status DEFAULT 'pending',
    provider VARCHAR(50),
    provider_message_id VARCHAR(255),
    sent_at TIMESTAMPTZ,
    delivered_at TIMESTAMPTZ,
    error_message TEXT,
    retry_count INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Payment webhooks table
CREATE TABLE IF NOT EXISTS payment_webhooks (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    provider VARCHAR(50) NOT NULL,
    webhook_id VARCHAR(255),
    event_type VARCHAR(100) NOT NULL,
    payment_id UUID REFERENCES payments(id),
    raw_data JSONB NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processing_attempts INTEGER DEFAULT 0,
    last_processing_error TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_referral_code ON users(referral_code);
CREATE INDEX IF NOT EXISTS idx_users_telegram_id ON users(telegram_id);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

CREATE INDEX IF NOT EXISTS idx_admin_users_role ON admin_users(role);
CREATE INDEX IF NOT EXISTS idx_admin_users_status ON admin_users(status);

CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_user_sessions_expires_at ON user_sessions(expires_at);

CREATE INDEX IF NOT EXISTS idx_admin_sessions_admin_id ON admin_sessions(admin_id);
CREATE INDEX IF NOT EXISTS idx_admin_sessions_expires_at ON admin_sessions(expires_at);

CREATE INDEX IF NOT EXISTS idx_devices_device_id ON devices(device_id);
CREATE INDEX IF NOT EXISTS idx_devices_status ON devices(status);
CREATE INDEX IF NOT EXISTS idx_devices_location ON devices(location);
CREATE INDEX IF NOT EXISTS idx_devices_name ON devices(name);

CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id);
CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status);
CREATE INDEX IF NOT EXISTS idx_payments_type ON payments(type);
CREATE INDEX IF NOT EXISTS idx_payments_created_at ON payments(created_at);
CREATE INDEX IF NOT EXISTS idx_payments_transaction_id ON payments(transaction_id);

CREATE INDEX IF NOT EXISTS idx_referrals_referrer_id ON referrals(referrer_id);
CREATE INDEX IF NOT EXISTS idx_referrals_referred_id ON referrals(referred_id);
CREATE INDEX IF NOT EXISTS idx_referrals_level ON referrals(level);

CREATE INDEX IF NOT EXISTS idx_rentals_user_id ON rentals(user_id);
CREATE INDEX IF NOT EXISTS idx_rentals_device_id ON rentals(device_id);
CREATE INDEX IF NOT EXISTS idx_rentals_status ON rentals(status);
CREATE INDEX IF NOT EXISTS idx_rentals_dates ON rentals(start_date, end_date);

CREATE INDEX IF NOT EXISTS idx_investments_user_id ON investments(user_id);
CREATE INDEX IF NOT EXISTS idx_investments_status ON investments(status);
CREATE INDEX IF NOT EXISTS idx_investments_dates ON investments(start_date, end_date);

CREATE INDEX IF NOT EXISTS idx_rental_earnings_rental_id ON rental_earnings(rental_id);
CREATE INDEX IF NOT EXISTS idx_rental_earnings_user_id ON rental_earnings(user_id);
CREATE INDEX IF NOT EXISTS idx_rental_earnings_earning_date ON rental_earnings(earning_date);

CREATE INDEX IF NOT EXISTS idx_investment_earnings_investment_id ON investment_earnings(investment_id);
CREATE INDEX IF NOT EXISTS idx_investment_earnings_user_id ON investment_earnings(user_id);
CREATE INDEX IF NOT EXISTS idx_investment_earnings_earning_date ON investment_earnings(earning_date);

CREATE INDEX IF NOT EXISTS idx_referral_earnings_referral_id ON referral_earnings(referral_id);
CREATE INDEX IF NOT EXISTS idx_referral_earnings_referrer_id ON referral_earnings(referrer_id);
CREATE INDEX IF NOT EXISTS idx_referral_earnings_earning_date ON referral_earnings(earning_date);

CREATE INDEX IF NOT EXISTS idx_withdrawal_requests_user_id ON withdrawal_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_withdrawal_requests_status ON withdrawal_requests(status);

CREATE INDEX IF NOT EXISTS idx_system_settings_category ON system_settings(category);

CREATE INDEX IF NOT EXISTS idx_email_notifications_user_id ON email_notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_email_notifications_status ON email_notifications(status);

CREATE INDEX IF NOT EXISTS idx_payment_webhooks_provider ON payment_webhooks(provider);
CREATE INDEX IF NOT EXISTS idx_payment_webhooks_processed ON payment_webhooks(processed);

-- Create function for generating referral codes
CREATE OR REPLACE FUNCTION generate_referral_code() 
RETURNS VARCHAR(20) AS $$
DECLARE
    code VARCHAR(20);
    done INTEGER := 0;
BEGIN
    LOOP
        code := UPPER(SUBSTRING(MD5(RANDOM()::TEXT), 1, 10));
        SELECT COUNT(*) INTO done FROM users WHERE referral_code = code;
        EXIT WHEN done = 0;
    END LOOP;
    
    RETURN code;
END;
$$ LANGUAGE plpgsql;

-- Create function for automatic referral code generation
CREATE OR REPLACE FUNCTION auto_generate_referral_code()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.referral_code IS NULL OR NEW.referral_code = '' THEN
        NEW.referral_code := generate_referral_code();
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for automatic referral code generation
DROP TRIGGER IF EXISTS trigger_auto_generate_referral_code ON users;
CREATE TRIGGER trigger_auto_generate_referral_code
    BEFORE INSERT ON users
    FOR EACH ROW
    EXECUTE FUNCTION auto_generate_referral_code();

-- Create function for referral relationship creation
CREATE OR REPLACE FUNCTION create_referral_relationships()
RETURNS TRIGGER AS $$
DECLARE
    level2_referrer UUID;
    level3_referrer UUID;
BEGIN
    IF NEW.referred_by IS NOT NULL THEN
        -- Level 1 referral
        INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
        VALUES (NEW.referred_by, NEW.id, 1, 7.00, 'active');
        
        -- Level 2 referral
        SELECT referred_by INTO level2_referrer FROM users WHERE id = NEW.referred_by;
        IF level2_referrer IS NOT NULL THEN
            INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
            VALUES (level2_referrer, NEW.id, 2, 5.00, 'active');
            
            -- Level 3 referral
            SELECT referred_by INTO level3_referrer FROM users WHERE id = level2_referrer;
            IF level3_referrer IS NOT NULL THEN
                INSERT INTO referrals (referrer_id, referred_id, level, commission_rate, status) 
                VALUES (level3_referrer, NEW.id, 3, 3.00, 'active');
            END IF;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for automatic referral relationship creation
DROP TRIGGER IF EXISTS trigger_create_referral_relationships ON users;
CREATE TRIGGER trigger_create_referral_relationships
    AFTER INSERT ON users
    FOR EACH ROW
    EXECUTE FUNCTION create_referral_relationships();

-- Create function for updating timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers for automatic timestamp updates
DROP TRIGGER IF EXISTS trigger_users_updated_at ON users;
CREATE TRIGGER trigger_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_admin_users_updated_at ON admin_users;
CREATE TRIGGER trigger_admin_users_updated_at
    BEFORE UPDATE ON admin_users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_devices_updated_at ON devices;
CREATE TRIGGER trigger_devices_updated_at
    BEFORE UPDATE ON devices
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_payments_updated_at ON payments;
CREATE TRIGGER trigger_payments_updated_at
    BEFORE UPDATE ON payments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_referrals_updated_at ON referrals;
CREATE TRIGGER trigger_referrals_updated_at
    BEFORE UPDATE ON referrals
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_rentals_updated_at ON rentals;
CREATE TRIGGER trigger_rentals_updated_at
    BEFORE UPDATE ON rentals
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_investments_updated_at ON investments;
CREATE TRIGGER trigger_investments_updated_at
    BEFORE UPDATE ON investments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS trigger_system_settings_updated_at ON system_settings;
CREATE TRIGGER trigger_system_settings_updated_at
    BEFORE UPDATE ON system_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Enable Row Level Security
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE rentals ENABLE ROW LEVEL SECURITY;
ALTER TABLE investments ENABLE ROW LEVEL SECURITY;
ALTER TABLE rental_earnings ENABLE ROW LEVEL SECURITY;
ALTER TABLE investment_earnings ENABLE ROW LEVEL SECURITY;
ALTER TABLE referral_earnings ENABLE ROW LEVEL SECURITY;
ALTER TABLE withdrawal_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE email_notifications ENABLE ROW LEVEL SECURITY;

-- Create RLS policies for users
CREATE POLICY "Users can read own data" ON users
    FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own data" ON users
    FOR UPDATE USING (auth.uid() = id);

-- Create RLS policies for payments
CREATE POLICY "Users can read own payments" ON payments
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can create own payments" ON payments
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Create RLS policies for rentals
CREATE POLICY "Users can read own rentals" ON rentals
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can create own rentals" ON rentals
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Create RLS policies for investments
CREATE POLICY "Users can read own investments" ON investments
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can create own investments" ON investments
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Create RLS policies for rental earnings
CREATE POLICY "Users can read own rental earnings" ON rental_earnings
    FOR SELECT USING (auth.uid() = user_id);

-- Create RLS policies for investment earnings
CREATE POLICY "Users can read own investment earnings" ON investment_earnings
    FOR SELECT USING (auth.uid() = user_id);

-- Create RLS policies for referral earnings
CREATE POLICY "Users can read own referral earnings" ON referral_earnings
    FOR SELECT USING (auth.uid() = referrer_id);

-- Create RLS policies for withdrawal requests
CREATE POLICY "Users can read own withdrawal requests" ON withdrawal_requests
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can create own withdrawal requests" ON withdrawal_requests
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Create RLS policies for email notifications
CREATE POLICY "Users can read own email notifications" ON email_notifications
    FOR SELECT USING (auth.uid() = user_id);

-- Insert default admin user
INSERT INTO admin_users (username, email, password_hash, role, status, created_at) 
VALUES ('admin', 'admin@starlinkrouterrent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW())
ON CONFLICT (username) DO NOTHING;

-- Insert sample devices
INSERT INTO devices (device_id, name, model, location, status, daily_rate, max_speed_down, max_speed_up, uptime_percentage, created_at) VALUES
('SRR001', 'Starlink Router Alpha', 'Starlink Gen3', 'New York, USA', 'available', 15.00, 200, 20, 99.5, NOW()),
('SRR002', 'Starlink Router Beta', 'Starlink Gen3', 'London, UK', 'available', 18.00, 250, 25, 99.8, NOW()),
('SRR003', 'Starlink Router Gamma', 'Starlink Enterprise', 'Tokyo, Japan', 'available', 25.00, 300, 30, 99.9, NOW()),
('SRR004', 'Starlink Router Delta', 'Starlink Gen3', 'Sydney, Australia', 'available', 20.00, 220, 22, 99.7, NOW()),
('SRR005', 'Starlink Router Epsilon', 'Starlink Enterprise', 'Frankfurt, Germany', 'available', 22.00, 280, 28, 99.6, NOW())
ON CONFLICT (device_id) DO NOTHING;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, category, description, is_public, created_at) VALUES
('site_name', 'Starlink Router Rent', 'string', 'general', 'Site name', TRUE, NOW()),
('site_url', 'https://starlinkrouterrent.com', 'string', 'general', 'Site URL', TRUE, NOW()),
('admin_email', 'admin@starlinkrouterrent.com', 'string', 'general', 'Admin email address', FALSE, NOW()),
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
('referral_email_enabled', '1', 'boolean', 'email', 'Enable referral bonus emails', FALSE, NOW())
ON CONFLICT (setting_key) DO NOTHING;