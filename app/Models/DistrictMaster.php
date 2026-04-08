<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistrictMaster extends Model
{
    use HasFactory;

    protected $table = 'district_masters';

    protected $fillable = [
        'name',
        'state_id',
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(StateMaster::class, 'state_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(BlockMaster::class, 'district_id');
    }
}