<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Category\Category;
use App\Models\Unit\Unit;

class Product extends Model {
    protected $table = 'product_masters';

    protected $fillable = [
        'name', 'ben_name', 'product_code', 'product_des', 
        'sku', 'cat_id', 'is_packet', 'uom', 'hsn_code', 
        'pro_size', 'is_active', 'is_deleted'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    public function images(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id');
    }

    public function uomRelation(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'uom');
    }
}