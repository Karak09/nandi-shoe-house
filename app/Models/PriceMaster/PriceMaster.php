<?php

namespace App\Models\PriceMaster;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;

class PriceMaster extends Model
{
    use HasFactory;

    protected $table = 'price_masters';

    protected $fillable = [
        'product_id',
        'pro_mrp_price',
        'pro_sale_price',
        'pro_mrp_discount',
        'pro_mrp_discount_percentage',
        'pro_sale_discount',
        'pro_sale_discount_percentage',
        'pro_online',
        'pro_online_discount',
        'pro_online_discount_percentage',
        'pro_unit',
        'pro_per_unit_price',
        'pro_size',
        'cgst_rate',
        'sgst_rate',
        'gst_rate',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'pro_mrp_price' => 'decimal:2',
        'pro_sale_price' => 'decimal:2',
        'pro_mrp_discount' => 'decimal:2',
        'pro_mrp_discount_percentage' => 'decimal:2',
        'pro_sale_discount' => 'decimal:2',
        'pro_sale_discount_percentage' => 'decimal:2',
        'pro_online' => 'decimal:2',
        'pro_online_discount' => 'decimal:2',
        'pro_online_discount_percentage' => 'decimal:2',
        'pro_unit' => 'decimal:2',
        'pro_per_unit_price' => 'decimal:2',
        'pro_size' => 'decimal:2',
        'cgst_rate' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_deleted', false);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}