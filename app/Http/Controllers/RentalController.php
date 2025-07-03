<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $devices = Device::where('status', 'available')->get();
        
        return view('rentals.index', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'plan_type' => 'required|in:basic,standard,premium',
            'duration' => 'required|integer|min:30|max:365',
        ]);

        // Implementation for creating rental
        // This would handle payment processing, device assignment, etc.
        
        return redirect()->route('dashboard')->with('success', 'Rental created successfully!');
    }
}