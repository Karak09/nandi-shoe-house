<?php
namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Product\Unit;

class StoreStockDetails extends Model {
    protected $table = 'store_stock_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = ['batch_no' => 'array'];

    public function product() { return $this->belongsTo(Product::class, 'product_id'); }
    public function uomRelation() { return $this->belongsTo(Unit::class, 'uom'); }
}