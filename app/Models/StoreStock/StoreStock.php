<?php
namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Unit\Unit;
use App\Models\Store\StoreMaster;

class StoreStock extends Model {
    protected $table = 'store_stocks';
    protected $fillable = [
        'store_id', 'product_id', 'quantity', 'uom', 'no_of_pack', 
        'each_pack_quantity', 'is_packet', 'is_active', 'is_deleted'
    ];

    public function product() { return $this->belongsTo(Product::class, 'product_id'); }
    public function uomRelation() { return $this->belongsTo(Unit::class, 'uom'); }
    public function store() { return $this->belongsTo(StoreMaster::class, 'store_id'); }
}