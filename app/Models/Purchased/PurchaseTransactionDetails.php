<?php

namespace App\Models\Purchased;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\Product;
use App\Models\Unit\Unit;

class PurchaseTransactionDetails extends Model {
    protected $table = 'purchase_transaction_details';
    
    protected $fillable = [
        'purchase_details_id', 'store_id', 'product_id', 'quantity', 'uom', 
        'mfg_date', 'exp_date', 'mrp', 'unit_price','total_price','batch_no',
        'no_of_pack','each_pack_quantity','gst','cgst','sgst','is_packet','transaction_type'
    ];
    
    protected $casts = [
        'batch_no' => 'array', 
    ];

    public function purchaseDetails(): BelongsTo {
        return $this->belongsTo(PurchaseDetails::class, 'purchase_details_id');
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function uomRelation(): BelongsTo {
        return $this->belongsTo(Unit::class, 'uom');
    }

    public function store(): BelongsTo 
    {
        return $this->belongsTo(\App\Models\Stores\StoreMaster::class, 'store_id');
    }
    
}