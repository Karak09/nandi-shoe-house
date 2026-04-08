<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\{StateMaster, DistrictMaster,BlockMaster,GramPanchayatMaster,
                MunicipalityMaster,PostOfficeMaster,VillageMaster,WardMaster};

class VendorMaster extends Model
{
    use HasFactory;

    protected $table = 'vendor_masters';

    protected $fillable = [
        'vendor_name', 'owner_name', 'flat_no', 'address', 'state_id', 
        'district_id', 'area_type', 'block_id', 'gp_id', 'vill_id', 
        'post_id', 'muni_id', 'ward_id', 'pin', 'contact_no', 'email', 
        'location', 'is_active', 'is_deleted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
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