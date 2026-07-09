<?php
namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Unit\Unit;
use App\Models\Users\User;
use App\Models\Stores\StoreMaster;
use App\Models\Combo\ComboProduct;
use App\Models\Requisition\Requisition;

class StoreStockDetails extends Model {
    protected $table = 'store_stock_details';
    
    protected $fillable = [
        'transaction_type','purchase_details_id','combo_id','requisition_details_id',
        'store_transfer_id',
        'user_id', 'store_id', 'received_from', 
        'product_id', 'quantity', 'uom', 'mrp', 'unit_price', 'total_price', 
        'batch_no', 'barcode_no', 'no_of_pack', 'each_pack_quantity', 'gst', 
        'cgst', 'sgst', 'is_packet'
    ];
    
    protected $casts = ['batch_no' => 'array','barcode_no' => 'array'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); 
    }

    public function uomRelation()
    {  
        return $this->belongsTo(Unit::class, 'uom'); 
    }

    public function user()
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function store()
    {
        return $this->belongsTo(StoreMaster::class, 'store_id');
    }

    public function combo()
    {
        return $this->belongsTo(ComboProduct::class, 'combo_id');
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'requisition_details_id');
    }

    public function purchaseDetails()
    {
        return $this->belongsTo(\App\Models\Purchased\PurchaseDetails::class, 'purchase_details_id');
    }
}