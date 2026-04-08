<?php
namespace App\Models\Purchased;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Vendor\VendorMaster; // Assuming you have this

class PurchaseDetails extends Model {
    protected $table = 'purchase_details';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function transactions(): HasMany {
        return $this->hasMany(PurchaseTransactionDetails::class, 'purchase_details_id');
    }
    
    public function vendor() {
        return $this->belongsTo(VendorMaster::class, 'vendor_id');
    }
}