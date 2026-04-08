<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StateMaster extends Model
{
    use HasFactory;

    protected $table = 'state_masters';

    protected $fillable = [
        'name',
        'country_id', // Assuming you have a CountryMaster model
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(DistrictMaster::class, 'state_id');
    }
}