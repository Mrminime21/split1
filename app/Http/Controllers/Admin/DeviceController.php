<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Device::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('device_id', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $devices = $query->withCount('rentals')->latest()->paginate(20);

        return view('admin.devices.index', compact('devices'));
    }

    public function create()
    {
        return view('admin.devices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|unique:devices',
            'name' => 'required|string|max:100',
            'model' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'daily_rate' => 'required|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'max_speed_down' => 'required|integer|min:1',
            'max_speed_up' => 'required|integer|min:1',
            'uptime_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:available,rented,maintenance,offline,reserved',
        ]);

        Device::create([
            'id' => Str::uuid(),
            'device_id' => $request->device_id,
            'name' => $request->name,
            'model' => $request->model,
            'location' => $request->location,
            'daily_rate' => $request->daily_rate,
            'setup_fee' => $request->setup_fee ?? 0,
            'max_speed_down' => $request->max_speed_down,
            'max_speed_up' => $request->max_speed_up,
            'uptime_percentage' => $request->uptime_percentage,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.devices.index')->with('success', 'Device created successfully!');
    }

    public function show(Device $device)
    {
        $device->load(['rentals.user']);
        return view('admin.devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        return view('admin.devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'device_id' => 'required|string|unique:devices,device_id,' . $device->id,
            'name' => 'required|string|max:100',
            'model' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'daily_rate' => 'required|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'max_speed_down' => 'required|integer|min:1',
            'max_speed_up' => 'required|integer|min:1',
            'uptime_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:available,rented,maintenance,offline,reserved',
        ]);

        $device->update($request->only([
            'device_id', 'name', 'model', 'location', 'daily_rate',
            'setup_fee', 'max_speed_down', 'max_speed_up', 'uptime_percentage', 'status'
        ]));

        return redirect()->route('admin.devices.show', $device)->with('success', 'Device updated successfully!');
    }

    public function destroy(Device $device)
    {
        if ($device->rentals()->where('status', 'active')->exists()) {
            return redirect()->back()->with('error', 'Cannot delete device with active rentals!');
        }

        $device->delete();
        return redirect()->route('admin.devices.index')->with('success', 'Device deleted successfully!');
    }
}