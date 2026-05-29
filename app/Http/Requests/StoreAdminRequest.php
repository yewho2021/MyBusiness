<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:tbl_admin,email',
            'username' => 'required|string|max:50|unique:tbl_admin,username',
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'required|exists:tbl_admin_roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }
}
