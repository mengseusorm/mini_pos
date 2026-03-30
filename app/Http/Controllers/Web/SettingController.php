<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllAsArray();
        $logoRow  = Setting::where('key', 'site_name')->first();
        $logoUrl  = $logoRow?->getFirstMediaUrl('logo') ?: null;

        return view('settings.index', compact('settings', 'logoUrl'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'sometimes|string|max:100',
            'site_description' => 'sometimes|string|max:255',
            'theme_color'      => 'sometimes|string|max:50',
        ]);

        foreach ($request->only('site_name', 'site_description', 'theme_color') as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
