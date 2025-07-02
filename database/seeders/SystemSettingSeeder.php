<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['setting_key' => 'site_name', 'setting_value' => 'Starlink Router Rent', 'setting_type' => 'string', 'category' => 'general', 'description' => 'Site name', 'is_public' => true],
            ['setting_key' => 'site_url', 'setting_value' => 'https://starlinkrouterrent.com', 'setting_type' => 'string', 'category' => 'general', 'description' => 'Site URL', 'is_public' => true],
            ['setting_key' => 'admin_email', 'setting_value' => 'admin@starlinkrouterrent.com', 'setting_type' => 'string', 'category' => 'general', 'description' => 'Admin email address', 'is_public' => false],
            ['setting_key' => 'min_deposit', 'setting_value' => '50', 'setting_type' => 'number', 'category' => 'payments', 'description' => 'Minimum deposit amount', 'is_public' => true],
            ['setting_key' => 'max_deposit', 'setting_value' => '10000', 'setting_type' => 'number', 'category' => 'payments', 'description' => 'Maximum deposit amount', 'is_public' => true],
            ['setting_key' => 'min_withdrawal', 'setting_value' => '20', 'setting_type' => 'number', 'category' => 'payments', 'description' => 'Minimum withdrawal amount', 'is_public' => true],
            ['setting_key' => 'withdrawal_fee', 'setting_value' => '2.0', 'setting_type' => 'number', 'category' => 'payments', 'description' => 'Withdrawal fee percentage', 'is_public' => true],
            ['setting_key' => 'plisio_api_key', 'setting_value' => '', 'setting_type' => 'string', 'category' => 'payments', 'description' => 'Plisio.net API key', 'is_public' => false],
            ['setting_key' => 'binance_api_key', 'setting_value' => '', 'setting_type' => 'string', 'category' => 'payments', 'description' => 'Binance API key', 'is_public' => false],
            ['setting_key' => 'telegram_bot_token', 'setting_value' => '', 'setting_type' => 'string', 'category' => 'telegram', 'description' => 'Telegram bot token', 'is_public' => false],
            ['setting_key' => 'email_notifications_enabled', 'setting_value' => '1', 'setting_type' => 'boolean', 'category' => 'email', 'description' => 'Enable email notifications', 'is_public' => false],
            ['setting_key' => 'welcome_email_enabled', 'setting_value' => '1', 'setting_type' => 'boolean', 'category' => 'email', 'description' => 'Enable welcome emails', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            DB::table('system_settings')->insert($setting);
        }
    }
}