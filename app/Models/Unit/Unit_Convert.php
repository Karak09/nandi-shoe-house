<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit_Convert extends Model {
    protected $table = 'unit_convert_masters';
    protected $fillable = [
        'name', 'from_unit', 'to_unit', 'unit_factor', 
        'price_factor', 'packet', 'is_active', 'is_deleted'
    ];

    public function fromUnit(): BelongsTo {
        return $this->belongsTo(Unit::class, 'from_unit');
    }

    public function toUnit(): BelongsTo {
        return $this->belongsTo(Unit::class, 'to_unit');
    }
}