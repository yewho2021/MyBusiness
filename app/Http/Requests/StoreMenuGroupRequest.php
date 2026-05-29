<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'      => 'required|string|max:100',
            'slug'       => 'required|string|max:100|unique:tbl_admin_menu_groups,slug',
            'sort_order' => 'required|integer|min:0',
        ];
    }
}
