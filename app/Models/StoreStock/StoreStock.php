<?php
namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Product\Unit;
use App\Models\Store\StoreMaster;

class StoreStock extends Model {
    protected $table = 'store_stocks';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function product() { return $this->belongsTo(Product::class, 'product_id'); }
    public function uomRelation() { return $this->belongsTo(Unit::class, 'uom'); }
    public function store() { return $this->belongsTo(StoreMaster::class, 'store_id'); }
}