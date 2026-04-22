<?php
namespace App\Models\Purchased;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Vendor\VendorMaster;

class PurchaseDetails extends Model {
    protected $table = 'purchase_details';
    protected $fillable = [
        'user_id', 'vendor_id', 'challan_no', 'challan_date', 'total', 
        'command', 'ip_address', 'fst_image_doc', 'fst_image_file_name',
        'sec_image_doc','sec_image_file_name','trd_image_doc','trd_image_file_name',
        'foth_image_doc','foth_image_file_name','fiv_image_doc','fiv_image_file_name',
        'transaction_type'
    ];

    public function transactions(): HasMany {
        return $this->hasMany(PurchaseTransactionDetails::class, 'purchase_details_id');
    }
    
    public function vendor() {
        return $this->belongsTo(VendorMaster::class, 'vendor_id');
    }

    public function user() 
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'user_id');
    }

    public function storeStockDetails() 
    {
        // Ensure 'purchase_details_id' matches your database column name exactly
        return $this->hasMany(\App\Models\StoreStock\StoreStockDetails::class, 'purchase_details_id', 'id');
    }
}