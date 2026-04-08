<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTypeMaster extends Model
{
    use HasFactory;

    protected $table = 'user_type_masters';

    protected $fillable = [
        'u_type', 'is_active', 'is_delete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_type_id');
    }
}