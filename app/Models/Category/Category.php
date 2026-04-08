<?php

namespace App\Models\Category;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model {

    protected $table = 'category_masters';

    protected $fillable = [
        'name',
        'cat_id',
        'ben_name',
        'cat_code',
        'cat_des',
        'parent_id',
        'is_active',
        'is_deleted'
    ];

    // To get the parent category
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // To get sub-categories
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}