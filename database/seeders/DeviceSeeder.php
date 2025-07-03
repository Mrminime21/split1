<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'id' => Str::uuid(),
                'device_id' => 'SRR001',
                'name' => 'Premium Router Alpha',
                'model' => 'Star Gen3',
                'location' => 'New York, USA',
                'status' => 'available',
                'daily_rate' => 15.00,
                'max_speed_down' => 200,
                'max_speed_up' => 20,
                'uptime_percentage' => 99.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'device_id' => 'SRR002',
                'name' => 'Premium Router Beta',
                'model' => 'Star Gen3',
                'location' => 'London, UK',
                'status' => 'available',
                'daily_rate' => 18.00,
                'max_speed_down' => 250,
                'max_speed_up' => 25,
                'uptime_percentage' => 99.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'device_id' => 'SRR003',
                'name' => 'Premium Router Gamma',
                'model' => 'Star Enterprise',
                'location' => 'Tokyo, Japan',
                'status' => 'available',
                'daily_rate' => 25.00,
                'max_speed_down' => 300,
                'max_speed_up' => 30,
                'uptime_percentage' => 99.9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'device_id' => 'SRR004',
                'name' => 'Premium Router Delta',
                'model' => 'Star Gen3',
                'location' => 'Sydney, Australia',
                'status' => 'available',
                'daily_rate' => 20.00,
                'max_speed_down' => 220,
                'max_speed_up' => 22,
                'uptime_percentage' => 99.7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'device_id' => 'SRR005',
                'name' => 'Premium Router Epsilon',
                'model' => 'Star Enterprise',
                'location' => 'Frankfurt, Germany',
                'status' => 'available',
                'daily_rate' => 22.00,
                'max_speed_down' => 280,
                'max_speed_up' => 28,
                'uptime_percentage' => 99.6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('devices')->insert($devices);
    }
}