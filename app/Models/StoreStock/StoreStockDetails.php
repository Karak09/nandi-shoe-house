<?php
namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Unit\Unit;
use App\Models\Users\User;
use App\Models\Stores\StoreMaster;

class StoreStockDetails extends Model {
    protected $table = 'store_stock_details';
    
    // ADDED the new columns
    protected $fillable = [
        'transaction_type','purchase_details_id','combo_id', 'user_id', 'store_id', 'received_from', 
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
}