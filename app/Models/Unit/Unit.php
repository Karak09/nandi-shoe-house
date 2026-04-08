<?php

namespace App\Models\Unit;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model {
    protected $table = 'unit_masters';
    protected $fillable = ['name', 'keyword', 'is_active', 'is_deleted'];
}