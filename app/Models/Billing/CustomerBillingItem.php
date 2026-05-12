<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBillingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'customer_billing_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'std_id',
        'sl_no',
        'product_name',
        'product_id',
        'cat_id',
        'product_code',
        'quantity',
        'barcode_no',
        'batch_no',
        'uom',
        'mrp_price',
        'sale_price',
        'discount_price',
        'discount_percentage',
        'each_packet_quantity'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'barcode_no' => 'array', // Automatically handles JSON to Array
        'batch_no' => 'array',   // Automatically handles JSON to Array
        'quantity' => 'decimal:2',
        'mrp_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'sl_no' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Link to Store Transfer Details (Parent transaction)
     */
    public function storeTransfer()
    {
        return $this->belongsTo(\App\Models\StoreStock\StoreTransferDetail::class, 'std_id');
    }

    /**
     * Helper: Calculate total for this specific line item
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->sale_price;
    }
}