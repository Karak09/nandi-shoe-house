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
            ->leftJoin('unit_convert_masters as u', 'p.uom', '=', 'u.id')
            ->leftJoin('product_images as img', 'p.id', '=', 'img.product_id')
            ->where('ssd.store_id', $storeId)
            ->select(
                'ssd.product_id',
                'ssd.barcode_no',
                'ssd.transaction_type',
                'ssd.quantity',
                'p.name as product_name',
                'p.pro_size',
                'p.cat_id',
                'p.uom as uom_id',
                'u.name as uom_name',
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

                    'size' => $row->pro_size ?? '',
                    'barcode' => $cleanBarcode,
                    'cat_id' => $row->cat_id ?? 0,
                    'uom_id' => $row->uom_id,
                    'uom_name' => $row->uom_name ?? 'PCS',
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
        // Backend Validations
        $request->validate([
            'store_id' => 'required|integer',
            'customer_name' => 'required|string|max:120',
            'customer_phone' => 'required|digits:10',
            'customer_age' => 'required|integer|min:1',
            'payment_mode' => 'required|integer',
            'recived_money' => 'nullable|numeric|min:0',
            'refund_money' => 'nullable|numeric|min:0',
            'transaction_no' => 'nullable|digits:5',
            'cart' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $storeId = $request->store_id;
            $cart = $request->cart;
            $totalAmount = 0;

            // 1. STRICT BACKEND STOCK VALIDATION
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



//           21.05.2026

// namespace App\Http\Controllers\Offline\Sale;

// use App\Http\Controllers\Controller;
// use App\Http\Controllers\Common\CommonController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// use App\Models\Stores\StoreMaster;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetails;
// use App\Models\StoreStock\StoreTransferDetail;
// use App\Models\Billing\BillPaymentDetail;
// use App\Models\Billing\CustomerBillingItem;

// class StoreSaleController extends CommonController
// {
//     public function index()
//     {
//         $user = Auth::user();
//         $userType = $user->user_type ?? ($user->userType->u_type ?? 'User');

//         $stores = collect();
//         $productsData = collect();

//         if (in_array($userType, ['Admin', 'Super Admin'])) {
//             $stores = StoreMaster::where('is_active', true)->get();
//         } else {
//             $productsData = $this->fetchStoreProductsQuery($user->store_id);
//         }

//         return view('Offline.Sale.storesale', compact('stores', 'productsData', 'user'));
//     }

//     public function getStoreProducts($storeId)
//     {
//         try {
//             $products = $this->fetchStoreProductsQuery($storeId);
//             return response()->json(['status' => 'success', 'data' => $products]);
//         } catch (\Throwable $e) {
//             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
//         }
//     }

//     private function fetchStoreProductsQuery($storeId)
//     {
//         if (!$storeId) return collect();

//         $stockDetails = DB::table('store_stock_details as ssd')
//             ->join('product_masters as p', 'ssd.product_id', '=', 'p.id')
//             ->leftJoin('price_masters as pm', 'p.id', '=', 'pm.product_id')
//             ->leftJoin('unit_convert_masters as u', 'p.uom', '=', 'u.id')
//             ->leftJoin('product_images as img', 'p.id', '=', 'img.product_id')
//             ->where('ssd.store_id', $storeId)
//             ->select(
//                 'p.id as product_id',
//                 'p.name as product_name',
//                 'p.pro_size',
//                 'p.cat_id',
//                 'p.uom as uom_id',
//                 'u.name as uom_name',
//                 'ssd.barcode_no',
//                 'ssd.batch_no',
//                 'pm.pro_sale_price',
//                 'pm.pro_mrp_price',
//                 'img.fst_image_file_name',
//                 DB::raw('SUM(CASE WHEN ssd.transaction_type = 2 OR ssd.transaction_type = 3 THEN -ssd.quantity ELSE ssd.quantity END) as available_qty')
//             )
//             ->groupBy(
//                 'p.id', 'p.name', 'p.pro_size', 'p.cat_id', 'p.uom', 'u.name',
//                 'ssd.barcode_no', 'ssd.batch_no', 'pm.pro_sale_price', 'pm.pro_mrp_price', 'img.fst_image_file_name'
//             )
//             ->having('available_qty', '>', 0)
//             ->get();

//         return $stockDetails->map(function ($row) {
//             // Fix Image Spaces Issue
//             $imageUrl = null;
//             if (!empty($row->fst_image_file_name)) {
//                 $safeImageName = str_replace(' ', '%20', $row->fst_image_file_name);
//                 $imageUrl = asset('storage/products/' . $safeImageName);
//             }

//             // Clean barcode & batch if they were saved as JSON strings previously
//             $cleanBarcode = str_replace(['[', ']', '"', '\\'], '', $row->barcode_no);
//             $cleanBatch = str_replace(['[', ']', '"', '\\'], '', $row->batch_no);

//             $displayName = $row->product_name;
//             if (!empty($row->pro_size)) {
//                 $displayName .= ' (Size ' . $row->pro_size . ')';
//             }

//             return [
//                 'cart_id' => $row->product_id . '_' . $cleanBarcode,
//                 'id' => $row->product_id,
//                 'pure_name' => $row->product_name, // Saves to DB
//                 'display_name' => $displayName,    // Shows in UI
//                 'barcode' => $cleanBarcode,
//                 'batch' => $cleanBatch,
//                 'cat_id' => $row->cat_id ?? 0,
//                 'uom_id' => $row->uom_id,
//                 'uom_name' => $row->uom_name ?? 'PCS',
//                 'price' => (float)($row->pro_sale_price ?? 0),
//                 'mrp' => (float)($row->pro_mrp_price ?? 0),
//                 'stock' => (float)$row->available_qty,
//                 'image' => $imageUrl,
//             ];
//         })->values();
//     }

//     public function checkout(Request $request)
//     {
//         $request->validate([
//             'store_id' => 'required|integer',
//             'customer_name' => 'required|string',
//             'customer_phone' => 'required|numeric|digits:10',
//             'customer_age' => 'required|numeric|min:1',
//             'payment_mode' => 'required|integer',
//             'cart' => 'required|array|min:1',
//         ]);

//         DB::beginTransaction();

//         try {
//             $user = Auth::user();
//             $storeId = $request->store_id;
//             $cart = $request->cart;

//             $totalAmount = 0;

//             // 1. Strict Server-Side Stock Validation
//             foreach ($cart as $item) {
//                 if ($item['qty'] <= 0) {
//                     throw new \Exception("Invalid quantity for product: " . $item['display_name']);
//                 }

//                 $stockQuery = StoreStockDetails::where('store_id', $storeId)
//                     ->where('product_id', $item['id'])
//                     ->where('barcode_no', 'LIKE', '%' . $item['barcode'] . '%') // Catch JSON or raw matches
//                     ->select(DB::raw('SUM(CASE WHEN transaction_type IN (2,3) THEN -quantity ELSE quantity END) as available_qty'))
//                     ->lockForUpdate()
//                     ->first();
                
//                 if (!$stockQuery || $stockQuery->available_qty < $item['qty']) {
//                     throw new \Exception("Insufficient stock for: " . $item['display_name'] . " (Available: " . ($stockQuery->available_qty ?? 0) . ", Requested: " . $item['qty'] . ")");
//                 }
//                 $totalAmount += ($item['price'] * $item['qty']);
//             }

//             // 2. Master Transfer Record
//             $transferDetail = new StoreTransferDetail();
//             $transferDetail->user_id = $user->id;
//             $transferDetail->store_id = $storeId;
//             $transferDetail->transfer_type = 1;
//             $transferDetail->total_amount = $totalAmount;
//             $transferDetail->ip_address = $request->ip();
//             $transferDetail->transfer_no = 'TEMP';
//             $transferDetail->save();

//             $transferNo = 'TRN-POS-' . str_pad($transferDetail->id, 6, '0', STR_PAD_LEFT);
//             $transferDetail->transfer_no = $transferNo;
//             $transferDetail->save();

//             // 3. Bill Payment Record
//             $billNo = 'CUS-' . date('y') . str_pad($transferDetail->id, 5, '0', STR_PAD_LEFT);
            
//             $bill = new BillPaymentDetail();
//             $bill->std_id = $transferDetail->id;
//             $bill->bill_no = $billNo;
//             $bill->payment_mode = $request->payment_mode;
//             $bill->phone = $request->customer_phone;
//             $bill->cus_name = $request->customer_name;
//             $bill->age = $request->customer_age;
//             $bill->total_amount = $totalAmount;
            
//             // Payment Mode Logic
//             if ($request->payment_mode == 1) { // Cash
//                 $bill->recived_money = $request->recv_amount ?? $totalAmount;
//                 $bill->refund_money = $request->refund_amount ?? 0;
//             } elseif ($request->payment_mode == 2) { // UPI
//                 $bill->recived_money = $totalAmount;
//                 $bill->refund_money = 0;
//                 // Store transaction number in cash_transfer_status temporarily or create column if exists
//                 $bill->cash_transfer_status = $request->transaction_no; 
//             } else { // Card
//                 $bill->recived_money = $totalAmount;
//                 $bill->refund_money = 0;
//             }

//             $bill->dew_money = 0.00;
//             $bill->bill_month = Carbon::now()->month;
//             $bill->bill_year = Carbon::now()->year;
//             $bill->payment_status = 1; 
//             $bill->save();

//             // 4. Cart Items & Deductions
//             $slNo = 1;
//             foreach ($cart as $item) {
//                 CustomerBillingItem::create([
//                     'std_id' => $transferDetail->id,
//                     'sl_no' => $slNo++,
//                     'product_name' => $item['pure_name'], // DB only gets pure name
//                     'product_id' => $item['id'],
//                     'cat_id' => $item['cat_id'],
//                     'uom' => $item['uom_id'],
//                     'quantity' => $item['qty'],
//                     'mrp_price' => $item['mrp'],
//                     'unit_price' => $item['price'], 
//                     'sale_price' => $item['price'],
//                     'barcode_no' => json_encode([$item['barcode']]), // Array format
//                     'batch_no' => json_encode([$item['batch']]), // Array format
//                 ]);

//                 // Deduct from Stock Details
//                 StoreStockDetails::create([
//                     'store_id' => $storeId,
//                     'product_id' => $item['id'],
//                     'barcode_no' => json_encode([$item['barcode']]), 
//                     'batch_no' => json_encode([$item['batch']]),
//                     'transaction_type' => 2, // Deduction
//                     'quantity' => $item['qty'],
//                     'reference_no' => $transferNo,
//                     'remarks' => 'In-Store POS Sale'
//                 ]);

//                 // Deduct from Master StoreStock
//                 StoreStock::where('store_id', $storeId)
//                     ->where('product_id', $item['id'])
//                     ->decrement('quantity', $item['qty']);
//             }

//             DB::commit();

//             return response()->json([
//                 'status' => 'success', 
//                 'transfer_id' => $transferDetail->id // Returning ID to trigger print
//             ]);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     // New Print Bill Logic
//     public function printBill($id)
//     {
//         $transfer = StoreTransferDetail::with(['billPayment', 'customerBillingItems', 'store'])->findOrFail($id);
        
//         $summary = [
//             'totalQty' => $transfer->customerBillingItems->sum('quantity'),
//             'subTotal' => $transfer->total_amount,
//             'totalCGST' => 0, // Calculate if applicable
//             'totalSGST' => 0, // Calculate if applicable
//             'netAmount' => $transfer->total_amount,
//             'roundOff' => 0,
//             'grandTotal' => $transfer->total_amount,
//         ];

//         // Number to words (Simple version for demo, use a package ideally)
//         $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
//         $amountInWords = strtoupper($f->format($summary['grandTotal']));

//         return view('Offline.Sale.print_bill', compact('transfer', 'summary', 'amountInWords'));
//     }
// }





//                    20/05/2026

// namespace App\Http\Controllers\Offline\Sale;

// use App\Http\Controllers\Controller;
// use App\Http\Controllers\Common\CommonController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// use App\Models\Stores\StoreMaster;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetails;
// use App\Models\StoreStock\StoreTransferDetail;
// use App\Models\Billing\BillPaymentDetail;
// use App\Models\Billing\CustomerBillingItem;

// class StoreSaleController extends CommonController
// {
//     public function index()
//     {
//         $user = Auth::user();
//         $userType = $user->user_type ?? ($user->userType->u_type ?? 'User');

//         $stores = collect();
//         $productsData = collect();

//         // Super Admin or Admin
//         if (in_array($userType, ['Admin', 'Super Admin'])) {
//             $stores = StoreMaster::where('is_active', true)->get();
//         } else {
//             // Sales Manager
//             $productsData = $this->fetchStoreProductsQuery($user->store_id);
//         }

//         return view('Offline.Sale.storesale', compact('stores', 'productsData', 'user'));
//     }

//     public function getStoreProducts($storeId)
//     {
//         try {
//             $products = $this->fetchStoreProductsQuery($storeId);
//             return response()->json([
//                 'status' => 'success',
//                 'data' => $products
//             ]);
//         } catch (\Throwable $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage() . ' line: ' . $e->getLine()
//             ], 500);
//         }
//     }

//     private function fetchStoreProductsQuery($storeId)
//     {
//         if (!$storeId) return collect();

//         // Fetch all stock details for the store to calculate manually in PHP
//         // This solves the issue of barcode formatting (["barcode"] vs "barcode")
//         $stockDetails = DB::table('store_stock_details as ssd')
//             ->join('product_masters as p', 'ssd.product_id', '=', 'p.id')
//             ->leftJoin('price_masters as pm', 'p.id', '=', 'pm.product_id')
//             ->leftJoin('unit_convert_masters as u', 'p.uom', '=', 'u.id')
//             ->leftJoin('product_images as img', 'p.id', '=', 'img.product_id')
//             ->where('ssd.store_id', $storeId)
//             ->select(
//                 'ssd.product_id',
//                 'ssd.barcode_no',
//                 'ssd.transaction_type',
//                 'ssd.quantity',
//                 'p.name as product_name',
//                 'p.pro_size',
//                 'p.cat_id',
//                 'p.uom as uom_id',
//                 'u.name as uom_name',
//                 'pm.pro_sale_price',
//                 'pm.pro_mrp_price',
//                 'img.fst_image_file_name'
//             )
//             ->get();

//         $groupedStock = [];

//         foreach ($stockDetails as $row) {
//             // Clean the barcode regardless of how it was saved in the DB
//             $rawBarcode = $row->barcode_no;
//             $cleanBarcode = str_replace(['[', ']', '"', '\\'], '', $rawBarcode);
            
//             $key = $row->product_id . '_' . $cleanBarcode;

//             if (!isset($groupedStock[$key])) {
//                 // Setup Product Image
//                 $imageUrl = null;
//                 if (!empty($row->fst_image_file_name)) {
//                     $imageUrl = asset('storage/products/' . $row->fst_image_file_name);
//                 }

//                 // Append Size
//                 $displayName = $row->product_name;
//                 if (!empty($row->pro_size)) {
//                     $displayName .= ' (Size ' . $row->pro_size . ')';
//                 }

//                 $groupedStock[$key] = [
//                     'cart_id' => $key,
//                     'id' => $row->product_id,
//                     'name' => $displayName,
//                     'barcode' => $cleanBarcode,
//                     'cat_id' => $row->cat_id ?? 0,
//                     'uom_id' => $row->uom_id,
//                     'uom_name' => $row->uom_name ?? 'PCS',
//                     'price' => (float)($row->pro_sale_price ?? 0),
//                     'mrp' => (float)($row->pro_mrp_price ?? 0),
//                     'stock' => 0,
//                     'image' => $imageUrl,
//                 ];
//             }

//             // Calculate Stock based on Transaction Type
//             // 1 = Add | 2 = Out/Sale | 3 = Out/Combo
//             if ($row->transaction_type == 1) {
//                 $groupedStock[$key]['stock'] += (float)$row->quantity;
//             } elseif (in_array($row->transaction_type, [2, 3])) {
//                 $groupedStock[$key]['stock'] -= (float)$row->quantity;
//             }
//         }

//         // Return only products that have a positive stock remaining
//         return collect($groupedStock)->where('stock', '>', 0)->values();
//     }

//     public function checkout(Request $request)
//     {
//         $request->validate([
//             'store_id' => 'required|integer',
//             'customer_name' => 'required|string|max:120',
//             'customer_phone' => 'required|string|max:15',
//             'payment_mode' => 'required|integer',
//             'cart' => 'required|array|min:1',
//         ]);

//         DB::beginTransaction();

//         try {
//             $user = Auth::user();
//             $storeId = $request->store_id;
//             $cart = $request->cart;

//             $totalAmount = 0;

//             foreach ($cart as $item) {
//                 $totalAmount += ($item['price'] * $item['qty']);
//             }

//             // Master Transfer Record
//             $transferDetail = new StoreTransferDetail();
//             $transferDetail->user_id = $user->id;
//             $transferDetail->store_id = $storeId;
//             $transferDetail->transfer_type = 1;
//             $transferDetail->total_amount = $totalAmount;
//             $transferDetail->ip_address = $request->ip();
//             $transferDetail->transfer_no = 'TEMP';
//             $transferDetail->save();

//             $transferNo = 'TRN-POS-' . str_pad($transferDetail->id, 6, '0', STR_PAD_LEFT);
//             $transferDetail->transfer_no = $transferNo;
//             $transferDetail->save();

//             // Bill Payment Record
//             $billNo = 'CUS-' . date('y') . str_pad($transferDetail->id, 5, '0', STR_PAD_LEFT);
            
//             BillPaymentDetail::create([
//                 'std_id' => $transferDetail->id,
//                 'bill_no' => $billNo,
//                 'payment_mode' => $request->payment_mode,
//                 'phone' => $request->customer_phone,
//                 'cus_name' => $request->customer_name,
//                 'total_amount' => $totalAmount,
//                 'recived_money' => $totalAmount, 
//                 'dew_money' => 0.00,
//                 'bill_month' => Carbon::now()->month,
//                 'bill_year' => Carbon::now()->year,
//                 'payment_status' => 1, 
//             ]);

//             // Cart Items & Deductions
//             $slNo = 1;
//             foreach ($cart as $item) {
//                 // Ensure barcode is stored as a JSON array exactly as your DB expects
//                 $jsonBarcode = json_encode([$item['barcode']]);

//                 CustomerBillingItem::create([
//                     'std_id' => $transferDetail->id,
//                     'sl_no' => $slNo++,
//                     'product_name' => $item['name'],
//                     'product_id' => $item['id'],
//                     'cat_id' => $item['cat_id'],
//                     'uom' => $item['uom_id'],
//                     'quantity' => $item['qty'],
//                     'mrp_price' => $item['mrp'],
//                     'unit_price' => $item['price'], 
//                     'sale_price' => $item['price'],
//                     'barcode_no' => $jsonBarcode, 
//                 ]);

//                 // Deduct from Stock Details
//                 StoreStockDetails::create([
//                     'store_id' => $storeId,
//                     'product_id' => $item['id'],
//                     'barcode_no' => $jsonBarcode, 
//                     'transaction_type' => 2, // Deduction
//                     'quantity' => $item['qty'],
//                     'reference_no' => $transferNo,
//                     'remarks' => 'In-Store POS Sale'
//                 ]);

//                 // Deduct from Master StoreStock
//                 StoreStock::where('store_id', $storeId)
//                     ->where('product_id', $item['id'])
//                     ->decrement('quantity', $item['qty']);
//             }

//             DB::commit();

//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Sale completed successfully!',
//                 'bill_no' => $billNo
//             ]);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'DB Error: ' . $e->getMessage() . ' on line ' . $e->getLine()
//             ], 500);
//         }
//     }
// }



//             14.05.26
// namespace App\Http\Controllers\Offline\Sale;

// use App\Http\Controllers\Controller;
// use App\Http\Controllers\Common\CommonController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// use App\Models\Stores\StoreMaster;
// use App\Models\Product\Product;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetail;
// use App\Models\StoreStock\StoreTransferDetail;
// use App\Models\Billing\BillPaymentDetail;
// use App\Models\Billing\CustomerBillingItem;

// class StoreSaleController extends CommonController
// {
//     public function index()
//     {
//         $user = Auth::user();
//         // Adjust depending on how you access user type in your actual app
//         $userType = $user->user_type ?? ($user->userType->u_type ?? 'User'); 

//         $stores = collect();
//         $productsData = collect();

//         // Super Admin or Admin
//         if (in_array($userType, ['Admin', 'Super Admin'])) {
//             $stores = StoreMaster::where('is_active', true)->get();
//         } else {
//             // Sales Manager
//             $productsData = $this->fetchStoreProductsQuery($user->store_id);
//         }

//         return view('Offline.Sale.storesale', compact('stores', 'productsData', 'user'));
//     }

//     public function getStoreProducts($storeId)
//     {
//         try {
//             $products = $this->fetchStoreProductsQuery($storeId);
            
//             return response()->json([
//                 'status' => 'success',
//                 'data' => $products
//             ]);
//         } catch (\Throwable $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     private function fetchStoreProductsQuery($storeId)
//     {
//         if (!$storeId) return collect();

//         return Product::with([
//             'priceMaster',
//             'productImage',
//             'storeStock' => function ($query) use ($storeId) {
//                 $query->where('store_id', $storeId);
//             }
//         ])
//         ->whereHas('storeStock', function ($query) use ($storeId) {
//             $query->where('store_id', $storeId)->where('quantity', '>', 0);
//         })
//         ->get()
//         ->map(function ($product) {
//             // Encode the image name so spaces don't break the URL
//             $imageUrl = null;
//             if ($product->productImage && $product->productImage->fst_image_file_name) {
//                 $imageUrl = asset('storage/products/' . rawurlencode($product->productImage->fst_image_file_name));
//             }

//             return [
//                 'id' => $product->id,
//                 'name' => $product->name, // Using your schema's 'name' column
//                 'code' => $product->product_code, 
//                 'price' => $product->priceMaster ? (float)$product->priceMaster->pro_sale_price : 0.00,
//                 'stock' => $product->storeStock->first() ? (float)$product->storeStock->first()->quantity : 0,
//                 'uom' => $product->uom ?? 'PCS',
//                 'image' => $imageUrl,
//             ];
//         });
//     }

//     public function checkout(Request $request)
//     {
//         $request->validate([
//             'store_id' => 'required|integer', // Now required from the frontend
//             'customer_name' => 'required|string|max:120',
//             'customer_phone' => 'required|string|max:15',
//             'payment_mode' => 'required|integer',
//             'cart' => 'required|array|min:1',
//         ]);

//         DB::beginTransaction();

//         try {
//             $user = Auth::user();
//             $storeId = $request->store_id; // Using the store ID from the dropdown/hidden input
//             $cart = $request->cart;

//             $totalAmount = 0;

//             // 1. Calculate Totals & Verify Stock
//             foreach ($cart as $item) {
//                 $stock = StoreStock::where('store_id', $storeId)
//                                    ->where('product_id', $item['id'])
//                                    ->lockForUpdate() 
//                                    ->first();
                
//                 if (!$stock || $stock->quantity < $item['qty']) {
//                     throw new \Exception("Insufficient stock for: " . $item['name']);
//                 }
//                 $totalAmount += ($item['price'] * $item['qty']);
//             }

//             // 2. Insert Transfer Detail (Master record)
//             $transferDetail = new StoreTransferDetail();
//             $transferDetail->user_id = $user->id;
//             $transferDetail->store_id = $storeId;
//             $transferDetail->transfer_type = 1; // 1: Customer Sale
//             $transferDetail->total_amount = $totalAmount;
//             $transferDetail->ip_address = $request->ip();
//             $transferDetail->transfer_no = 'TEMP';
//             $transferDetail->save();

//             // Generate proper Transfer No
//             $transferNo = 'TRN-POS-' . str_pad($transferDetail->id, 6, '0', STR_PAD_LEFT);
//             $transferDetail->transfer_no = $transferNo;
//             $transferDetail->save();

//             // 3. Insert Bill Payment
//             $billNo = 'CUS-' . date('y') . str_pad($transferDetail->id, 5, '0', STR_PAD_LEFT);
            
//             BillPaymentDetail::create([
//                 'std_id' => $transferDetail->id,
//                 'bill_no' => $billNo,
//                 'payment_mode' => $request->payment_mode,
//                 'phone' => $request->customer_phone,
//                 'cus_name' => $request->customer_name,
//                 'total_amount' => $totalAmount,
//                 'recived_money' => $totalAmount, 
//                 'dew_money' => 0.00,
//                 'bill_month' => Carbon::now()->month,
//                 'bill_year' => Carbon::now()->year,
//                 'payment_status' => 1, 
//             ]);

//             // 4. Process Cart Items & Deduct Stock
//             $slNo = 1;
//             foreach ($cart as $item) {
//                 // Insert Billing Item
//                 CustomerBillingItem::create([
//                     'std_id' => $transferDetail->id,
//                     'sl_no' => $slNo++,
//                     'product_name' => $item['name'],
//                     'product_id' => $item['id'],
//                     'cat_id' => 0, 
//                     'quantity' => $item['qty'],
//                     'unit_price' => $item['price'], 
//                     'sale_price' => $item['price'],
//                 ]);

//                 // Deduct Stock
//                 StoreStock::where('store_id', $storeId)
//                     ->where('product_id', $item['id'])
//                     ->decrement('quantity', $item['qty']);

//                 // Log Stock Transaction
//                 StoreStockDetail::create([
//                     'store_id' => $storeId,
//                     'product_id' => $item['id'],
//                     'transaction_type' => 2, // 2: Deduction
//                     'quantity' => $item['qty'],
//                     'reference_no' => $transferNo,
//                     'remarks' => 'In-Store POS Sale'
//                 ]);
//             }

//             DB::commit();

//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Sale completed successfully!',
//                 'bill_no' => $billNo
//             ]);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }
// }






//      13.05.26


// namespace App\Http\Controllers\Offline\Sale;

// use App\Http\Controllers\Controller;
// use App\Http\Controllers\Common\CommonController;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// // Make sure to import your actual models
// use App\Models\Stores\StoreMaster;
// use App\Models\Product\Product;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetail;
// use App\Models\StoreStock\StoreTransferDetail;
// use App\Models\Billing\BillPaymentDetail;
// use App\Models\Billing\CustomerBillingItem;

// class StoreSaleController extends CommonController
// {
    // public function index()
    // {
    //     $user = Auth::user();

    //     // Handle User Types for Store Selection
    //     if (in_array($user->user_type, ['Admin', 'Super Admin'])) {

    //         $stores = StoreMaster::where('is_active', true)->get();

    //     } else {

    //         // Assuming Sales Manager is assigned a specific store
    //         $stores = StoreMaster::where('id', $user->store_id)
    //                     ->where('is_active', true)
    //                     ->get();
    //     }

    //     return view('Offline.Sale.storesale', compact('stores', 'user'));
    // }
    // public function index()
    // {
    //     return view('Offline.Sale.storesale');
    // }

    // AJAX Endpoint for Scanner
    // public function searchProduct(Request $request)
    // {
    //     $request->validate([
    //         'keyword' => 'required|string'
    //     ]);

    //     $keyword = $request->keyword;
    //     $storeId = Auth::user()->store_id; // Assuming user is tied to a store

    //     // Eloquent ORM: Eager load relationships without raw JOINS
    //     $products = Product::with([
    //         'priceMaster',
    //         'productImage',
    //         'storeStock' => function ($query) use ($storeId) {
    //             $query->where('store_id', $storeId);
    //         }
    //     ])
    //     ->where(function ($query) use ($keyword) {
    //         // Search by Exact Barcode/Code OR partial Product Name
    //         $query->where('barcode_no', $keyword)
    //               ->orWhere('product_code', $keyword)
    //               ->orWhere('product_name', 'LIKE', "%{$keyword}%");
    //     })
    //     ->whereHas('storeStock', function ($query) use ($storeId) {
    //         // Only fetch products that actually have stock in THIS store
    //         $query->where('store_id', $storeId)->where('quantity', '>', 0);
    //     })
    //     ->get();

    //     // Format data for frontend JS
    //     $formattedProducts = $products->map(function ($product) {
    //         return [
    //             'id' => $product->id,
    //             'name' => $product->product_name,
    //             'code' => $product->product_code,
    //             'price' => $product->priceMaster ? $product->priceMaster->pro_sale_price : 0,
    //             'stock' => $product->storeStock->first() ? $product->storeStock->first()->quantity : 0,
    //             'uom' => $product->uom ?? 'PCS',
    //             'image' => $product->productImage ? asset('storage/products/' . $product->productImage->fst_image_file_name) : null,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $formattedProducts
    //     ]);
    // }

    // public function checkout(Request $request)
    // {
    //     $request->validate([
    //         'store_id' => 'required|integer',
    //         'payment_mode' => 'required|integer', // 1: Cash, 2: Online, 3: Card
    //         'customer_name' => 'nullable|string|max:120',
    //         'customer_phone' => 'nullable|string|max:15',
    //         'cart' => 'required|array|min:1',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $user = Auth::user();
    //         $storeId = $request->store_id;
    //         $cart = $request->cart;

    //         // 1. Calculate Totals & Verify Stock
    //         $totalAmount = 0;
    //         $totalDiscount = 0;

    //         foreach ($cart as $item) {
    //             $stock = StoreStock::where('store_id', $storeId)->where('product_id', $item['id'])->lockForUpdate()->first();
                
    //             if (!$stock || $stock->quantity < $item['quantity']) {
    //                 throw new \Exception("Insufficient stock for product: " . $item['product_name']);
    //             }

    //             $totalAmount += ($item['pro_sale_price'] * $item['quantity']);
    //             $totalDiscount += ($item['pro_sale_discount'] * $item['quantity']);
    //         }

    //         // 2. Insert Store Transfer Details (Initial without transfer_no)
    //         $transferDetail = new StoreTransferDetail();
    //         $transferDetail->user_id = $user->id;
    //         $transferDetail->store_id = $storeId;
    //         $transferDetail->transfer_type = 1; // 1: Customer (In-Store Sale)
    //         $transferDetail->total_amount = $totalAmount;
    //         $transferDetail->ip_address = $request->ip();
    //         $transferDetail->transfer_no = 'TEMP'; // Temporary
    //         $transferDetail->save();

    //         // Generate and Update Transfer No based on Primary Key
    //         $transferNo = 'TRN-POS-' . str_pad($transferDetail->id, 6, '0', STR_PAD_LEFT);
    //         $transferDetail->transfer_no = $transferNo;
    //         $transferDetail->save();

    //         // 3. Insert Bill Payment Details
    //         $billNo = 'CUS-' . date('y') . str_pad($transferDetail->id, 5, '0', STR_PAD_LEFT);
            
    //         $bill = new BillPaymentDetail();
    //         $bill->std_id = $transferDetail->id;
    //         $bill->bill_no = $billNo;
    //         $bill->payment_mode = $request->payment_mode;
    //         $bill->phone = $request->customer_phone;
    //         $bill->cus_name = $request->customer_name;
    //         $bill->total_amount = $totalAmount;
    //         $bill->recived_money = $totalAmount; // Assuming full payment for now
    //         $bill->dew_money = 0.00;
    //         $bill->bill_month = Carbon::now()->month;
    //         $bill->bill_year = Carbon::now()->year;
    //         $bill->payment_status = 1; // 1: Done
    //         $bill->save();

    //         // 4. Process Cart Items: Deduct Stock & Insert Billing Items
    //         $slNo = 1;
    //         foreach ($cart as $item) {
    //             // Insert Billing Item
    //             CustomerBillingItem::create([
    //                 'std_id' => $transferDetail->id,
    //                 'sl_no' => $slNo++,
    //                 'product_name' => $item['product_name'],
    //                 'product_id' => $item['id'],
    //                 'cat_id' => $item['cat_id'],
    //                 'product_code' => $item['product_code'],
    //                 'uom' => $item['uom'],
    //                 'quantity' => $item['quantity'],
    //                 'mrp_price' => $item['pro_mrp_price'],
    //                 'unit_price' => $item['pro_sale_price'] + $item['pro_sale_discount'], 
    //                 'sale_price' => $item['pro_sale_price'],
    //                 'discount_price' => $item['pro_sale_discount'],
    //                 'discount_percentage' => $item['pro_sale_discount_percentage'],
    //             ]);

    //             // Deduct Stock
    //             StoreStock::where('store_id', $storeId)
    //                 ->where('product_id', $item['id'])
    //                 ->decrement('quantity', $item['quantity']);

    //             // Insert Stock Transaction Detail (transaction_type = 2 for Out/Deduction)
    //             StoreStockDetail::create([
    //                 'store_id' => $storeId,
    //                 'product_id' => $item['id'],
    //                 'transaction_type' => 2, 
    //                 'quantity' => $item['quantity'],
    //                 'reference_no' => $transferNo,
    //                 'remarks' => 'In-Store POS Sale'
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success', 
    //             'message' => 'Sale completed successfully!',
    //             'transfer_no' => $transferNo,
    //             'bill_no' => $billNo
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => 'error', 
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
