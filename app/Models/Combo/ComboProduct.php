<?php

namespace App\Models\Combo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Stores\StoreMaster; // ✅ correct
use App\Models\Users\User;

class ComboProduct extends Model
{
    use HasFactory;

    protected $table = 'combo_products';

    protected $fillable = [
        'user_id',
        'store_id',
        'combo_code',
        'product_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Combo belongs to a product (final combo product)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Combo has many items (recipe)
    public function items()
    {
        return $this->hasMany(ComboProductItem::class, 'combo_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function store()
    {
        return $this->belongsTo(StoreMaster::class, 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}