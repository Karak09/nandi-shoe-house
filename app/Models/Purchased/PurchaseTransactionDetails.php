<?php

namespace App\Models\Purchased;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\Product;
use App\Models\Unit\Unit;

class PurchaseTransactionDetails extends Model {
    protected $table = 'purchase_transaction_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    protected $casts = [
        'batch_no' => 'array', // Automatically handles the JSON casting
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
}