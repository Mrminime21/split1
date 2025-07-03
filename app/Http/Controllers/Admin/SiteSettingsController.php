<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $settings = SystemSetting::all()->groupBy('category');
        return view('admin.site-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            SystemSetting::where('setting_key', $key)->update([
                'setting_value' => $value,
                'updated_by' => auth('admin')->id(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Site settings updated successfully!');
    }

    public function create()
    {
        return view('admin.site-settings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'setting_key' => 'required|string|unique:system_settings',
            'setting_value' => 'required|string',
            'setting_type' => 'required|in:string,number,boolean,json,text',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        SystemSetting::create([
            'setting_key' => $request->setting_key,
            'setting_value' => $request->setting_value,
            'setting_type' => $request->setting_type,
            'category' => $request->category,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public'),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.site-settings.index')->with('success', 'Setting created successfully!');
    }
}