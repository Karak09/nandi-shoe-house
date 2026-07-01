<?php

namespace App\Models\Colour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colour extends Model
{
    use SoftDeletes;

    protected $table = 'colour_masters';

    protected $fillable = [
        'colour_name',
        'colour_id',
        'is_active',
        'is_deleted',
    ];
}
