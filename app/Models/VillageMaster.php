<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VillageMaster extends Model {

    protected $table = 'village_masters';

    protected $fillable = 
    [
        'name', 
        'gram_panchayat_id', 
        'is_active', 
        'is_delete'
    ];

    public function gramPanchayat() 
    { 
        return $this->belongsTo(GramPanchayatMaster::class, 'gram_panchayat_id'); 
    }
}