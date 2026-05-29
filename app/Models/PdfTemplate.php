<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    protected $table = 'tbl_pdf_templates';

    protected $fillable = [
        'name',
        'description',
        'html_content',
        'paper_size',
        'orientation',
        'margins',
        'created_by',
    ];

    protected $casts = [
        'margins' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function getMarginsOrDefault(): array
    {
        return $this->margins ?? ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10];
    }
}
