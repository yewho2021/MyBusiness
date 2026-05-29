<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBackupJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:100',
            'frequency'        => 'required|in:daily,weekly,monthly,custom',
            'cron_expression'  => 'nullable|string|max:50',
            'destination_path' => 'nullable|string|max:255',
            'include_paths'    => 'nullable|string',
            'exclude_paths'    => 'nullable|string',
            'include_database' => 'nullable|boolean',
            'retention_count'  => 'nullable|integer|min:1|max:100',
        ];
    }
}
