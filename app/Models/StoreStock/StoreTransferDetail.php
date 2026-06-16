<?php

namespace App\Models\StoreStock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Billing\CustomerBillingItem;
use App\Models\StoreStock\StoreStockDetails;
use App\Models\Users\User;
use App\Models\Stores\StoreMaster;

class StoreTransferDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'store_transfer_details';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'store_id',
        'transfer_type',
        'transfer_no',
        'total_amount',
        'ip_address'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'transfer_type' => 'integer',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constants for transfer_type for better code readability
     */
    const TYPE_CUSTOMER    = 1;
    const TYPE_ONLINE      = 2;
    const TYPE_THIRD_PARTY = 3;
    const TYPE_REQUISITION = 4;

    /**
     * Get the user who performed the transfer.
     */
    public function user()
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    /**
     * Get the store where the transfer originated.
     */
    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id');
    }

     public function storeStockDetails()
    {
        return $this->hasMany(
            StoreStockDetails::class,
            'store_id',
            'store_id'
        );
    }

    // ADD THIS
    public function customerBillingItems()
    {
        return $this->hasMany(
            CustomerBillingItem::class,
            'std_id',
            'id'
        );
    }

    /**
     * Helper to get the human-readable transfer type label.
     */
    public function getTransferTypeLabelAttribute()
    {
        return match ($this->transfer_type) {
            self::TYPE_CUSTOMER    => 'Customer',
            self::TYPE_ONLINE      => 'Online',
            self::TYPE_THIRD_PARTY => '3rd Party',
            self::TYPE_REQUISITION => 'Requisition',
            default                => 'Unknown',
        };
    }
}