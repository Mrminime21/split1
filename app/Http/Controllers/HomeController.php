<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'available_devices' => Device::where('status', 'available')->count(),
            'total_devices' => Device::count(),
            'uptime_percentage' => Device::avg('uptime_percentage') ?? 99.0,
        ];

        return view('home', compact('stats'));
    }
}