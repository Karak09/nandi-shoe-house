<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockMaster extends Model
{
    use HasFactory;

    protected $table = 'block_masters';

    protected $fillable = [
        'name',
        'district_id',
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(DistrictMaster::class, 'district_id');
    }

    public function postOffices(): HasMany
    {
        return $this->hasMany(PostOfficeMaster::class, 'block_id');
    }
}