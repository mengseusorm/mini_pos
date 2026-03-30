<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllAsArray();
        $logoRow  = Setting::firstOrCreate(['key' => 'logo'], ['value' => '']);
        $logoUrl  = $logoRow->getFirstMediaUrl('logo') ?: null;

        return view('settings.index', compact('settings', 'logoUrl'));
    }

    public function update(SettingRequest $request)
    {

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
