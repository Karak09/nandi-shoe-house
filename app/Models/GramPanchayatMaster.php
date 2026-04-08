<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GramPanchayatMaster extends Model {
    protected $table = 'gram_panchayat_masters';

    protected $fillable = 
    [
        'name', 
        'block_id', 
        'is_active', 
        'is_delete'
    ];

    public function block() 
    { 
        return $this->belongsTo(BlockMaster::class, 'block_id'); 
    }
}