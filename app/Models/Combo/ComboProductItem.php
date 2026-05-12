<?php

namespace App\Models\Combo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;

class ComboProductItem extends Model
{
    use HasFactory;

    protected $table = 'combo_product_items';

    protected $fillable = [
        'combo_id',
        'product_id',
        'quantity',
        'uom_id',
        'no_of_pack',
        'each_pack_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Belongs to combo
    public function combo()
    {
        return $this->belongsTo(ComboProduct::class, 'combo_id');
    }

    // Belongs to product (used item)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}