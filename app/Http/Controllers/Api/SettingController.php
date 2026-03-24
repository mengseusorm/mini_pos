<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::getAllAsArray();

        // Attach logo URL (use the row that holds the logo media)
        $logoRow = Setting::where('key', 'site_name')->first();
        $settings['logo_url']   = $logoRow?->getFirstMediaUrl('logo') ?: null;
        $settings['logo_thumb'] = $logoRow?->getFirstMediaUrl('logo', 'thumb') ?: null;

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_name'        => 'sometimes|string|max:100',
            'site_description' => 'sometimes|string|max:255',
            'theme_color'      => 'sometimes|string|max:50',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(Setting::getAllAsArray());
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,webp,svg|max:2048',
        ]);

        // Store logo on the site_name setting row (acts as the "site settings" model)
        $row = Setting::firstOrCreate(
            ['key' => 'site_name'],
            ['value' => 'Mini POS']
        );

        $media = $row->addMediaFromRequest('logo')
            ->toMediaCollection('logo');

        return response()->json([
            'logo_url'   => $media->getUrl(),
            'logo_thumb' => $media->getUrl('thumb'),
        ]);
    }

    public function deleteLogo(): JsonResponse
    {
        $row = Setting::where('key', 'site_name')->first();
        $row?->clearMediaCollection('logo');

        return response()->json(['logo_url' => null, 'logo_thumb' => null]);
    }
}
