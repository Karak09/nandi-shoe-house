<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WardMaster extends Model {

    protected $table = 'ward_masters';

    protected $fillable = 
    [
        'name', 
        'municipality_id', 
        'is_active', 
        'is_delete'
    ];

    public function municipality() 
    { 
        return $this->belongsTo(MunicipalityMaster::class, 'municipality_id'); 
    }
}
