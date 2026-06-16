<?php

namespace App\Models\Requisition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $table = 'requisition_details';

    protected $fillable = [
        'user_id',
        'where_req',
        'req_store_id',
        'req_at',
        'send_store_id',
        'total_amount',
        'approved_total_amount',
        'status',
        'ip_address',
        'req_id', 
        'remarks',
        'remarks1',
        'remarks2',
        'remarks3',
        'approved_by',
        'approved_at',
        'modified_by',
        'modified_at',
        'rejected_by',
        'rejected_at',
    ];

    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'req_details_id', 'id');
    }
}