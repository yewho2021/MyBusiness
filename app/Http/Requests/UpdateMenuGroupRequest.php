<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title'      => 'required|string|max:100',
            'slug'       => 'required|string|max:100|unique:tbl_admin_menu_groups,slug,' . $id,
            'sort_order' => 'required|integer|min:0',
        ];
    }
}
