<?php

namespace App\Models\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extending Authenticatable for login features
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users\UserDetail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';

    protected $fillable = [
        'user_details_id', 'username', 'login_id', 'user_type_id', 'password',
        'com_password', 'entry_time', 'exit_time', 'pwd_chng_count', 'pwd_chng_ip',
        'is_subscription', 'subscription_id', 'is_active', 'is_deleted',
        'latitude', 'longitude', 'entry_ip'
    ];

    protected $hidden = [
        'password',
        'com_password',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'is_subscription' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserTypeMaster::class, 'user_type_id');
    }

    public function details(): BelongsTo
    {
        // FIXED: Added 's' to UsersDetails
        return $this->belongsTo(UsersDetails::class, 'user_details_id'); 
    }

    // --- JWT METHODS ---
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Embed the role ID in the token so the frontend always knows who the user is
        return [
            'role_id' => $this->user_type_id
        ];
    }
}