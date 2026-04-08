<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model {
    // Matching your specific spelling
    protected $table = 'product_images';

    protected $guarded = ['id']; // Allow mass assignment for all image columns
}