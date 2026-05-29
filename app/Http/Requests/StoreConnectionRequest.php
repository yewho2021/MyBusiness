<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:100',
            'dbhost'     => 'required|string|max:255',
            'dbport'     => 'nullable|string|max:10',
            'dbname'     => 'required|string|max:255',
            'dbusername' => 'required|string|max:255',
            'dbpassword' => 'required|string|max:255',
            'description'=> 'nullable|string|max:1000',
        ];
    }
}
