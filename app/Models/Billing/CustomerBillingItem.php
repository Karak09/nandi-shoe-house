<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBillingItem extends Model
{
    use HasFactory;
    protected $table = 'customer_billing_items';

    protected $fillable = [
        'std_id',
        'sl_no',
        'product_name',
        'product_id',
        'cat_id',
        'pro_size',
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

    protected $casts = [
        'barcode_no' => 'array',
        'batch_no' => 'array',  
        'quantity' => 'decimal:2',
        'mrp_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'sl_no' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function storeTransfer()
    {
        return $this->belongsTo(\App\Models\StoreStock\StoreTransferDetail::class, 'std_id');
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->sale_price;
    }
}