<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name'        => 'required|string|max:50',
            'slug'        => 'required|string|max:50|unique:tbl_admin_roles,slug,' . $id,
            'description' => 'nullable|string|max:255',
            'level'       => 'required|integer|min:1|max:99',
        ];
    }
}
