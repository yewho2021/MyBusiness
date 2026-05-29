<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get admin ID from middleware singleton (set by AdminAuthenticate)
        $adminId = $this->attributes->get('admin_id');
        if (!$adminId) {
            $admin = $this->attributes->get('admin');
            $adminId = $admin ? $admin->id : 0;
        }

        $rules = [
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:tbl_admin,email,' . $adminId,
        ];

        if ($this->filled('current_password') || $this->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required to set a new password.',
            'password.min'   => 'New password must be at least 8 characters.',
            'password.regex' => 'New password must contain at least one uppercase letter and one number.',
        ];
    }
}
