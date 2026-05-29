<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProduct extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_company_product';

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'base_price',
        'cost_price',
        'sale_price',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'weight',
        'length',
        'width',
        'height',
        'tax_status',
        'tax_class',
        'featured_image',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
