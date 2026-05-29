<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dbhost'     => 'required|string',
            'dbport'     => 'nullable|string',
            'dbname'     => 'required|string',
            'dbusername' => 'required|string',
            'dbpassword' => 'required|string',
        ];
    }
}
