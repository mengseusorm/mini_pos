<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name'        => 'sometimes|string|max:100',
            'site_description' => 'sometimes|string|max:255',
            'theme_color'      => 'sometimes|in:blue,green,purple,red',
            'tax_rate'         => 'sometimes|numeric|min:0|max:100',
            'currency'         => 'sometimes|string|max:10',
            'logo'             => 'sometimes|file|image|max:2048',
        ];
    }
}
