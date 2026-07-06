<?php

namespace App\Http\Controllers\Offline\Combo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product\Product;
use App\Models\Unit\Unit;
use App\Models\StoreStock\StoreStock;
use App\Models\StoreStock\StoreStockDetails;
use App\Models\Combo\ComboProduct;
use App\Models\Combo\ComboProductItem;
use App\Models\Stores\StoreMaster;
use App\Models\PriceMaster\PriceMaster;
use App\Http\Controllers\Common\CommonController;


class ComboController extends CommonController
{
    public function index()
    {
        $user = Auth::user();
        // Role check: 1=Super Admin, 2=Admin
        if (in_array($user->user_type_id, [1, 2])) {
            $stores = StoreMaster::where('is_active', true)->get();
        } else {
            $stores = StoreMaster::where('id', $user->store_id)->get();
        }

        $all_products = Product::with(['colourRelation'])
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        $units = Unit::where('is_active', true)
            ->get();

        return view('Offline.Combo.combo', compact('stores', 'all_products', 'units'));
    }

    public function getStoreProducts($storeId)
    {
        try {
            $products = StoreStock::with(['product.colourRelation', 'uomRelation'])
                ->where('store_id', $storeId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->where('quantity', '>', 0)
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'         => 'required|exists:store_masters,id',
            'combo_product_id' => 'required|exists:product_masters,id',
            'bundle_qty'       => 'required|numeric|min:1',
            'items'            => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        return DB::transaction(function () use ($request) {
            try {
                $user_id = Auth::id();
                $store_id = $request->store_id;
                $target_id = $request->combo_product_id;
                $combo_ref = 'CMB-' . date('YmdHis');
                
                // 1. Create Combo Header
                $combo = new ComboProduct();
                $combo->user_id    = $user_id;
                $combo->store_id   = $store_id;
                $combo->combo_code = $combo_ref;
                $combo->product_id = $target_id;
                $combo->is_active  = true;
                $combo->ip_address= $request->ip(); //newly added
                $combo->save();

                // 2. Process Ingredients (OUTWARD - Type 3)
                foreach ($request->items as $item) {
                    $prod_id = $item['product_id'];
                    $qtyNeeded = floatval($item['use_qty']);

                    $availableBatches = StoreStockDetails::where('store_id', $store_id)
                        ->where('product_id', $prod_id)
                        ->where('transaction_type', 1) 
                        ->where('quantity', '>', 0)
                        ->orderBy('id', 'asc')
                        ->get();

                    $remaining = $qtyNeeded;
                    $usedBatches = [];
                    $usedBarcodes = [];

                    foreach ($availableBatches as $batch) {
                        if ($remaining <= 0) break;
                        $take = min($batch->quantity, $remaining);
                        
                        // Collect batch and barcode info
                        if ($batch->batch_no) {
                            // Handle if existing batch_no is already an array or a string
                            if (is_array($batch->batch_no)) {
                                $usedBatches = array_merge($usedBatches, $batch->batch_no);
                            } else {
                                $usedBatches[] = $batch->batch_no;
                            }
                        }
                        if ($batch->barcode_no) {
                            if (is_array($batch->barcode_no)) {
                                $usedBarcodes = array_merge($usedBarcodes, $batch->barcode_no);
                            } else {
                                $usedBarcodes[] = $batch->barcode_no;
                            }
                        }

                        $remaining -= $take;
                    }

                    if ($remaining > 0) {
                        throw new \Exception("Insufficient stock in batches for product ID: $prod_id");
                    }

                    $priceInfo = PriceMaster::where('product_id', $prod_id)->first();

                    // SAVE ONE ROW PER INGREDIENT
                    $detailOut = new StoreStockDetails();
                    $detailOut->transaction_type = 3; 
                    $detailOut->combo_id         = $combo->id;
                    $detailOut->user_id          = $user_id;
                    $detailOut->store_id         = $store_id;
                    $detailOut->product_id       = $prod_id;
                    $detailOut->quantity         = $qtyNeeded;
                    $detailOut->uom              = $item['uom_id'];
                    $detailOut->mrp              = $priceInfo->pro_mrp_price ?? 0;
                    $detailOut->unit_price       = $priceInfo->pro_sale_price ?? 0;
                    $detailOut->gst              = $priceInfo->gst_rate ?? 0;
                    $detailOut->cgst             = $priceInfo->cgst_rate ?? 0;
                    $detailOut->sgst             = $priceInfo->sgst_rate ?? 0;
                    
                    // IMPORTANT: NO json_encode here. Pass the raw array.
                    $detailOut->batch_no         = array_values(array_unique($usedBatches));
                    $detailOut->barcode_no       = array_values(array_unique($usedBarcodes));
                    $detailOut->save();

                    // Deduct from main stock
                    StoreStock::where('store_id', $store_id)->where('product_id', $prod_id)->decrement('quantity', $qtyNeeded);

                    // Save Recipe Item
                    $comboItem = new ComboProductItem();
                    $comboItem->combo_id   = $combo->id;
                    $comboItem->product_id = $prod_id;
                    $comboItem->quantity   = $qtyNeeded;
                    $comboItem->uom        = $item['uom_id'];
                    $comboItem->save();
                }

                // 3. Process Target Bundle (INWARD - Type 1)
                $bundleQty = $request->bundle_qty;
                $bundleBarcode = 'BNMN' . time() . mt_rand(100, 999) . $target_id;

                $targetStock = StoreStock::updateOrCreate(
                    ['store_id' => $store_id, 'product_id' => $target_id],
                    ['is_active' => true, 'is_deleted' => false, 'uom' => $request->bundle_uom]
                );
                $targetStock->increment('quantity', $bundleQty);

                // SAVE ONE ROW FOR THE FINISHED BUNDLE
                $detailIn = new StoreStockDetails();
                $detailIn->transaction_type = 1; 
                $detailIn->combo_id         = $combo->id;
                $detailIn->user_id          = $user_id;
                $detailIn->store_id         = $store_id;
                $detailIn->product_id       = $target_id;
                $detailIn->quantity         = $bundleQty;
                $detailIn->uom              = $request->bundle_uom;
                $detailIn->mrp              = $request->combo_price;
                $detailIn->unit_price       = $request->unit_price;
                $detailIn->total_price      = $request->combo_price;
                $detailIn->gst              = floatval($request->gst_rate ?? 0);
                $detailIn->cgst             = $detailIn->gst / 2;
                $detailIn->sgst             = $detailIn->gst / 2;
                
                // IMPORTANT: Wrap the string in an array because of the Model Cast
                $detailIn->barcode_no       = [$bundleBarcode]; 
                $detailIn->batch_no         = null; 
                $detailIn->save();

                $targetProduct = Product::find($target_id);

                return response()->json([
                    'status' => 'success', 
                    'message' => 'Combo bundle created!',
                    'print_data' => [[
                        'name'     => $targetProduct->name,
                        'mrp'      => number_format($request->combo_price, 2, '.', ''),
                        'barcode'  => $bundleBarcode, // Send as string for printing logic
                        'quantity' => $bundleQty
                    ]],
                    'redirect_url' => route('store_stock.print_barcodes')
                ]);

            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        });
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        
        // Eager load everything including Bengali names and User Details
        $query = ComboProduct::with(['product.colourRelation', 'store', 'user.details']);

        // Store Filtering
        if ($request->filled('store_filter')) {
            $query->where('store_id', $request->store_filter);
        }

        // 2. Date Range Filtering
        $fromDate = $request->get('from_date', date('Y-m-d'));
        $toDate = $request->get('to_date', date('Y-m-d'));
        $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);

        $combos = $query->orderBy('created_at', 'desc')->get();

        // 3. Encrypt Combo IDs for the "View" button
        $combos->transform(function ($item) {
            $item->encrypted_id = $this->encryptData($item->id);
            return $item;
        });

        // 4. Prepare Stores for Dropdown
        $stores = StoreMaster::where('is_active', true)->get();

        return view('Offline\Combo\list', compact('combos', 'stores', 'fromDate', 'toDate'));
    }

    public function details($encrypted_id)
    {
        try {
            $id = $this->decryptData($encrypted_id);
            if (!$id) return response()->json([
                    'status' => 'error', 
                    'message' => 'Invalid ID'
                ], 400);

            // Fetch Ingredients (Type 3) and Bundle (Type 1)
            $raw = StoreStockDetails::with(['product.colourRelation', 'uomRelation'])
                ->where('combo_id', $id)
                ->where('transaction_type', 3)
                ->get();

            $finished = StoreStockDetails::with(['product.colourRelation', 'uomRelation'])
                ->where('combo_id', $id)
                ->where('transaction_type', 1)
                ->get();
                
            return response()->json([
                'status' => 'success',
                'raw' => $raw,
                'finished' => $finished
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPrintData($encrypted_id)
    {
        try {
            $id = $this->decryptData($encrypted_id);
            if (!$id) return response()->json(['status' => 'error', 'message' => 'Invalid ID'], 400);

            // 1. Get the Combo info
            $combo = ComboProduct::findOrFail($id);

            // 2. Use logged-in user's store to check current stock for barcode printing
            $user = Auth::user();
            $storeId = $user->store_id ?? $combo->store_id;

            $currentStock = \App\Models\StoreStock\StoreStock::where('store_id', $storeId)
                ->where('product_id', $combo->product_id)
                ->first();

            $liveQty = $currentStock ? (int)$currentStock->quantity : 0;

            if ($liveQty <= 0) {
                return response()->json(['status' => 'error', 'message' => 'No stock available in store to print barcodes.'], 400);
            }

            // 3. Get Barcode and MRP from the original Inward record of this combo
            $stockDetail = StoreStockDetails::with('product')
                ->where('combo_id', $id)
                ->where('transaction_type', 1) // Inward
                ->first();

            if (!$stockDetail) return response()->json(['status' => 'error', 'message' => 'Original record not found.'], 404);

            // Handle Barcode array/string
            $barcode = is_array($stockDetail->barcode_no) ? $stockDetail->barcode_no[0] : $stockDetail->barcode_no;

            return response()->json([
                'status' => 'success',
                'print_payload' => [
                    [
                        'name'     => $stockDetail->product->name,
                        'mrp'      => number_format($stockDetail->mrp, 2, '.', ''),
                        'barcode'  => $barcode,
                        'quantity' => $liveQty // Only print what is actually in the store right now
                    ]
                ],
                'redirect_url' => route('store_stock.print_barcodes')
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}











// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;
// use App\Models\Product\Product;
// use App\Models\Unit\Unit;
// use App\Models\StoreStock\StoreStock;
// use App\Models\StoreStock\StoreStockDetails;
// use App\Models\Combo\ComboProduct;
// use App\Models\Combo\ComboProductItem;
// use App\Models\Stores\StoreMaster;

// class ComboController extends Controller
// {
//     public function index()
//     {
//         $user = Auth::user();
        
//         // Ensure we get stores correctly for the dropdown
//         if (in_array($user->user_type_id, [1, 2])) {
//             $stores = DB::table('store_masters')->where('is_active', 1)->get();
//         } else {
//             $stores = DB::table('store_masters')->where('id', $user->store_id)->get();
//         }

//         $all_products = Product::where('is_active', true)->where('is_deleted', false)->get();
//         $units = DB::table('unit_convert_masters')->where('is_active', 1)->get();

//         return view('Offline.Combo.combo', compact('stores', 'all_products', 'units'));
//     }

//     // AJAX: Fetch products available in a specific store
//     public function getStoreProducts($storeId)
//     {
//         try {
//             $products = StoreStock::with(['product', 'uomRelation'])
//                 ->where('store_id', $storeId)
//                 ->where('is_active', 1)
//                 ->where('is_deleted', 0)
//                 ->where('quantity', '>', 0)
//                 ->get();

//             return response()->json($products);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'store_id'         => 'required',
//             'combo_product_id' => 'required',
//             'bundle_qty'       => 'required|numeric|min:1',
//             'items'            => 'required|array',
//             'combo_price'      => 'required|numeric',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['status' => 'error', 'message' => 'Please fill all required fields.'], 422);
//         }

//         DB::beginTransaction();
//         try {
//             $user_id = Auth::id();
//             $store_id = $request->store_id;
            
//             // Generate Barcode & Ref only on success
//             $barcode = 'BNMN' . time() . mt_rand(100, 999) . $prod['product_id'];
//             $combo_ref = 'CMB-' . date('Ymd') . '-' . rand(100, 999);

//             // 1. DEDUCT INGREDIENTS (Transaction Type 3: OUT)
//             foreach ($request->items as $item) {
//                 $qtyNeeded = $item['use_qty'] * $request->bundle_qty;

//                 $stock = StoreStock::where('store_id', $store_id)
//                     ->where('product_id', $item['product_id'])
//                     ->first();

//                 if (!$stock || $stock->quantity < $qtyNeeded) {
//                     throw new \Exception("Insufficient stock for one of the ingredients.");
//                 }

//                 $stock->decrement('quantity', $qtyNeeded);

//                 // Transaction OUT (Type 3)
//                 StoreStockDetails::create([
//                     'transaction_type' => 3, 
//                     'user_id'          => $user_id,
//                     'store_id'         => $store_id,
//                     'product_id'       => $item['product_id'],
//                     'quantity'         => $qtyNeeded,
//                     'uom'              => $item['uom_id'],
//                     'barcode_no'       => $barcode,
//                 ]);
//             }

//             // 2. ADD COMBO PRODUCT (Transaction Type 1: IN)
//             $comboStock = StoreStock::updateOrCreate(
//                 ['store_id' => $store_id, 'product_id' => $request->combo_product_id],
//                 ['is_active' => true, 'is_deleted' => false]
//             );
//             $comboStock->increment('quantity', $request->bundle_qty);

//             // Transaction IN (Type 1)
//             StoreStockDetails::create([
//                 'transaction_type' => 1,
//                 'user_id'          => $user_id,
//                 'store_id'         => $store_id,
//                 'product_id'       => $request->combo_product_id,
//                 'quantity'         => $request->bundle_qty,
//                 'uom'              => $request->bundle_uom,
//                 'mrp'              => $request->mrp ?? 0,
//                 'unit_price'       => $request->unit_price,
//                 'total_price'      => $request->combo_price,
//                 'gst'              => $request->gst_rate,
//                 'barcode_no'       => $barcode,
//             ]);

//             DB::commit();
//             return response()->json([
//                 'status' => 'success', 
//                 'message' => "Bundle Created! Ref: $combo_ref",
//                 'ref' => $combo_ref
//             ]);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
//         }
//     }
// } 