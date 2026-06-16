<?php

namespace App\Models\Requisition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Unit\Unit;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'requisition_items';
    protected $fillable = [
        'req_details_id',
        'product_id',
        'quantity',
        'price',
        'uom',
        'no_of_pack',
        'each_pack_quantity',
        'is_packet',
        'requested_unit_price',
        'requested_price', 
        'approved_quantity',
        'approved_unit_price',
        'approved_price',
        'ip_address',
    ];

    // Eloquent relationship to get the Product Name
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Eloquent relationship to get the UOM Name
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'uom', 'id');
    }
}