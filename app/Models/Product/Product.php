<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Category\Category;
use App\Models\Unit\Unit;
use App\Models\Unit\Unit_Convert;
use App\Models\PriceMaster\PriceMaster;
use App\Models\Product\ProductImage;
use App\Models\StoreStock\StoreStock;
use App\Models\Colour\Colour;

class Product extends Model {
    protected $table = 'product_masters';

    protected $fillable = [
        'name', 'ben_name', 'product_code', 'product_des', 
        'sku', 'cat_id', 'is_packet', 'uom', 'hsn_code', 
        'pro_size', 'colour_id', 'is_active', 'is_deleted'
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

    public function priceMaster()
    {
        return $this->hasOne(PriceMaster::class, 'product_id', 'id');
    }

    public function productImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id', 'id');
    }

    public function storeStock()
    {
        return $this->hasMany(StoreStock::class, 'product_id', 'id');
    }

    public function unit() {
        return $this->belongsTo(Unit_Convert::class, 'uom', 'id');
    }

    public function colourRelation(): BelongsTo
    {
        return $this->belongsTo(Colour::class, 'colour_id');
    }
}