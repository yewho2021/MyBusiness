<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id'       => 'required|exists:tbl_admin_menu_groups,id',
            'title'          => 'required|string|max:100',
            'icon'           => 'nullable|string|max:50',
            'route_name'     => 'nullable|string|max:100',
            'url'            => 'nullable|string|max:255',
            'permission_key' => 'nullable|string|max:100',
            'parent_id'      => 'nullable|exists:tbl_admin_menus,id',
            'sort_order'     => 'required|integer|min:0',
        ];
    }
}
