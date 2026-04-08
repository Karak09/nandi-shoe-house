<?php

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{StateMaster, DistrictMaster,BlockMaster,GramPanchayatMaster,
                MunicipalityMaster,PostOfficeMaster,VillageMaster,WardMaster};

class StoreMaster extends Model {

    protected $table = 'store_masters';

    protected $fillable = [
        'store_name',
        'address',
        'flat_no',
        'state_id',
        'district_id',
        'area_type', 
        'block_id',
        'gp_id',
        'vill_id',
        'post_id',
        'muni_id',
        'ward_id',
        'pin',
        'contact_no',
        'email',
        'is_active',
        'is_deleted'
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(StateMaster::class, 'state_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(DistrictMaster::class, 'district_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(BlockMaster::class, 'block_id');
    }

    public function gramPanchayat(): BelongsTo
    {
        return $this->belongsTo(GramPanchayatMaster::class, 'gp_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(MunicipalityMaster::class, 'muni_id');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(VillageMaster::class, 'vill_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(WardMaster::class, 'ward_id');
    }

    public function postOffice(): BelongsTo
    {
        return $this->belongsTo(PostOfficeMaster::class, 'post_id');
    }
}