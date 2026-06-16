<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPaymentDetail extends Model
{
    use HasFactory;

    protected $table = 'bill_payment_details';

    protected $fillable = [
        'std_id',
        'bill_no',
        'payment_mode',
        'transaction_no',
        'phone',
        'total_amount',
        'recived_money',
        'refund_money',
        'bill_month',
        'bill_year',
        'payment_status',
        'cash_transfer_status',
        'cus_name',
        'age'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'recived_money' => 'decimal:2',
        'refund_money' => 'decimal:2',
        'payment_status' => 'integer',
        'payment_mode' => 'integer',
        'bill_month' => 'integer',
        'bill_year' => 'integer',
        'age' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_UNPAID = 0;
    const STATUS_PAID   = 1;
    const STATUS_FAILED = 2;

    public function storeTransfer()
    {
        return $this->belongsTo(\App\Models\StoreStock\StoreTransferDetail::class, 'std_id');
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('bill_month', now()->month)
                     ->where('bill_year', now()->year);
    }
}