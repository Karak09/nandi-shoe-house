<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostOfficeMaster extends Model
{
    use HasFactory;

    protected $table = 'post_office_masters';

    protected $fillable = [
        'name',
        'block_id',
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(BlockMaster::class, 'block_id');
    }
}