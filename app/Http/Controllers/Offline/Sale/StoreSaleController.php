<?php

namespace App\Http\Controllers\Offline\Sale;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Stores\StoreMaster;
use App\Models\Product\Product;
use App\Models\StoreStock\StoreStock;
use App\Models\StoreStock\StoreStockDetails;
use App\Models\StoreStock\StoreTransferDetail;
use App\Models\Billing\BillPaymentDetail;
use App\Models\Billing\CustomerBillingItem;

class StoreSaleController extends CommonController
{
    public function index()
    {
        $user = Auth::user();
        $userType = $user->user_type ?? ($user->userType->u_type ?? 'User');

        $stores = collect();
        $productsData = collect();

        if (in_array($userType, ['Admin', 'Super Admin'])) {
            $stores = StoreMaster::where('is_active', true)->get();
        } else {
            $productsData = $this->fetchStoreProductsQuery($user->store_id);
        }

        return view('Offline.Sale.storesale', compact('stores', 'productsData', 'user'));
    }

    public function getStoreProducts($storeId)
    {
        try {
            $products = $this->fetchStoreProductsQuery($storeId);
            return response()->json(['status' => 'success', 'data' => $products]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function fetchStoreProductsQuery($storeId)
    {
        if (!$storeId) return collect();

        $stockDetails = DB::table('store_stock_details as ssd')
            ->join('product_masters as p', 'ssd.product_id', '=', 'p.id')
            ->leftJoin('price_masters as pm', 'p.id', '=', 'pm.product_id')
            ->leftJoin('unit_masters as u', 'p.uom', '=', 'u.id')
            ->leftJoin('colour_masters as clr', 'p.colour_id', '=', 'clr.id')
            ->leftJoin('product_images as img', 'p.id', '=', 'img.product_id')
            ->where('ssd.store_id', $storeId)
            ->select(
                'ssd.product_id',
                'ssd.barcode_no',
                'ssd.transaction_type',
                'ssd.quantity',
                'p.name as product_name',
                'p.product_code',
                'p.pro_size',
                'p.cat_id',
                'p.uom as uom_id',
                'u.name as uom_name',
                'u.keyword as uom_keyword',
                'clr.colour_name',
                'pm.pro_sale_price',
                'pm.pro_mrp_price',
                'img.fst_image_doc'
            )
            ->get();
    
        $groupedStock = [];

        foreach ($stockDetails as $row) {
            // Clean barcode (removes JSON brackets/quotes if stored incorrectly previously)
            $cleanBarcode = str_replace(['[', ']', '"', '\\'], '', $row->barcode_no);
            $key = $row->product_id . '_' . $cleanBarcode;

            if (!isset($groupedStock[$key])) {
                $imageUrl = !empty($row->fst_image_doc) ? asset('storage/' . $row->fst_image_doc) : null;

                $groupedStock[$key] = [
                    'cart_id' => $key,
                    'id' => $row->product_id,

                    'name' => $row->product_name,
                    'pure_name' => $row->product_name,
                    'product_code' => $row->product_code ?? '',
                    'colour_name' => $row->colour_name ?? '',

                    'size' => $row->pro_size ?? '',
                    'barcode' => $cleanBarcode,
                    'cat_id' => $row->cat_id ?? 0,
                    'uom_id' => $row->uom_id,
                    'uom_name' => $row->uom_keyword ?? $row->uom_name ?? 'PCS',
                    'price' => (float)($row->pro_sale_price ?? 0),
                    'mrp' => (float)($row->pro_mrp_price ?? 0),
                    'stock' => 0,
                    'image' => $imageUrl,
                ];
            }

            // Math: Transaction Type 1 (Add) minus Type 2 & 3 (Deduce)
            if ($row->transaction_type == 1) {
                $groupedStock[$key]['stock'] += (float)$row->quantity;
            } elseif (in_array($row->transaction_type, [2, 3])) {
                $groupedStock[$key]['stock'] -= (float)$row->quantity;
            }
        }

        return collect($groupedStock)->where('stock', '>', 0)->values();
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer',
            'customer_name' => 'required|string|max:120',
            'customer_phone' => 'required|digits:10',
            'customer_age' => 'required|integer|min:1',
            'payment_mode' => 'required|integer|in:1,2,3',
            'recived_money' => 'nullable|numeric|min:0',
            'refund_money' => 'nullable|numeric|min:0',
            'transaction_no' => 'nullable|digits:5',
            'cart' => 'required|array|min:1',
        ]);

        if (in_array($request->payment_mode, [2, 3]) && empty($request->transaction_no)) {
            return response()->json(['status' => 'error', 'message' => 'Transaction No is required for UPI/Card payments.'], 422);
        }

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $storeId = $request->store_id;
            $cart = $request->cart;
            $totalAmount = 0;

            foreach ($cart as $item) {
                // We query by searching for the barcode in JSON format or pure string
                $stockQuery = StoreStockDetails::where('store_id', $storeId)
                    ->where('product_id', $item['id'])
                    ->where('barcode_no', 'LIKE', '%' . $item['barcode'] . '%')
                    ->select(DB::raw('SUM(CASE WHEN transaction_type = 1 THEN quantity WHEN transaction_type IN (2, 3) THEN -quantity ELSE 0 END) as available_qty'))
                    ->lockForUpdate()
                    ->first();
                
                $availQty = $stockQuery->available_qty ?? 0;

                // EXACT ERROR MESSAGE REQUESTED BY YOU
                if ($availQty < $item['qty']) {
                    throw new \Exception("enter '" . $item['pure_name'] . "' have " . $availQty . " " . $item['uom_name'] . ", you sale " . $item['qty'] . " " . $item['uom_name']);
                }

                $totalAmount += ($item['price'] * $item['qty']);
            }

            // 2. Transfer Detail
            $transferDetail = new StoreTransferDetail();
            $transferDetail->user_id = $user->id;
            $transferDetail->store_id = $storeId;
            $transferDetail->transfer_type = 1;
            $transferDetail->total_amount = $totalAmount;
            $transferDetail->ip_address = $request->ip();
            $transferDetail->transfer_no = 'TEMP';
            $transferDetail->save();

            $transferNo = 'TRN-POS-' . str_pad($transferDetail->id, 6, '0', STR_PAD_LEFT);
            $transferDetail->transfer_no = $transferNo;
            $transferDetail->save();

            // 3. Bill Payment Detail
            $billNo = 'CUS-' . date('y') . str_pad($transferDetail->id, 5, '0', STR_PAD_LEFT);
            
            $bill = BillPaymentDetail::create([
                'std_id' => $transferDetail->id,
                'bill_no' => $billNo,
                'payment_mode' => $request->payment_mode,
                'phone' => $request->customer_phone,
                'cus_name' => $request->customer_name,
                'age' => $request->customer_age, // NEW FIELD
                'total_amount' => $totalAmount,
                'recived_money' => $request->recived_money ?? $totalAmount, // NEW FIELD
                'refund_money' => $request->refund_money ?? 0, // NEW FIELD
                // 'transaction_no' => $request->transaction_no ?? null, // NEW FIELD
                'transaction_no' => 0,
                'dew_money' => 0.00,
                'bill_month' => Carbon::now()->month,
                'bill_year' => Carbon::now()->year,
                'payment_status' => 1, 
            ]);

            // 4. Cart Items & Stock Deductions
            $slNo = 1;
            foreach ($cart as $item) {
                // Strictly store Barcode as JSON and Batch as NULL
                $jsonBarcode = json_encode([$item['barcode']]);
                
                CustomerBillingItem::create([
                    'std_id' => $transferDetail->id,
                    'sl_no' => $slNo++,
                    'product_name' => $item['pure_name'], // ONLY Pure name, no size appended
                    'product_id' => $item['id'],
                    'cat_id' => $item['cat_id'],
                    'uom' => $item['uom_id'],
                    'quantity' => $item['qty'],
                    'mrp_price' => $item['mrp'],
                    'unit_price' => $item['price'], 
                    'sale_price' => $item['price'],
                    'barcode_no' => $jsonBarcode, 
                    'batch_no' => null // Set NULL explicitly
                ]);

                // StoreStockDetail
                StoreStockDetails::create([
                    'user_id' => $user->id, // User ID Added
                    'store_id' => $storeId,
                    'product_id' => $item['id'],
                    'barcode_no' => $jsonBarcode, // JSON Format
                    'batch_no' => null, // NULL explicitly
                    'transaction_type' => 2, // Deduction
                    'quantity' => $item['qty'],
                    'reference_no' => $transferNo,
                    'remarks' => 'In-Store POS Sale'
                ]);

                // Master StoreStock
                StoreStock::where('store_id', $storeId)
                    ->where('product_id', $item['id'])
                    ->decrement('quantity', $item['qty']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'bill_id' => $bill->id // Return Bill ID to print
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500); // Front-end intercepts this for Toastr
        }
    }

    // New Print Function
    public function printBill($bill_id)
    {
        $bill = BillPaymentDetail::where('id', $bill_id)->firstOrFail();
        $challan = StoreTransferDetail::with(['storeStockDetails', 'customerBillingItems', 'user'])
                        ->where('id', $bill->std_id)->firstOrFail();
        
        return view('Offline.Sale.print_bill', compact('bill', 'challan'));
    }
}
