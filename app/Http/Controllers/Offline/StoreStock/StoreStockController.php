<?php

namespace App\Http\Controllers\Offline\StoreStock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchased\PurchasedStock;
use App\Models\Purchased\PurchaseDetails;
use App\Models\Purchased\PurchaseTransactionDetails;
use App\Models\StoreStock\StoreStock;
use App\Models\StoreStock\StoreStockDetails;
use App\Models\Stores\StoreMaster;
use App\Models\Unit\Unit;
use Carbon\Carbon;
use App\Http\Controllers\Common\CommonController;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreStockController extends CommonController
{
    // ---Fetch Godown Stock Product PAGE ---
    public function index()
    {
        $stores = StoreMaster::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        $units = Unit::where('is_active', true)
            ->where('is_deleted', false)
            ->get();
        
        $godownStocks = PurchasedStock::with(['product.colourRelation', 'uomRelation'])
            ->where('quantity', '>', 0)
            ->get();

        foreach ($godownStocks as $stock) {
            $txs = PurchaseTransactionDetails::where('product_id', $stock->product_id)
                ->whereNotNull('batch_no')
                ->where('quantity', '>', 0) 
                ->get();
            
            $batches = [];
            foreach($txs as $tx) {
                if(is_array($tx->batch_no)) {
                    $batches = array_merge($batches, $tx->batch_no);
                }
            }
            $stock->all_batches = count($batches) > 0 ? implode(', ', array_unique($batches)) : 'N/A';
        }

        $godownStocks->map(function ($stock) {
            $stock->uom_keyword = $stock->uomRelation->keyword ?? '';
            $stock->colour_name = $stock->product->colourRelation->colour_name ?? '';
            $stock->pro_size = $stock->product->pro_size ?? '';
            return $stock;
        });

        return view('Offline.StoreStock.store-stock', compact('stores', 'godownStocks', 'units'));
    }

    // --- Transfer Stock to Store PAGE ---
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|integer|gt:0',
            'products.*.uom' => 'nullable|integer',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.mrp' => 'required|numeric|min:0',
            'products.*.cgst' => 'nullable|numeric|min:0',
            'products.*.sgst' => 'nullable|numeric|min:0',
            'products.*.no_of_pack' => 'nullable|integer|min:0',
            'products.*.each_pack_quantity' => 'nullable|numeric|min:0',
        ], [
            'products.*.product_id.required' => 'Product is required.',
            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.quantity.integer' => 'Quantity must be a whole number, no decimals.',
            'products.*.quantity.gt' => 'Quantity must be greater than 0.',
            'products.*.unit_price.required' => 'Unit Price is required.',
            'products.*.unit_price.min' => 'Unit Price cannot be negative.',
            'products.*.mrp.required' => 'MRP is required.',
            'products.*.mrp.min' => 'MRP cannot be negative.',
            'products.*.cgst.min' => 'CGST cannot be negative.',
            'products.*.sgst.min' => 'SGST cannot be negative.',
            'products.*.no_of_pack.min' => 'No of packs cannot be negative.',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        // PHASE 1: Validate ALL product quantities BEFORE any DB writes
        $validatedProducts = [];
        foreach ($request->products as $index => $prod) {
            $qtyToTransfer = floatval($prod['quantity']);

            $godownStock = PurchasedStock::where('product_id', $prod['product_id'])->first();

            if (!$godownStock || $godownStock->quantity < $qtyToTransfer) {
                $available = $godownStock ? $godownStock->quantity : 0;
                return response()->json([
                    'status' => 'error',
                    'errors' => ["products.{$index}.quantity" => ["Max available godown stock is {$available}."]]
                ], 422);
            }

            $validatedProducts[] = [
                'product_id'     => $prod['product_id'],
                'quantity'       => $qtyToTransfer,
                'uom'            => $prod['uom'] ?? null,
                'mrp'            => $prod['mrp'],
                'unit_price'     => $prod['unit_price'],
                'cgst'           => $prod['cgst'] ?? 0,
                'sgst'           => $prod['sgst'] ?? 0,
                'is_packet'      => $prod['is_packet'] ?? false,
                'no_of_pack'     => $prod['no_of_pack'] ?? 0,
                'each_pack_quantity' => $prod['each_pack_quantity'] ?? null,
                'batch_no'       => $prod['batch_no'] ?? '',
            ];
        }

        // PHASE 2: All validations passed — now write to DB
        $barcodesToPrint = [];

        DB::beginTransaction();
        try {
            $storeId = $request->store_id;
            $datePrefix = date('my');

            $todayTransfers = PurchaseDetails::whereDate('created_at', Carbon::today())
                                ->where('transaction_type', 2)
                                ->count();

            $serial = str_pad($todayTransfers + 1, 3, '0', STR_PAD_LEFT);
            $transferChallanNo = "NSH-{$storeId}-{$datePrefix}-{$serial}";

            $transferChallan = PurchaseDetails::create([
                'user_id' => auth()->id() ?? 1,
                'challan_no' => $transferChallanNo,
                'challan_date' => Carbon::today(),
                'transaction_type' => 2,
                'ip_address' => $request->ip(),
                'total' => 0
            ]);

            $grandTotalTransfer = 0;

            foreach ($validatedProducts as $prod) {
                $qtyToTransfer = $prod['quantity'];

                $godownStock = PurchasedStock::with('product')
                    ->where('product_id', $prod['product_id'])
                    ->lockForUpdate()
                    ->first();

                $godownStock->quantity -= $qtyToTransfer;
                $godownStock->save();

                $gstPct = floatval($prod['cgst']) + floatval($prod['sgst']);
                $totalPrice = ($qtyToTransfer * $prod['unit_price']) * (1 + ($gstPct / 100));
                $grandTotalTransfer += $totalPrice;

                PurchaseTransactionDetails::create([
                    'purchase_details_id' => $transferChallan->id,
                    'store_id' => $request->store_id,
                    'product_id' => $prod['product_id'],
                    'quantity' => $qtyToTransfer,
                    'uom' => $prod['uom'],
                    'mrp' => $prod['mrp'],
                    'unit_price' => $prod['unit_price'],
                    'total_price' => $totalPrice,
                    'gst' => $gstPct,
                    'cgst' => $prod['cgst'],
                    'sgst' => $prod['sgst'],
                    'is_packet' => $prod['is_packet'],
                    'transaction_type' => 2,
                ]);

                $storeStock = StoreStock::firstOrNew(['store_id' => $request->store_id, 'product_id' => $prod['product_id']]);
                $storeStock->quantity = ($storeStock->quantity ?? 0) + $qtyToTransfer;
                $storeStock->uom = $prod['uom'];
                $storeStock->save();

                $barcode = 'BNMN' . time() . mt_rand(100, 999) . $prod['product_id'];

                StoreStockDetails::create([
                    'purchase_details_id' => $transferChallan->id,
                    'user_id' => auth()->id() ?? 1,
                    'store_id' => $request->store_id,
                    'received_from' => 1,
                    'product_id' => $prod['product_id'],
                    'quantity' => $qtyToTransfer,
                    'uom' => $prod['uom'],
                    'mrp' => $prod['mrp'],
                    'unit_price' => $prod['unit_price'],
                    'total_price' => $totalPrice,
                    'batch_no' => explode(', ', $prod['batch_no']),
                    'barcode_no' => $barcode,
                    'gst' => $gstPct,
                    'cgst' => $prod['cgst'],
                    'sgst' => $prod['sgst'],
                    'no_of_pack' => $prod['no_of_pack'],
                    'each_pack_quantity' => $prod['each_pack_quantity'],
                    'is_packet' => $prod['is_packet'],
                    'transaction_type' => 1,
                ]);

                $barcodesToPrint[] = [
                    'name' => $godownStock->product->name,
                    'mrp' => $prod['mrp'],
                    'barcode' => $barcode,
                    'quantity' => intval($qtyToTransfer)
                ];
            }

            $transferChallan->update(['total' => $grandTotalTransfer]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Transfer complete! Generated ' . count($validatedProducts) . ' unique barcodes.',
                'barcodes' => $barcodesToPrint
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Store transfer failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Transfer failed due to a server error. No data was saved. Please try again.'], 500);
        }
    }

    // --- Store Total Stock PAGE ---
    public function totalStock(Request $request, $store_id=null)
    {
        $stores = StoreMaster::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        if ($store_id) {
            $storeId = $this->decryptData($store_id);

            if (!$storeId) {
                abort(404, 'Invalid Store ID');
            }
        } else {
            $storeId = $stores->first()->id ?? null;
        }

        $stores->map(function ($store) {
            $store->enc_id = $this->encryptData($store->id);
            return $store;
        });

        $storeStocks = StoreStock::with(['product.colourRelation', 'uomRelation'])
            ->where('store_id', $storeId)
            ->orderBy('quantity', 'desc')
            ->get();
            
        $storeStocks->map(function ($stock) {
            $stock->enc_store_id = $this->encryptData((string) $stock->store_id);
            $stock->enc_product_id = $this->encryptData((string) $stock->product_id);
            return $stock;
        });

        // Fetch images efficiently
        $productIds = $storeStocks->pluck('product_id')->toArray();
        $productImages = DB::table('product_images')
            ->whereIn('product_id', $productIds)
            ->pluck('fst_image_doc', 'product_id');

        $totalUnique = $storeStocks->count();
        $totalItems = $storeStocks->sum('quantity');

        return view('Offline.StoreStock.total_stock_store', compact('stores', 'storeStocks', 'storeId', 'totalUnique', 'totalItems', 'productImages'));
    }

    // --- Store Product HISTORY PAGE ---
    public function productHistory($enc_store_id, $enc_product_id)
    {
        $store_id = $this->decryptData($enc_store_id);
        $product_id = $this->decryptData($enc_product_id);

        if (!$store_id || !$product_id) {
            abort(404, 'Invalid Link Data');
        }

        $store = StoreMaster::find($store_id);
        
        $details = StoreStockDetails::with(['product.colourRelation', 'uomRelation'])
            ->leftJoin('purchase_details', 'store_stock_details.purchase_details_id', '=', 'purchase_details.id')
            ->where('store_stock_details.store_id', $store_id)
            ->where('store_stock_details.product_id', $product_id)
            ->select('store_stock_details.*', 'purchase_details.challan_no as bill_no')
            ->orderBy('store_stock_details.created_at', 'asc')
            ->get();

        $imageRecord = DB::table('product_images')
            ->where('product_id', $product_id)
            ->first();

        $imageUrl = ($imageRecord && $imageRecord->fst_image_doc) 
            ? asset('storage/' . $imageRecord->fst_image_doc) 
            : null;

        $totalIn = 0;
        $totalOut = 0;
        $runningStock = 0;

        // 3. Calculate running stock safely
        foreach($details as $detail) {
            $qty = (float) $detail->quantity;
            
            if ($detail->transaction_type == 1) { // IN
                $totalIn += $qty;
                $runningStock += $qty;
                $detail->in_qty = $qty;
                $detail->out_qty = 0;

            } elseif ($detail->transaction_type == 2 || $detail->transaction_type == 3) { // OUT
                $totalOut += $qty;
                $runningStock -= $qty;
                $detail->in_qty = 0;
                $detail->out_qty = $qty;
            } else {
                // Optional: handle unknown types
                $detail->in_qty = 0;
                $detail->out_qty = 0;
            }
            $detail->running_stock = $runningStock;
        }

        $available = $totalIn - $totalOut;

        $details = $details->sortByDesc('created_at')->values();

        return view('Offline.StoreStock.total_stock_store_history', compact('details', 'store', 'totalIn', 'totalOut', 'available', 'imageUrl'));
    }

    // --- Print Barcode Which Have in Store PAGE ---
    public function printBarcodes() { 
        return view('Offline.StoreStock.print-barcodes'); 
    }

    // --- STORE All Product In & Out TRANSACTION Page --
    public function StoreAllTransaction(Request $request)
    {
        $stores = StoreMaster::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        $storeId = $request->input('store_id', $stores->first()->id ?? null);

        $query = PurchaseDetails::with(['transactions.product', 'transactions.uomRelation'])
            ->where('transaction_type', 2) // 2 = OUTWARD Transfer
            ->orderBy('challan_date', 'desc')
            ->orderBy('id', 'desc');

        if ($storeId) {
            $query->whereHas('transactions', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('challan_date', [$request->start_date, $request->end_date]);
        } else {
            $query->whereDate('challan_date', Carbon::today());
        }

        $challans = $query->get();

        return view('Offline.StoreStock.store_transaction_ledger', compact('stores', 'storeId', 'challans'));
    }

    // --- Store Purchase Product History PAGE ---
    public function StorePurchaseHistory(Request $request, $enc_store_id = null)
    {
        $stores = StoreMaster::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        // 1. Decrypt Store ID
        $storeId = null;
        if ($enc_store_id) {
            $storeId = (int) $this->decryptData($enc_store_id);
            if (!$storeId) {
                abort(404, 'Invalid Store ID');
            }
        }

        // 2. Encrypt Store IDs for dropdown
        $stores->map(function ($store) {
            $store->enc_id = $this->encryptData((string) $store->id);
            return $store;
        });

        // 3. Get Dates
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date = $request->input('end_date', date('Y-m-d'));

        // 4. Build Query
        $query = PurchaseDetails::with([
            'storeStockDetails.product.colourRelation', 
            'storeStockDetails.uomRelation', 
            'storeStockDetails.store', 
            'user.details'
        ])
        ->where('transaction_type', 2) 
        ->whereDate('challan_date', '>=', $start_date)
        ->whereDate('challan_date', '<=', $end_date)
        ->orderBy('challan_date', 'desc')
        ->orderBy('id', 'desc');

        // Apply Store Filter
        if ($storeId) {
            $query->whereHas('storeStockDetails', function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            });
        }

        $challans = $query->get();

        // 5. Pre-Encrypt Challan IDs and Calculate Quantities so the Blade file doesn't crash!
        $challans->map(function ($c) {
            // Securely encrypt the ID for the Print button
            $c->enc_id = $this->encryptData((string) $c->id);
            // Calculate total quantity to decide if Barcode button should show
            $c->total_qty = $c->storeStockDetails->sum('quantity');
            // Attach current store stock quantity for each product so barcodes print only what's in store
            foreach ($c->storeStockDetails as $detail) {
                $storeStock = \App\Models\StoreStock\StoreStock::where('store_id', $detail->store_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                $detail->current_stock_qty = $storeStock ? (int) $storeStock->quantity : 0;
            }
            return $c;
        });

        return view('Offline.StoreStock.store-purchased-history', compact('stores', 'storeId', 'enc_store_id', 'challans', 'start_date', 'end_date'));
    }

    // --- Word Converter PAGE ---
    private function amountToWords($number) 
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees . ' Rupees ' : '') . $paise;
    }

    // --- Print Challan Which Have in Store PAGE ---
    public function printChallan($encrypted_id)
    {
        // Use CommonController decrypt method instead of Crypt::decrypt!
        $id = (int) $this->decryptData($encrypted_id);
        
        if (!$id) {
            abort(404, 'Invalid or expired print link.');
        }

        $challan = PurchaseDetails::with([
            'storeStockDetails.product.colourRelation', 
            'storeStockDetails.uomRelation', 
            'storeStockDetails.store', 
            'user.details'
        ])->findOrFail($id);

        $summary = [
            'totalQty' => 0, 'subTotal' => 0, 'totalCGST' => 0, 'totalSGST' => 0, 'netAmount' => 0,
        ];

        foreach($challan->storeStockDetails as $item) {
            $summary['totalQty'] += $item->quantity;
            $basePrice = $item->quantity * $item->unit_price;
            $summary['subTotal'] += $basePrice;
            $summary['totalCGST'] += $basePrice * ($item->cgst / 100);
            $summary['totalSGST'] += $basePrice * ($item->sgst / 100);
            $summary['netAmount'] += $item->total_price;
        }

        $summary['grandTotal'] = round($summary['netAmount']);
        $summary['roundOff'] = $summary['grandTotal'] - $summary['netAmount'];
        $amountInWords = $this->amountToWords($summary['grandTotal']);

        return view('Offline.StoreStock.print_challan', compact('challan', 'summary', 'amountInWords'));
    }

}





















// namespace App\Http\Controllers\Offline\StoreStock;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Validator;
// use App\Models\Purchased\PurchasedStock;
// use App\Models\Purchased\PurchaseTransactionDetails;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetails;
// use App\Models\Stores\StoreMaster;
// use App\Models\Unit\Unit;
// use Carbon\Carbon;

// class StoreStockController extends Controller
// {
//     public function index()
//     {
//         $stores = StoreMaster::where('is_active', true)->where('is_deleted', false)->get();
//         $units = Unit::where('is_active', true)->where('is_deleted', false)->get();
        
//         $godownStocks = PurchasedStock::with(['product', 'uomRelation'])
//             ->where('quantity', '>', 0)
//             ->get();

//         foreach ($godownStocks as $stock) {
//             // Fetch all INWARD transactions for this product to get ALL batches
//             $txs = PurchaseTransactionDetails::where('product_id', $stock->product_id)
//                 ->where('transaction_type', 1)->whereNotNull('batch_no')->get();
            
//             $batches = [];
//             foreach($txs as $tx) {
//                 if(is_array($tx->batch_no)) {
//                     $batches = array_merge($batches, $tx->batch_no);
//                 }
//             }
//             $stock->all_batches = count($batches) > 0 ? implode(', ', array_unique($batches)) : 'N/A';
//         }

//         return view('Offline.StoreStock.store-stock', compact('stores', 'godownStocks', 'units'));
//     }

//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'store_id' => 'required|integer',
//             'products' => 'required|array|min:1',
//             'products.*.product_id' => 'required|integer',
//             'products.*.quantity' => 'required|numeric|min:0.1',
//             'products.*.uom' => 'required|integer',
//             'products.*.unit_price' => 'required|numeric|min:0',
//             'products.*.mrp' => 'required|numeric|min:0',
//         ], [
//             'store_id.required' => 'Please select a destination store.',
//             'products.*.quantity.required' => 'Quantity is required.',
//             'products.*.unit_price.required' => 'Unit Price is required.',
//             'products.*.mrp.required' => 'Store MRP is required.',
//             'products.*.uom.required' => 'UOM is required.'
//         ]);

//         if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

//         $barcodesToPrint = [];

//         DB::beginTransaction();
//         try {

//             // this will close for table column not have
//             // Generate Unique store_stock_id (e.g., NSH12042601)
//             // $todayCount = StoreStockDetails::whereDate('created_at', Carbon::today())->count();
//             // $storeStockId = 'NSH' . date('dmy') . str_pad($todayCount + 1, 2, '0', STR_PAD_LEFT);
//             //end
            
//             foreach ($request->products as $index => $prod) {
//                 $qtyToTransfer = floatval($prod['quantity']);
                
//                 // 1. Verify Godown Stock
//                 $godownStock = PurchasedStock::with('product')->where('product_id', $prod['product_id'])->lockForUpdate()->first();
                
//                 if (!$godownStock || $godownStock->quantity < $qtyToTransfer) {
//                     return response()->json([
//                         'status' => 'error', 
//                         // Exact mapping to the JS array index for flawless UI errors
//                         'errors' => ["products.{$index}.quantity" => ["Max available godown stock is {$godownStock->quantity}."]]
//                     ], 422);
//                 }

//                 // 2. Deduct Godown Stock
//                 $godownStock->quantity -= $qtyToTransfer;
//                 $godownStock->save();

//                 // 3. Purchase Ledger (OUT)
//                 $gstPct = floatval($prod['cgst'] ?? 0) + floatval($prod['sgst'] ?? 0);
//                 $totalPrice = ($qtyToTransfer * $prod['unit_price']) * (1 + ($gstPct / 100));

//                 PurchaseTransactionDetails::create([
//                     'purchase_details_id' => 0, // User requested not to store this ID
//                     'store_id' => $request->store_id,
//                     'product_id' => $prod['product_id'],
//                     'quantity' => $qtyToTransfer,
//                     'uom' => $prod['uom'],
//                     'mrp' => $prod['mrp'],
//                     'unit_price' => $prod['unit_price'],
//                     'total_price' => $totalPrice,
//                     'batch_no' => explode(', ', $prod['batch_no']), // Store as array
//                     'gst' => $gstPct,
//                     'cgst' => $prod['cgst'] ?? 0,
//                     'sgst' => $prod['sgst'] ?? 0,
//                     'transaction_type' => 2 // OUTWARD
//                 ]);

//                 // 4. Increase Store Aggregate Stock
//                 $storeStock = StoreStock::firstOrNew(['store_id' => $request->store_id, 'product_id' => $prod['product_id']]);
//                 $storeStock->quantity = ($storeStock->quantity ?? 0) + $qtyToTransfer;
//                 $storeStock->uom = $prod['uom'];
//                 $storeStock->save();

//                 // 5. Store Details (IN) -> 1 ROW PER PRODUCT!
//                 $barcode = 'BNMN' . time() . mt_rand(100, 999) . $prod['product_id'];

//                 StoreStockDetails::create([
//                     // this will close for table column not have
//                     // 'store_stock_id' => $storeStockId,
//                     // 'transaction_type' => 1, // 1 = IN
//                     //end
//                     'user_id' => auth()->id() ?? 1,
//                     'store_id' => $request->store_id,
//                     'received_from' => 1,
//                     'product_id' => $prod['product_id'],
//                     'quantity' => $qtyToTransfer, 
//                     'uom' => $prod['uom'],
//                     'mrp' => $prod['mrp'],
//                     'unit_price' => $prod['unit_price'],
//                     'total_price' => $totalPrice,
//                     'batch_no' => explode(', ', $prod['batch_no']),
//                     'barcode_no' => $barcode,
//                     'gst' => $gstPct,
//                     'cgst' => $prod['cgst'] ?? 0,
//                     'sgst' => $prod['sgst'] ?? 0,
//                     'no_of_pack' => $prod['no_of_pack'] ?? 0,
//                     'each_pack_quantity' => $prod['each_pack_quantity'] ?? null,
//                     'is_packet' => $prod['is_packet'] ?? false,
//                 ]);

//                 // Pass to Print View (Include quantity so print view knows how many stickers to loop)
//                 $barcodesToPrint[] = [
//                     'name' => $godownStock->product->name,
//                     'mrp' => $prod['mrp'],
//                     'barcode' => $barcode,
//                     'quantity' => intval($qtyToTransfer) // Important for print loop!
//                 ];
//             }

//             DB::commit();
//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Transfer complete! Generated ' . count($request->products) . ' unique barcodes.',
//                 'barcodes' => $barcodesToPrint
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['status' => 'error', 'message' => 'Transfer failed: ' . $e->getMessage()], 500);
//         }
//     }

//     public function printBarcodes() { return view('Offline.StoreStock.print-barcodes'); }
// }