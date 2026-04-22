<?php

namespace App\Models\Purchased;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\Product;
use App\Models\Unit\Unit;

class PurchasedStock extends Model {
    // FIX: Must match migration table name exactly
    protected $table = 'purchased_stocks'; 
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'product_id', 'quantity', 'uom', 'no_of_pack', 'each_pack_quantity','is_packet'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function uomRelation(): BelongsTo {
        return $this->belongsTo(Unit::class, 'uom');
    }
}