import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
  throw new Error('Missing Supabase environment variables');
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey, {
  auth: {
    autoRefreshToken: true,
    persistSession: true,
    detectSessionInUrl: true
  }
});

// Database types
export interface User {
  id: string;
  username: string;
  email: string;
  referral_code: string;
  referred_by?: string;
  telegram_id?: number;
  telegram_username?: string;
  balance: number;
  total_earnings: number;
  total_invested: number;
  total_withdrawn: number;
  referral_earnings: number;
  rental_earnings: number;
  investment_earnings: number;
  phone?: string;
  country?: string;
  timezone?: string;
  language?: string;
  status: 'active' | 'suspended' | 'pending' | 'banned';
  email_verified: boolean;
  telegram_verified: boolean;
  kyc_status: 'none' | 'pending' | 'approved' | 'rejected';
  created_at: string;
  updated_at: string;
}

export interface Device {
  id: string;
  device_id: string;
  name: string;
  model: string;
  location: string;
  status: 'available' | 'rented' | 'maintenance' | 'offline';
  daily_rate: number;
  uptime_percentage: number;
  max_speed_down: number;
  max_speed_up: number;
  created_at: string;
}

export interface Rental {
  id: string;
  user_id: string;
  device_id: string;
  plan_type: 'basic' | 'standard' | 'premium';
  plan_name: string;
  rental_duration: number;
  daily_profit_rate: number;
  total_cost: number;
  expected_daily_profit: number;
  actual_total_profit: number;
  status: 'pending' | 'active' | 'completed' | 'cancelled';
  start_date: string;
  end_date: string;
  created_at: string;
}

export interface Investment {
  id: string;
  user_id: string;
  plan_name: string;
  plan_duration: number;
  investment_amount: number;
  daily_rate: number;
  expected_daily_profit: number;
  total_earned: number;
  status: 'pending' | 'active' | 'completed' | 'cancelled';
  start_date: string;
  end_date: string;
  created_at: string;
}

export interface Payment {
  id: string;
  user_id: string;
  transaction_id: string;
  amount: number;
  currency: string;
  crypto_currency?: string;
  payment_method: 'crypto' | 'binance' | 'card' | 'bank_transfer';
  status: 'pending' | 'processing' | 'completed' | 'failed';
  type: 'deposit' | 'withdrawal' | 'rental' | 'investment';
  description?: string;
  created_at: string;
}

export interface Referral {
  id: string;
  referrer_id: string;
  referred_id: string;
  level: 1 | 2 | 3;
  commission_rate: number;
  total_commission_earned: number;
  status: 'active' | 'inactive';
  created_at: string;
}