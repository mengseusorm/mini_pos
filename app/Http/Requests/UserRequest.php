<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'unique:users,email' . ($userId ? ',' . $userId : '')],
            'password' => $this->isMethod('post')
                ? 'required|string|min:8|confirmed'
                : 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:admin,cashier',
        ];
    }
}
