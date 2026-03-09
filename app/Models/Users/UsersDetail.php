<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
// use App\Models\StateMaster; // Uncomment when you have these models
// use App\Models\DistrictMaster;

class UserDetails extends Model
{
    use HasFactory;

    protected $table = 'users_details';

    protected $fillable = [
        'f_name', 'l_name', 'user_name', 'dob', 'mobile', 'vrfy_mobile', 
        'otp_mobile', 'email', 'vrfy_email', 'otp_email', 'address', 
        'gender', 'state_id', 'district_id', 'pin', 'image_doc', 
        'image_file_name', 'proof_doc', 'proof_file_name', 'date_of_reg', 
        'verify_date', 'verify_by', 'verify_status_id', 'is_active', 
        'is_deleted', 'img_upload_ip', 'img_change_ip', 'deleted_by', 
        'deleted_date', 'deleted_ip'
    ];

    protected $casts = [
        'dob' => 'date',
        'vrfy_mobile' => 'boolean',
        'date_of_reg' => 'datetime',
        'verify_date' => 'datetime',
        'deleted_date' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_details_id');
    }

    // public function state() { return $this->belongsTo(StateMaster::class, 'state_id'); }
    // public function district() { return $this->belongsTo(DistrictMaster::class, 'district_id'); }
}