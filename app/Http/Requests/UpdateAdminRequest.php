<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Decrypt the encrypted token to get the real admin ID for unique exclusion
        $token = $this->route('token') ?? $this->route('id');
        $id = null;
        if ($token) {
            try { $id = decrypt($token); } catch (\Exception $e) { $id = $token; }
        }

        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:tbl_admin,email,' . $id,
            'username' => 'required|string|max:50|unique:tbl_admin,username,' . $id,
            'role_id'  => 'required|exists:tbl_admin_roles,id',
        ];

        if ($this->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }
}
