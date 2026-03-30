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
        $logoRow  = Setting::firstOrCreate(['key' => 'logo'], ['value' => '']);
        $logoUrl  = $logoRow->getFirstMediaUrl('logo') ?: null;

        return view('settings.index', compact('settings', 'logoUrl'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'sometimes|string|max:100',
            'site_description' => 'sometimes|string|max:255',
            'theme_color'      => 'sometimes|in:blue,green,purple,red',
            'tax_rate'         => 'sometimes|numeric|min:0|max:100',
            'currency'         => 'sometimes|string|max:10',
            'logo'             => 'sometimes|file|image|max:2048',
        ]);

        foreach ($request->only('site_name', 'site_description', 'theme_color', 'tax_rate', 'currency') as $key => $value) {
            if ($value !== null && $value !== '') {
                Setting::set($key, $value);
            }
        }

        if ($request->hasFile('logo')) {
            $logoRow = Setting::firstOrCreate(['key' => 'logo'], ['value' => '']);
            $logoRow->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
