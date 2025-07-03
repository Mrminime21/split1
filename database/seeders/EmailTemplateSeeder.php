<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {{site_name}}!',
                'content' => '<h1>Welcome {{username}}!</h1><p>Thank you for joining {{site_name}}. Your account has been created successfully.</p><p>Your referral code: <strong>{{referral_code}}</strong></p><p>Start earning today by renting our premium Starlink routers!</p>',
                'variables' => 'username,site_name,referral_code',
                'is_active' => true,
            ],
            [
                'name' => 'Deposit Confirmation',
                'slug' => 'deposit_confirmation',
                'subject' => 'Deposit Confirmed - ${{amount}}',
                'content' => '<h1>Deposit Confirmed</h1><p>Hi {{username}},</p><p>Your deposit of ${{amount}} has been successfully processed.</p><p>Transaction ID: {{transaction_id}}</p><p>Your new balance: ${{new_balance}}</p>',
                'variables' => 'username,amount,transaction_id,new_balance',
                'is_active' => true,
            ],
            [
                'name' => 'Withdrawal Request',
                'slug' => 'withdrawal_request',
                'subject' => 'Withdrawal Request Received - ${{amount}}',
                'content' => '<h1>Withdrawal Request Received</h1><p>Hi {{username}},</p><p>We have received your withdrawal request for ${{amount}}.</p><p>Processing time: 24-48 hours</p><p>Method: {{method}}</p>',
                'variables' => 'username,amount,method',
                'is_active' => true,
            ],
            [
                'name' => 'Withdrawal Completed',
                'slug' => 'withdrawal_completed',
                'subject' => 'Withdrawal Completed - ${{amount}}',
                'content' => '<h1>Withdrawal Completed</h1><p>Hi {{username}},</p><p>Your withdrawal of ${{amount}} has been successfully processed.</p><p>Transaction Hash: {{transaction_hash}}</p>',
                'variables' => 'username,amount,transaction_hash',
                'is_active' => true,
            ],
            [
                'name' => 'Daily Earnings',
                'slug' => 'daily_earnings',
                'subject' => 'Daily Earnings Report - ${{earnings}}',
                'content' => '<h1>Daily Earnings Report</h1><p>Hi {{username}},</p><p>Your daily earnings: ${{earnings}}</p><p>Total balance: ${{balance}}</p><p>Keep earning with {{site_name}}!</p>',
                'variables' => 'username,earnings,balance,site_name',
                'is_active' => false,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}