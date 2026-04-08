<?php

namespace App\Models\Price;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\Product;

class Price extends Model {
    protected $table = 'price_masters';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Allows us to fetch the Product Name, SKU, and Base Size easily
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}