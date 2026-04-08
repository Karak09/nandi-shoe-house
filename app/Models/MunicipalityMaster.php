<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MunicipalityMaster extends Model {

    protected $table = 'municipality_masters';
    protected $fillable = 
    [
        'name', 
        'type', 
        'district_id', 
        'is_active', 
        'is_delete'
    ];

    public function district() 
    { 
        return $this->belongsTo(DistrictMaster::class, 'district_id'); 
    }
}