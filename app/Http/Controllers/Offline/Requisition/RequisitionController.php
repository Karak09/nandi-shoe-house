<?php

namespace App\Http\Controllers\Offline\Requisition;
use App\Http\Controllers\Common\CommonController;
use Illuminate\Http\Request;
use App\Models\Requisition\Requisition;
use App\Models\Product\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Stores\StoreMaster;
use Carbon\Carbon;

class RequisitionController extends CommonController
{
    public function create()
    {
        $user = Auth::user();
        $userDetailsId = $user->user_details_id ?? 1; 
        $userTypeId = $user->user_type_id;
        $userStoreId = StoreMaster::where('user_id', $user->id)
            ->value('id');
        // dd($userStoreId);
        $stores = StoreMaster::where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('id', '!=', $userStoreId)
            ->get();
        
        return view('Offline.Requisition.requisition_create', compact('userDetailsId','userTypeId', 'stores', 'userStoreId'));
    }

    public function getProducts(Request $request)
    {
        $type = $request->type; 
        $storeId = $request->store_id;

        // Eager load priceMaster to get the price
        $query = Product::with(['images', 'category', 'unit', 'priceMaster'])
            ->where('is_active', 1)
            ->where('is_deleted', 0);

        if ($type === 'store' && $storeId) {
            $query->whereHas('storeStock', function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                  ->where('is_active', 1)
                  ->where('is_deleted', 0);
            });
        }

        $products = $query->get()->map(function($product) {
            $images = [];
            if ($product->images) {
                $imageColumns = ['fst_image_doc', 'sec_image_doc', 'trd_image_doc', 'foth_image_doc', 'fiv_image_doc', 'six_image_doc', 'sev_image_doc', 'eig_image_doc'];
                foreach ($imageColumns as $col) {
                    if (!empty($product->images->$col)) {
                        $images[] = asset('storage/' . $product->images->$col);
                    }
                }
            }
            if (count($images) == 0) $images[] = 'https://ui-avatars.com/api/?name=No+Image&background=e2e8f0&color=64748b&size=300';
            
            // Get price from PriceMaster (default to 0 if not found)
            $price = $product->priceMaster->pro_mrp_price ?? 0.00;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
                'pro_size' => $product->pro_size,
                'uom_id' => $product->uom,
                'uom_name' => $product->unit->name ?? 'N/A',
                'category_name' => $product->category->name ?? 'N/A',
                'price' => $price,
                'image_array' => $images
            ];
        });

        return response()->json(['status' => 'success', 'data' => $products]);
    }

    public function store(Request $request)
    {
        if (empty($request->items)) return response()->json(['status' => 'error', 'message' => 'Cart is empty.']);

        DB::beginTransaction();
        try {
            $lastReq = Requisition::orderBy('id', 'desc')->first();
            $sequence = $lastReq ? $lastReq->id + 1 : 1;
            $reqId = 'REQ-' . Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // FIX: Securely get Requester Store ID from Auth, fallback to 1 if Admin/Godown
            $userStoreId = Auth::user()->store_id ?? 1;
            // $sendStoreId = StoreMaster::where('user_id', Auth::id())->value('id');
            // $sendStoreId = StoreMaster::where('user_id', Auth::id())->value('id') ?? 1;

            if (Auth::user()->user_type_id == 6) { // Godown/Admin
                $sendStoreId = 1;
            } else {
                $sendStoreId = StoreMaster::where('user_id', Auth::id())->value('id');
            }
            
            $requisition = Requisition::create([
                'user_id' => Auth::id(),
                'where_req' => $request->where_req,      
                'req_store_id' => $request->req_store_id, 
                'send_store_id' => $sendStoreId, 
                'req_at' => now(),
                'total_amount' => $request->total_amount,
                'status' => 4, // 4 = on-hold (New)
                'ip_address' => $request->ip(),
                'req_id' => $reqId,
                'remarks' => $request->remarks,
            ]);
            // dd($requisition);
            foreach ($request->items as $item) {
                DB::table('requisition_items')->insert([
                    'req_details_id' => $requisition->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'uom' => $item['uom'] ?? null,
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            // dd($request->items);
            // dd([
            //     'user_id' => Auth::id(),
            //     'store_id' => StoreMaster::where('user_id', Auth::id())->value('id'),
            //     'request' => $request->all(),
            // ]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Requisition generated successfully! ID: ' . $reqId], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->user_type_id ?? 1; 
        
        $store = DB::table('store_masters')->where('user_id', $user->id)->first();
        $storeId = $store ? $store->id : null;

        $query = Requisition::with(['items.product.priceMaster', 'items.unit']);

        // --- STRICT VISIBILITY LOGIC ---
        if ($role == 3) { 
            // Sales Manager: Sees requisitions THEY CREATED or sent TO THEIR STORE
            $query->where(function($q) use ($user, $storeId) {
                $q->where('user_id', $user->id)
                  ->orWhere(function($sub) use ($storeId) {
                      $sub->where('where_req', 'Store')->where('req_store_id', $storeId);
                  });
            });
        } elseif ($role == 6) { 
            // Purchase Entry (Godown Manager): Sees things THEY CREATED or sent TO GODOWN
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('where_req', 'Godown');
            });
        } elseif ($role == 8) { 
            // 3rd Party: Sees only their own requests
            $query->where('user_id', $user->id);
        }

        $requisitions = $query->latest()->get()->map(function($req) {
            $req->encrypted_id = $this->encryptData($req->id);

            // Who Req (Requester) -> Find the store of the user who created it
            $creatorStore = DB::table('store_masters')->where('user_id', $req->user_id)->first();
            $req->who_req_name = $creatorStore ? ($creatorStore->store_name ?? $creatorStore->name) : 'Godown/Admin';

            // Where Req (Destination) -> Look at req_store_id
            if ($req->where_req === 'Godown') {
                $req->where_req_name = 'Godown';
            } else {
                $targetStore = DB::table('store_masters')->where('id', $req->req_store_id)->first();
                $req->where_req_name = 'Store (' . ($targetStore ? ($targetStore->store_name ?? $targetStore->name) : 'Unknown') . ')';
            }
            
            $reqUser = DB::table('users_details')->where('id', $req->user_id)->first();
            $req->creator_name = $reqUser ? trim(($reqUser->f_name ?? '') . ' ' . ($reqUser->l_name ?? '') ?? 'System') : 'Unknown';

            // $req->creator_name = $reqUser ? ($reqUser->f_name ?? $reqUser->name ?? 'System') : 'Unknown';

            return $req;
        });
        
        return view('Offline.Requisition.requisition_list', compact('requisitions', 'role', 'storeId'));
    }
    
    // perfectly fine. only reoved barcode [] in db.
    // public function edit($encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     if (!$id) return redirect()->route('requisition.list')->with('error', 'Invalid ID');

    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1;
        
    //     $store = DB::table('store_masters')->where('user_id', $user->id)->first();
    //     $storeId = $store ? $store->id : 1;
        
    //     $requisition = Requisition::with(['items.product.priceMaster', 'items.unit'])->findOrFail($id);
        
    //     $isRequester = ($storeId == $requisition->req_store_id);
    //     $isSender = ($storeId == $requisition->send_store_id);
        
    //     if ($requisition->send_store_id == 1 && in_array($role, [1, 2, 6])) {
    //         $isSender = true; 
    //     }
        
    //     // Fetch detailed stock securely
    //     $stockData = [];
    //     foreach($requisition->items as $item) {
    //         $productId = $item->product_id;
    //         $price = $item->product->priceMaster->pro_mrp_price ?? 0.00;
    //         $barcodes = [];

    //         if ($requisition->where_req == 'Godown') {

    //             $availableQty = DB::table('purchased_stocks')
    //                 ->where('product_id', $productId)
    //                 ->sum('quantity');

    //         } else {

    //             $storeId = $requisition->req_store_id;

    //             $availableQty = DB::table('store_stocks')
    //                 ->where('store_id', $storeId)
    //                 ->where('product_id', $productId)
    //                 ->sum('quantity');

    //             $details = DB::table('store_stock_details')
    //                 ->where('store_id', $storeId)
    //                 ->where('product_id', $productId)
    //                 ->where('quantity', '>', 0)
    //                 ->get();

    //             foreach ($details as $d) {

    //                 $barcode = json_decode($d->barcode_no, true);

    //                 if (is_array($barcode)) {
    //                     $barcode = $barcode[0] ?? null;
    //                 }

    //                 if (empty($barcode)) {
    //                     $barcode = trim($d->barcode_no, '"');
    //                 }

    //                 if (!isset($barcodes[$barcode])) {
    //                     $barcodes[$barcode] = 0;
    //                 }

    //                 if ($d->transaction_type == 1) {

    //                     $barcodes[$barcode] += $d->quantity;

    //                 } elseif (in_array($d->transaction_type, [2, 3])) {

    //                     $barcodes[$barcode] -= $d->quantity;
    //                 }
    //             }
    //         }
    //         $stockData[$productId] = ['available_qty' => $availableQty, 'price' => $price, 'barcodes' => $barcodes];
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role', 'isRequester', 'isSender', 'encrypted_id'));
    // }

    public function edit($encrypted_id)
    {
        $id = $this->decryptData($encrypted_id);
        if (!$id) return redirect()->route('requisition.list')->with('error', 'Invalid ID');

        $user = Auth::user();
        $role = $user->user_type_id ?? 1;
        
        $store = DB::table('store_masters')->where('user_id', $user->id)->first();
        $storeId = $store ? $store->id : 1;
        
        $requisition = Requisition::with(['items.product.priceMaster', 'items.unit'])->findOrFail($id);
        
        $isRequester = ($user->id == $requisition->user_id);

        $isSender = false;

        if ($requisition->where_req == 'Godown') {

            // Godown users/admin/purchase manager
            if (in_array($role, [1, 2, 6]) && !$isRequester) {
                $isSender = true;
            }

        } else {

            // Store requisition
            if ($storeId == $requisition->req_store_id && !$isRequester) {
                $isSender = true;
            }

        }
        
        // Fetch detailed stock securely
        $stockData = [];
        foreach($requisition->items as $item) {
            $productId = $item->product_id;
            $price = $item->product->priceMaster->pro_mrp_price ?? 0.00;
            $barcodes = [];

            if ($requisition->where_req == 'Godown') {

                $availableQty = DB::table('purchased_stocks')
                    ->where('product_id', $productId)
                    ->sum('quantity');

            } else {

                $storeId = $requisition->req_store_id;

                $availableQty = DB::table('store_stocks')
                    ->where('store_id', $storeId)
                    ->where('product_id', $productId)
                    ->sum('quantity');

                $details = DB::table('store_stock_details')
                    ->where('store_id', $storeId)
                    ->where('product_id', $productId)
                    ->where('quantity', '>', 0)
                    ->get();

                foreach ($details as $d) {

                    $barcode = $d->barcode_no;

                    // remove outer quotes repeatedly
                    while (
                        is_string($barcode) &&
                        strlen($barcode) > 1 &&
                        substr($barcode, 0, 1) == '"' &&
                        substr($barcode, -1) == '"'
                    ) {
                        $barcode = trim($barcode, '"');
                    }

                    // remove escaped quotes
                    $barcode = str_replace('\\"', '"', $barcode);

                    // convert ["BARCODE"] to BARCODE
                    if (str_starts_with($barcode, '[')) {

                        $decoded = json_decode($barcode, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $barcode = $decoded[0] ?? '';
                        }
                    }

                    $barcode = trim($barcode);

                    if (empty($barcode)) {
                        continue;
                    }

                    if (!isset($barcodes[$barcode])) {
                        $barcodes[$barcode] = 0;
                    }

                    // Stock In
                    if ((int)$d->transaction_type === 1) {
                        $barcodes[$barcode] += (float)$d->quantity;
                    }

                    // Stock Out
                    if (in_array((int)$d->transaction_type, [2, 3])) {
                        $barcodes[$barcode] -= (float)$d->quantity;
                    }
                }

                // remove zero or negative balance
                foreach ($barcodes as $bc => $qty) {

                    if ($qty <= 0) {
                        unset($barcodes[$bc]);
                    }
                }
            }
            // dd($barcodes);
            $stockData[$productId] = ['available_qty' => $availableQty, 'price' => $price, 'barcodes' => $barcodes];
            // dd([
            //     'where_req' => $requisition->where_req,
            //     'role' => $role,
            //     'login_user' => $user->id,
            //     'req_user' => $requisition->user_id,
            //     'isRequester' => $isRequester,
            //     'isSender' => $isSender
            // ]);
        }

        return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role', 'isRequester', 'isSender', 'encrypted_id'));
    }

    // --- PROCESS WORKFLOW ---
    public function process(Request $request, $encrypted_id)
    {
        $id = $this->decryptData($encrypted_id);
        $requisition = Requisition::with('items')->findOrFail($id);
        $action = $request->action; 

        // Prevent duplicate processing
        if (
            in_array($action, ['approve', 'reject', 'modify']) &&
            in_array($requisition->status, [1, 3])
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'This requisition is already processed.'
            ]);
        }
        
        DB::beginTransaction();
        try {
            // 1. SENDER REJECTS
            if ($action === 'reject') {
                if(empty($request->remarks)) throw new \Exception("Remarks are mandatory for rejection.");
                $requisition->update(['status' => 3, 'remarks3' => $request->remarks, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
            } 
            
            // 2. SENDER MODIFIES
            elseif ($action === 'modify') {
                if(empty($request->remarks)) throw new \Exception("Remarks are mandatory when modifying.");
                
                foreach ($request->items as $itemId => $data) {
                    // STRICT: Only update modify_quantity. Original quantity remains intact.
                    DB::table('requisition_items')->where('id', $itemId)->update([
                        'modify_quantity' => $data['modify_quantity']
                    ]);
                }
                $requisition->update(['status' => 2, 'remarks1' => $request->remarks, 'modified_by' => Auth::id(), 'modified_at' => now()]);
            } 
            
            // 3. REQUESTER REJECTS
            elseif ($action === 'requester_reject') {
                if(empty($request->remarks)) throw new \Exception("Remarks are mandatory for rejection.");
                $requisition->update(['status' => 3, 'remarks2' => $request->remarks, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
            }
            
            // 4. REQUESTER ACCEPTS
            elseif ($action === 'requester_accept') {
                $requisition->update(['status' => 5, 'req_accept_by' => 1]); // 5 = Pending Final Sender Approval
            }
            
            // 5. SENDER FINAL APPROVE & DEDUCT
            elseif ($action === 'approve') {

            if ($requisition->status == 1) {
                throw new \Exception('Already approved.');
            }
                // Godown requisition
                if ($requisition->where_req == 'Godown') {

                    $purchaseId = DB::table('purchase_details')->insertGetId([
                        'user_id' => Auth::id(),
                        'challan_no' => $requisition->req_id,
                        'challan_date' => date('Y-m-d'),
                        'total' => $requisition->total_amount,
                        'ip_address' => request()->ip(),
                        'transaction_type' => 2,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                foreach ($requisition->items as $item) {

                    $finalQty = ($item->modify_quantity !== null)
                        ? $item->modify_quantity
                        : $item->quantity;

                    if ($finalQty <= 0) {
                        continue;
                    }

                    // Save approved quantity
                    DB::table('requisition_items')
                        ->where('id', $item->id)
                        ->update([
                            'approved_quantity' => $finalQty
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | GODOWN APPROVAL
                    |--------------------------------------------------------------------------
                    */
                    if ($requisition->where_req == 'Godown') {

                        $currentStock = DB::table('purchased_stocks')
                            ->where('product_id', $item->product_id)
                            ->sum('quantity');

                        if ((float)$currentStock < (float)$finalQty) {
                            throw new \Exception('Stock not available.');
                        }

                        // deduct godown stock
                        DB::table('purchased_stocks')
                            ->where('product_id', $item->product_id)
                            ->decrement('quantity', $finalQty);

                            
                        $storeStock = DB::table('store_stocks')
                            ->where('store_id', $requisition->req_store_id)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($storeStock) {

                            DB::table('store_stocks')
                                ->where('id', $storeStock->id)
                                ->increment('quantity', $finalQty);
                        
                        } else {
                            dd([
                                'req_store_id' => $requisition->req_store_id,
                                'where_req' => $requisition->where_req,
                                'requisition_id' => $requisition->id,
                            ]);

                            DB::table('store_stocks')->insert([
                                'store_id' => $requisition->req_store_id,
                                'product_id' => $item->product_id,
                                'quantity' => $finalQty,
                                'uom' => $item->uom,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                        
                        // get product price
                        $price = DB::table('price_masters')
                            ->where('product_id', $item->product_id)
                            ->first();

                        DB::table('purchase_transaction_details')->insert([
                            'purchase_details_id' => $purchaseId,
                            'store_id' => $requisition->req_store_id,
                            'product_id' => $item->product_id,
                            'quantity' => $finalQty,
                            'uom' => $item->uom,
                            'mrp' => $price->pro_mrp_price ?? 0,
                            'unit_price' => $price->pro_sale_price ?? 0,
                            'total_price' => ($price->pro_sale_price ?? 0) * $finalQty,
                            'batch_no' => json_encode([]),
                            'transaction_type' => 2,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STORE APPROVAL
                    |--------------------------------------------------------------------------
                    */
                    else {

                        $storeId = $requisition->req_store_id;

                        $selectedBarcode =
                            $request->items[$item->id]['barcode'] ?? null;

                        if (!$selectedBarcode) {
                            throw new \Exception('Please select barcode.');
                        }

                        $currentStock = DB::table('store_stocks')
                            ->where('store_id', $storeId)
                            ->where('product_id', $item->product_id)
                            ->sum('quantity');

                        if ((float)$currentStock < (float)$finalQty) {
                            throw new \Exception('Stock not available.');
                        }

                        // deduct stock
                        DB::table('store_stocks')
                            ->where('store_id', $storeId)
                            ->where('product_id', $item->product_id)
                            ->decrement('quantity', $finalQty);

                        $price = DB::table('price_masters')
                            ->where('product_id', $item->product_id)
                            ->first();

                        DB::table('store_stock_details')->insert([
                            'user_id' => Auth::id(),
                            'store_id' => $storeId,
                            'requisition_details_id' => $requisition->id, // same for all products
                            'product_id' => $item->product_id,
                            'quantity' => $finalQty,
                            'uom' => $item->uom,
                            'mrp' => $price->pro_mrp_price ?? 0,
                            'unit_price' => $price->pro_sale_price ?? 0,
                            'total_price' => ($price->pro_sale_price ?? 0) * $finalQty,
                            'barcode_no' => json_encode([$selectedBarcode]),
                            'batch_no' => json_encode([]),
                            'transaction_type' => 4,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                $requisition->update([
                    'status' => 1,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Processed Successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}


    // public function index()
    // {
    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1; 
        
    //     // Find the Store mapped to this User
    //     $store = DB::table('store_masters')->where('user_id', $user->id)->first();
    //     $storeId = $store ? $store->id : null;

    //     $query = Requisition::with(['items.product.priceMaster', 'items.unit']);

    //     // --- STRICT RBAC VISIBILITY LOGIC ---
    //     if ($role == 3) { 
    //         // Sales Manager: Sees requisitions where their store is either requesting or sending
    //         $query->where(function($q) use ($storeId) {
    //             $q->where('req_store_id', $storeId)->orWhere('send_store_id', $storeId);
    //         });
    //     } elseif ($role == 6) { 
    //         // Purchase Entry: Sees requisitions ONLY sent to Godown (Assuming Godown is 1 or where_req is Godown)
    //         $query->where('send_store_id', 1)->orWhere('where_req', 'Godown');
    //     } elseif ($role == 8) { 
    //         // 3rd Party: Sees only their own requests
    //         $query->where('user_id', $user->id);
    //     }
    //     // Roles 1 & 2 (Super Admin & Admin) skip constraints and see all.

    //     $requisitions = $query->latest()->get()->map(function($req) {
    //         $req->encrypted_id = $this->encryptData($req->id);

    //         // Fetch "Who Req" Name
    //         $reqStore = DB::table('store_masters')->where('id', $req->req_store_id)->first();
    //         $req->who_req_name = $reqStore ? ($reqStore->store_name ?? 'Unknown Store') : 'Godown';

    //         // Fetch "Where Req" Name
    //         if ($req->where_req === 'Godown' || $req->send_store_id == 1) {
    //             $req->where_req_name = 'Godown';
    //         } else {
    //             $sendStore = DB::table('store_masters')->where('id', $req->send_store_id)->first();
    //             $req->where_req_name = 'Store (' . ($sendStore ? $sendStore->store_name : 'Unknown') . ')';
    //         }

    //         return $req;
    //     });
        
    //     return view('Offline.Requisition.requisition_list', compact('requisitions', 'role', 'storeId'));
    // }


    //  02.06.26

    // --- 3. EDIT PAGE ---
    // public function edit($encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     if (!$id) return redirect()->route('requisition.list')->with('error', 'Invalid ID');

    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1;
        
    //     // Find Store
    //     $store = DB::table('store_masters')->where('user_id', $user->id)->first();
    //     $storeId = $store ? $store->id : 1;
        
    //     $requisition = Requisition::with(['items.product.priceMaster', 'items.unit'])->findOrFail($id);
        
    //     // Ping-Pong Role Verification
    //     $isRequester = ($storeId == $requisition->req_store_id);
    //     $isSender = ($storeId == $requisition->send_store_id);
        
    //     // Purchase Entry (6) acts as the Sender for the Godown
    //     if ($requisition->send_store_id == 1 && in_array($role, [1, 2, 6])) {
    //         $isSender = true; 
    //     }
        
    //     // Fetch detailed stock
    //     $stockData = [];
    //     foreach($requisition->items as $item) {
    //         $productId = $item->product_id;
            
    //         if ($requisition->send_store_id == 1) { 
    //             $availableQty = DB::table('purchased_stocks')->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('purchase_transaction_details')->where('product_id', $productId)->where('transaction_type', 1)->select('batch_no', 'uom', 'quantity')->get();
    //         } else { 
    //             $availableQty = DB::table('store_stocks')->where('store_id', $requisition->req_store_id)->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('store_stock_details')->where('store_id', $requisition->req_store_id)->where('product_id', $productId)->whereIn('transaction_type', [1, 3])->select('batch_no', 'barcode_no', 'uom', 'quantity')->get();
    //         }
    //         $stockData[$productId] = ['available_qty' => $availableQty, 'details' => $batches];
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role', 'isRequester', 'isSender', 'encrypted_id'));
    // }

    // // --- 4. PROCESS WORKFLOW ---
    // public function process(Request $request, $encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     $requisition = Requisition::with('items')->findOrFail($id);
    //     $action = $request->action; 

    //     $store = DB::table('store_masters')->where('user_id', Auth::id())->first();
    //     $userStoreId = $store ? $store->id : 1;
    //     $isRequester = ($userStoreId == $requisition->req_store_id);

    //     DB::beginTransaction();
    //     try {
    //         if ($action === 'reject') {
    //             if(empty($request->remarks)) throw new \Exception("Remarks are mandatory for rejection.");
    //             $requisition->update(['status' => 3, 'remarks' => $request->remarks, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
    //         } 
    //         elseif ($action === 'modify') {
    //             foreach ($request->items as $itemId => $data) {
    //                 DB::table('requisition_items')->where('id', $itemId)->update(['quantity' => $data['quantity'], 'uom' => $data['uom']]);
    //             }
    //             $requisition->update(['status' => 2, 'remarks' => $request->remarks ?? $requisition->remarks, 'modified_by' => Auth::id(), 'modified_at' => now()]);
    //         } 
    //         elseif ($action === 'approve') {
    //             if ($isRequester) {
    //                 // Requester accepts Sender's modifications. Status 5.
    //                 $requisition->update(['status' => 5, 'remarks' => $request->remarks ?? $requisition->remarks]);
    //             } else {
    //                 // Sender Approves -> Deduct Stock
    //                 foreach ($requisition->items as $item) {
    //                     $finalQty = $request->items[$item->id]['quantity'] ?? $item->quantity;
                        
    //                     if ($requisition->send_store_id == 1) {
    //                         DB::table('purchased_stocks')->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                         DB::table('purchase_transaction_details')->insert(['purchase_details_id' => 0, 'product_id' => $item->product_id, 'quantity' => $finalQty, 'uom' => $item->uom, 'transaction_type' => 2, 'created_at' => now(), 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                         DB::table('store_stock_details')->insert(['store_id' => $requisition->send_store_id, 'product_id' => $item->product_id, 'quantity' => $finalQty, 'uom' => $item->uom, 'transaction_type' => 2, 'created_at' => now(), 'updated_at' => now()]);
    //                     }
    //                 }
    //                 $requisition->update(['status' => 1, 'remarks' => $request->remarks ?? $requisition->remarks, 'approved_by' => Auth::id(), 'approved_at' => now()]);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Processed Successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }


//     01.06.26

    // --- 2. LIST PAGE ---
    // public function index()
    // {
    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? null; 
    //     $storeId = $user->store_id ?? null; 
    //     // dd($storeId);
    //     $query = Requisition::with(['items.product.priceMaster', 'items.unit']);

    //     // Visibility: Users only see Requisitions linked to their Store (Either as Requester or Sender)
    //     if (in_array($role, [3, 6, 8])) { 
    //         $query->where(function($q) use ($storeId) {
    //             $q->where('req_store_id', $storeId)->orWhere('send_store_id', $storeId);
    //         });
    //     }

    //     $requisitions = $query->latest()->get()->map(function($req) {
    //         $req->encrypted_id = $this->encryptData($req->id);

    //         // Fetch "Who Req" (Requesting Store)
    //         $reqStore = DB::table('store_masters')->where('id', $req->req_store_id)->first();
    //         $req->who_req_name = $reqStore ? ($reqStore->store_name ?? $reqStore->name) : 'Godown';

    //         // Fetch "Where Req" (Sending Store)
    //         if ($req->where_req === 'Godown') {
    //             $req->where_req_name = 'Godown';
    //         } else {
    //             $sendStore = DB::table('store_masters')->where('id', $req->send_store_id)->first();
    //             $req->where_req_name = 'Store (' . $req->who_req_name . ')';
    //             // $req->where_req_name = 'Store (' . ($sendStore ? ($sendStore->store_name ?? $sendStore->name) : 'Unknown') . ')';
    //         }

    //         return $req;
    //     });
        
    //     return view('Offline.Requisition.requisition_list', compact('requisitions', 'role', 'storeId'));
    // }

    // // --- 3. EDIT PAGE ---
    // public function edit($encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     if (!$id) return redirect()->route('requisition.list')->with('error', 'Invalid ID');

    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1;
    //     $storeId = $user->store_id ?? 1;
        
    //     $requisition = Requisition::with(['items.product.priceMaster', 'items.unit'])->findOrFail($id);
        
    //     // Define if viewing user is Requester or Sender
    //     $isRequester = ($storeId == $requisition->req_store_id);
    //     $isSender = ($storeId == $requisition->send_store_id);
    //     if ($requisition->send_store_id == 1 && in_array($role, [1, 2])) $isSender = true; // Admins handle Godown
        
    //     // Fetch detailed stock
    //     $stockData = [];
    //     foreach($requisition->items as $item) {
    //         $productId = $item->product_id;
            
    //         if ($requisition->send_store_id == 1) { 
    //             $availableQty = DB::table('purchased_stocks')->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('purchase_transaction_details')->where('product_id', $productId)->where('transaction_type', 1)->select('batch_no', 'uom', 'quantity')->get();
    //         } else { 
    //             $availableQty = DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('store_stock_details')->where('store_id', $requisition->send_store_id)->where('product_id', $productId)->whereIn('transaction_type', [1, 3])->select('batch_no', 'barcode_no', 'uom', 'quantity')->get();
    //         }
    //         $stockData[$productId] = ['available_qty' => $availableQty, 'details' => $batches];
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role', 'isRequester', 'isSender', 'encrypted_id'));
    // }

    // // --- 4. PROCESS WORKFLOW ---
    // public function process(Request $request, $encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     $requisition = Requisition::with('items')->findOrFail($id);
    //     $action = $request->action; 

    //     $userStoreId = Auth::user()->store_id ?? 1;
    //     $isRequester = ($userStoreId == $requisition->req_store_id);

    //     DB::beginTransaction();
    //     try {
    //         if ($action === 'reject') {
    //             if(empty($request->remarks)) throw new \Exception("Remarks are mandatory for rejection.");
    //             $requisition->update(['status' => 3, 'remarks' => $request->remarks, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
    //         } 
    //         elseif ($action === 'modify') {
    //             foreach ($request->items as $itemId => $data) {
    //                 DB::table('requisition_items')->where('id', $itemId)->update(['quantity' => $data['quantity'], 'uom' => $data['uom']]);
    //             }
    //             // Status 2. We save the modifier so the Ping-Pong logic knows whose turn it is.
    //             $requisition->update(['status' => 2, 'remarks' => $request->remarks ?? $requisition->remarks, 'modified_by' => Auth::id(), 'modified_at' => now()]);
    //         } 
    //         elseif ($action === 'approve') {
    //             if ($isRequester) {
    //                 // Requester accepts Sender's modifications. Status 5 = Requester Approved. Goes back to sender.
    //                 $requisition->update(['status' => 5, 'remarks' => $request->remarks ?? $requisition->remarks]);
    //             } else {
    //                 // Sender Approves -> Deduct Stock and Confirm
    //                 foreach ($requisition->items as $item) {
    //                     $finalQty = $request->items[$item->id]['quantity'] ?? $item->quantity;
                        
    //                     if ($requisition->send_store_id == 1) {
    //                         DB::table('purchased_stocks')->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                         DB::table('purchase_transaction_details')->insert(['purchase_details_id' => 0, 'product_id' => $item->product_id, 'quantity' => $finalQty, 'uom' => $item->uom, 'transaction_type' => 2, 'created_at' => now(), 'updated_at' => now()]);
    //                     } else {
    //                         DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                         DB::table('store_stock_details')->insert(['store_id' => $requisition->send_store_id, 'product_id' => $item->product_id, 'quantity' => $finalQty, 'uom' => $item->uom, 'transaction_type' => 2, 'created_at' => now(), 'updated_at' => now()]);
    //                     }
    //                 }
    //                 $requisition->update(['status' => 1, 'remarks' => $request->remarks ?? $requisition->remarks, 'approved_by' => Auth::id(), 'approved_at' => now()]);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Processed Successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }



////  31.05.26
    // --- STORE METHOD (Updated with new columns) ---
    // public function store(Request $request)
    // {
    //     if (empty($request->items) || !is_array($request->items)) {
    //         return response()->json(['status' => 'error', 'message' => 'Cart is empty.']);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $lastReq = Requisition::orderBy('id', 'desc')->first();
    //         $sequence = $lastReq ? $lastReq->id + 1 : 1;
    //         $reqId = 'REQ-' . \Carbon\Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

    //         // Save new columns: where_req, req_store_id, total_amount, req_at
    //         $requisition = Requisition::create([
    //             'user_id' => Auth::id() ?? 1,
    //             'where_req' => $request->where_req,       // 'Godown' or 'Store'
    //             'req_store_id' => $request->req_store_id, // Store ID (or 1 for Godown)
    //             'req_at' => now(),
    //             'total_amount' => $request->total_amount,
    //             'status' => 4, // 4 = on-hold
    //             'ip_address' => $request->ip(),
    //             'req_id' => $reqId,
    //             'remarks' => $request->remarks,
    //         ]);
    //         // dd($request);
    //         foreach ($request->items as $item) {
    //             DB::table('requisition_items')->insert([
    //                 'req_details_id' => $requisition->id,
    //                 'product_id' => $item['product_id'],
    //                 'quantity' => $item['qty'],
    //                 'price' => $item['price'],
    //                 'uom' => $item['uom'] ?? null,
    //                 'ip_address' => $request->ip(),
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //         }
    //         // dd($request->items);
    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Requisition generated successfully! ID: ' . $reqId], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()], 500);
    //     }
    // }

    // public function index()
    // {
    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1; 
    //     $storeId = $user->store_id ?? null; 
    //     // dd($storeId);
    //     // Eager load items with their actual Product and Unit data
    //     $query = Requisition::with(['items.product', 'items.unit']);

    //     // Strict Role Visibility Logic
    //     if ($role == 6) { 
    //         // Admin sees only Godown (store 1)
    //         $query->where('req_store_id', 1);
    //     } elseif ($role == 3) { 
    //         // Store Manager sees requisitions linked to their specific store
    //         $query->where(function($q) use ($storeId) {
    //             $q->where('req_store_id', $storeId)->orWhere('send_store_id', $storeId);
    //         });
    //     } elseif ($role == 8) { 
    //         // 3rd Party sees only their own
    //         $query->where('user_id', $user->id);
    //     }

    //     // Fetch and append Encrypted IDs dynamically
    //     $requisitions = $query->latest()->get()->map(function($req) {
    //         $req->encrypted_id = $this->encryptData($req->id);
    //         return $req;
    //     });
        
    //     return view('Offline.Requisition.requisition_list', compact('requisitions', 'role', 'storeId'));
    // }

    // // --- EDIT / APPROVAL PAGE ---
    // public function edit($encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     if (!$id) return redirect()->route('requisition.list')->with('error', 'Invalid Requisition ID');

    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1;
    //     $storeId = $user->store_id ?? null;
        
    //     // Eager load everything including PriceMaster for MRP
    //     $requisition = Requisition::with(['items.product.priceMaster', 'items.unit'])->findOrFail($id);
        
    //     $stockData = [];

    //     foreach($requisition->items as $item) {
    //         $productId = $item->product_id;
    //         $mrp = $item->product->priceMaster->pro_mrp_price ?? 0;

    //         if ($requisition->send_store_id == 1) { 
    //             // Godown Logic
    //             $availableQty = DB::table('purchased_stocks')->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('purchase_transaction_details')
    //                 ->where('product_id', $productId)
    //                 ->where('transaction_type', 1) // Inward batches
    //                 ->select('batch_no', 'uom', 'quantity')
    //                 ->get();

    //             $stockData[$productId] = [
    //                 'available_qty' => $availableQty,
    //                 'mrp' => $mrp,
    //                 'details' => $batches
    //             ];
    //         } else { 
    //             // Store Logic
    //             $availableQty = DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $productId)->sum('quantity');
    //             $batches = DB::table('store_stock_details')
    //                 ->where('store_id', $requisition->send_store_id)
    //                 ->where('product_id', $productId)
    //                 ->whereIn('transaction_type', [1, 3]) // Inward or Combo Add
    //                 ->select('batch_no', 'barcode_no', 'uom', 'quantity')
    //                 ->get();

    //             $stockData[$productId] = [
    //                 'available_qty' => $availableQty,
    //                 'mrp' => $mrp,
    //                 'details' => $batches
    //             ];
    //         }
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role', 'storeId', 'encrypted_id'));
    // }

    // // --- PROCESS (Confirm / Modify / Reject) ---
    // public function process(Request $request, $encrypted_id)
    // {
    //     $id = $this->decryptData($encrypted_id);
    //     if (!$id) return response()->json(['status' => 'error', 'message' => 'Invalid Request']);

    //     $requisition = Requisition::with('items')->findOrFail($id);
    //     $action = $request->action; 

    //     DB::beginTransaction();
    //     try {
    //         if ($action === 'reject') {
    //             $requisition->update(['status' => 3, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
    //         } 
    //         elseif ($action === 'modify') {
    //             foreach ($request->items as $itemId => $data) {
    //                 DB::table('requisition_items')->where('id', $itemId)->update([
    //                     'quantity' => $data['quantity'],
    //                     'uom' => $data['uom'] // Stores updated UOM ID
    //                 ]);
    //             }
    //             $requisition->update(['status' => 2, 'modified_by' => Auth::id(), 'modified_at' => now()]);
    //         } 
    //         elseif ($action === 'confirm') {
    //             foreach ($requisition->items as $item) {
    //                 $finalQty = $request->items[$item->id]['quantity'] ?? $item->quantity;
                    
    //                 if ($requisition->send_store_id == 1) {
    //                     // Godown Deduction
    //                     DB::table('purchased_stocks')->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
                        
    //                     // Log Outward Movement (transaction_type = 2)
    //                     DB::table('purchase_transaction_details')->insert([
    //                         'purchase_details_id' => 0, // System generated deduction
    //                         'product_id' => $item->product_id,
    //                         'quantity' => $finalQty,
    //                         'uom' => $item->uom,
    //                         'transaction_type' => 2, 
    //                         'created_at' => now(),
    //                         'updated_at' => now()
    //                     ]);
    //                 } else {
    //                     // Store Deduction
    //                     DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $item->product_id)->decrement('quantity', $finalQty);

    //                     // Log Outward Movement (transaction_type = 2)
    //                     DB::table('store_stock_details')->insert([
    //                         'store_id' => $requisition->send_store_id,
    //                         'product_id' => $item->product_id,
    //                         'quantity' => $finalQty,
    //                         'uom' => $item->uom,
    //                         'transaction_type' => 2, 
    //                         'created_at' => now(),
    //                         'updated_at' => now()
    //                     ]);
    //                 }
    //             }
    //             $requisition->update(['status' => 1, 'approved_by' => Auth::id(), 'approved_at' => now()]);
    //         }

    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Requisition processed successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    //     }
    // }



///    28.05.26
// // --- LIST PAGE (Role Based) ---
    // public function index()
    // {
    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1; // 1: Superadmin, 2:Admin, 3: Sales Manager, 4:Reporter, 5:Account, 6: Purchase Entry, 7: Stock Transfer, 8: 3rd party
    //     $storeId = $user->store_id ?? null; 

    //     // 1. Eager load items ALONG WITH their related product and unit data
    //     $query = Requisition::with(['items.product', 'items.unit']);

    //     // 2. Strict Role Visibility Logic
    //     if ($role == 6) { 
    //         // Admin (3) sees only Godown (store 1)
    //         $query->where('req_store_id', 1);
    //     } elseif ($role == 3) { 
    //         // Store Manager (6) sees requisitions linked to their specific store
    //         $query->where(function($q) use ($storeId) {
    //             $q->where('req_store_id', $storeId)->orWhere('send_store_id', $storeId);
    //         });
    //     } elseif ($role == 8) { 
    //         // 3rd Party (7) sees only their own created requisitions
    //         $query->where('user_id', $user->id);
    //     }
    //     // Superadmin (1) skips the conditionals and sees everything

    //     $requisitions = $query->latest()->get();
        
    //     return view('Offline.Requisition.requisition_list', compact('requisitions', 'role', 'storeId'));
    // }

    // // --- EDIT / APPROVAL PAGE ---
    // public function edit($id)
    // {
    //     $user = Auth::user();
    //     $role = $user->user_type_id ?? 1;
    //     $requisition = Requisition::with('items')->findOrFail($id);
        
    //     // Fetch detailed stock based on destination
    //     $stockData = [];
    //     foreach($requisition->items as $item) {
            
    //         // Godown (Usually ID 1): Get from purchase_transaction_details
    //         if ($requisition->send_store_id == 1) { 
    //             $stock = DB::table('purchase_transaction_details')
    //                 ->where('product_id', $item->product_id)
    //                 ->where('quantity', '>', 0)
    //                 ->select('batch_no', 'uom', 'quantity')
    //                 ->get();
    //         } 
    //         // Store: Get from store_stock_details
    //         else { 
    //             $stock = DB::table('store_stock_details')
    //                 ->where('store_id', $requisition->send_store_id)
    //                 ->where('product_id', $item->product_id)
    //                 ->where('quantity', '>', 0)
    //                 ->select('batch_no', 'barcode_no', 'uom', 'quantity')
    //                 ->get();
    //         }
    //         $stockData[$item->product_id] = $stock;
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData', 'role'));
    // }

    // // --- PROCESS (Confirm / Modify / Reject) ---
    // public function process(Request $request, $id)
    // {
    //     $requisition = Requisition::with('items')->findOrFail($id);
    //     $action = $request->action; 

    //     DB::beginTransaction();
    //     try {
    //         if ($action === 'reject') {
    //             $requisition->update(['status' => 3, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
    //         } 
    //         elseif ($action === 'modify') {
    //             // Update quantities but DO NOT deduct stock. Status = 2 (Modified)
    //             foreach ($request->items as $itemId => $data) {
    //                 DB::table('requisition_items')->where('id', $itemId)->update([
    //                     'quantity' => $data['quantity'],
    //                     'uom' => $data['uom']
    //                 ]);
    //             }
    //             $requisition->update(['status' => 2, 'modified_by' => Auth::id(), 'modified_at' => now()]);
    //         } 
    //         elseif ($action === 'confirm') {
    //             // Deduct Stock Logic
    //             foreach ($requisition->items as $item) {
    //                 // Overwrite item quantity if it was modified in this final step
    //                 $finalQty = $request->items[$item->id]['quantity'] ?? $item->quantity;
                    
    //                 if ($requisition->send_store_id == 1) {
    //                     // Deduct from Godown (Main Table)
    //                     DB::table('purchased_stocks')->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                 } else {
    //                     // Deduct from Store (Main Table)
    //                     DB::table('store_stocks')->where('store_id', $requisition->send_store_id)->where('product_id', $item->product_id)->decrement('quantity', $finalQty);
    //                 }
                    
    //                 // Note: If you need strict FIFO batch deduction, you would loop through transaction_details here and deduct row by row. 
    //                 // For now, it deducts from the master stock pools to keep the DB balanced.
    //             }
    //             $requisition->update(['status' => 1, 'approved_by' => Auth::id(), 'approved_at' => now()]);
    //         }

    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Requisition updated successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    //     }
    // }

    

//     27.05.26

    // public function index()
    // {
    //     $user = Auth::user();
    //     $role = $user->user_details_id ?? 1; 

    //     $query = Requisition::with('items');

    //     // Role Logic: Admin(3), Store Manager(6), 3rd Party(7), Superadmin(1)
    //     if ($role == 3) {
    //         // Admin sees godown requisitions (Assuming godown req_store_id = 1)
    //         $query->where('req_store_id', 1);
    //     } elseif ($role == 6) {
    //         // Store Manager sees their store
    //         $query->where('req_store_id', $user->store_id);
    //     } elseif ($role == 7) {
    //         // 3rd party sees their own requisitions
    //         $query->where('user_id', $user->id);
    //     }

    //     $requisitions = $query->latest()->get();
    //     return view('Offline.Requisition.requisition_list', compact('requisitions', 'role'));
    // }

    // // --- EDIT / APPROVAL PAGE ---
    // public function edit($id)
    // {
    //     $requisition = Requisition::with('items')->findOrFail($id);
        
    //     // Fetch stock based on destination
    //     $stockData = [];
    //     foreach($requisition->items as $item) {
    //         if ($requisition->send_store_id == 1) { // Assuming 1 is Godown
    //             $stock = DB::table('purchased_stocks')->where('product_id', $item->product_id)->sum('quantity');
    //             $stockData[$item->product_id] = $stock;
    //         } else { // Store
    //             $stock = DB::table('store_stocks')
    //                 ->where('store_id', $requisition->send_store_id)
    //                 ->where('product_id', $item->product_id)
    //                 ->sum('quantity');
    //             $stockData[$item->product_id] = $stock;
    //         }
    //     }

    //     return view('Offline.Requisition.requisition_edit', compact('requisition', 'stockData'));
    // }

    // // --- PROCESS (Confirm / Modify / Reject) ---
    // public function process(Request $request, $id)
    // {
    //     $requisition = Requisition::findOrFail($id);
    //     $action = $request->action; // 'confirm', 'modify', 'reject'

    //     DB::beginTransaction();
    //     try {
    //         if ($action === 'reject') {
    //             $requisition->update(['status' => 3, 'rejected_by' => Auth::id(), 'rejected_at' => now()]);
    //         } 
    //         elseif ($action === 'modify') {
    //             // Update quantities but DO NOT deduct stock
    //             foreach ($request->items as $itemId => $data) {
    //                 DB::table('requisition_items')->where('id', $itemId)->update([
    //                     'quantity' => $data['quantity'],
    //                     'uom' => $data['uom']
    //                 ]);
    //             }
    //             $requisition->update(['status' => 2, 'modified_by' => Auth::id(), 'modified_at' => now()]);
    //         } 
    //         elseif ($action === 'confirm') {
    //             // Deduct Stock Logic Here based on send_store_id
    //             foreach ($requisition->items as $item) {
    //                 if ($requisition->send_store_id == 1) {
    //                     // Godown Deduction Logic (FIFO on purchased_stocks)
    //                     // Implement your specific deduction query here
    //                 } else {
    //                     // Store Deduction Logic (store_stocks)
    //                     // Implement your specific deduction query here
    //                 }
    //             }
    //             $requisition->update(['status' => 1, 'approved_by' => Auth::id(), 'approved_at' => now()]);
    //         }

    //         DB::commit();
    //         return response()->json(['status' => 'success', 'message' => 'Requisition updated successfully.']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }


// 27.05.26


// namespace App\Http\Controllers\Offline\Requisition;
// use App\Http\Controllers\Common\CommonController;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Requisition\Requisition;
// use App\Models\Product\Product;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;

// class RequisitionController extends CommonController
// {
//     public function create()
//     {
//         $user = Auth::user();
//         $userDetailsId = $user->user_details_id ?? 1; 
//         $userStoreId = $user->store_id ?? null; 
//         $stores = DB::table('store_masters')->where('is_active', 1)->get();

//         return view('Offline.Requisition.requisition_create', compact('userDetailsId', 'stores', 'userStoreId'));
//     }

//     // --- ELOQUENT API FETCH ---
//     public function getProducts(Request $request)
//     {
//         $type = $request->type; 
//         $storeId = $request->store_id;

//         // Clean Eloquent Query with Eager Loading
//         $query = Product::with(['images', 'category', 'unit'])
//             ->where('is_active', 1)
//             ->where('is_deleted', 0);

//         if ($type === 'store' && $storeId) {
//             $query->whereHas('storeStock', function ($q) use ($storeId) {
//                 $q->where('store_id', $storeId)
//                 ->where('is_active', 1)
//                 ->where('is_deleted', 0);
//             });
//         }

//         $products = $query->get()->map(function($product) {
//             $images = [];
//             $img = $product->images;

//             if ($img) {
//                 if ($img->fst_image_doc) {
//                     $images[] = asset('storage/' . rawurlencode($img->fst_image_doc));
//                 }

//                 if ($img->sec_image_doc) {
//                     $images[] = asset('storage/' . rawurlencode($img->sec_image_doc));
//                 }

//                 if ($img->trd_image_doc) {
//                     $images[] = asset('storage/' . rawurlencode($img->trd_image_doc));
//                 }
//             }
            
//             return [
//                 'id' => $product->id,
//                 'name' => $product->name,
//                 'product_code' => $product->product_code,
//                 'pro_size' => $product->pro_size,
//                 'uom_id' => $product->uom,
//                 'uom_name' => $product->unit->name ?? 'N/A',
//                 'category_name' => $product->category->name ?? 'N/A',
//                 'image_array' => count($images) > 0 ? $images : [asset('images/placeholder.png')]
//             ];
//         });

//         return response()->json(['status' => 'success', 'data' => $products]);
//     }




//         27.05.26

// namespace App\Http\Controllers\Offline\Requisition;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Requisition\Requisition;
// use App\Models\Requisition\RequisitionItem;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;
// use Carbon\Carbon;
// // Make sure to import your ProductMaster model here

// class RequisitionController extends Controller
// {
//     public function index()
//     {
//         $requisitions = Requisition::with('items')->latest()->get();
//         return view('Offline.Requisition.requisition_list', compact('requisitions'));
//     }

//     public function create()
//     {
//         $user = Auth::user();
//         $userDetailsId = $user->user_details_id ?? 1; // Default for testing
//         $userStoreId = $user->store_id ?? null; 

//         // Only fetch stores on page load, NO products.
//         $stores = DB::table('store_masters')->where('is_active', 1)->get();

//         return view('Offline.Requisition.requisition_create', compact('userDetailsId', 'stores', 'userStoreId'));
//     }

//     public function getProducts(Request $request)
//     {
//         $type = $request->type; // 'godown' or 'store'
//         $storeId = $request->store_id;

//         // Base Query: Get active products and join images, categories, and units
//         $query = DB::table('product_masters')
//             ->leftJoin('product_images', 'product_masters.id', '=', 'product_images.product_id')
//             ->leftJoin('category_masters', 'product_masters.cat_id', '=', 'category_masters.id')
//             ->leftJoin('unit_masters', 'product_masters.uom', '=', 'unit_masters.id') 
//             ->where('product_masters.is_active', 1)
//             ->where('product_masters.is_deleted', 0)
//             ->select(
//                 'product_masters.id', 'product_masters.name', 'product_masters.product_code', 
//                 'product_masters.pro_size',
//                 'product_masters.uom',
//                 'category_masters.name as category_name',
//                 'unit_masters.name as uom_name',
//                 'product_images.fst_image_doc', 'product_images.sec_image_doc', 
//                 'product_images.trd_image_doc'
//             );

//         // If type is 'store', ONLY get products available in that specific store from store_stocks
//         if ($type === 'store') {
//             if (!$storeId) {
//                 return response()->json(['status' => 'error', 'message' => 'Store ID missing']);
//             }
//             $query->join('store_stocks', 'product_masters.id', '=', 'store_stocks.product_id')
//                 ->where('store_stocks.store_id', $storeId)
//                 ->where('store_stocks.is_active', 1)
//                 ->where('store_stocks.is_deleted', 0);
//         }

//         $products = $query->get();
            
//         // Format image array
//         $products->map(function($product) {
//             $images = [];
//             if($product->fst_image_doc) $images[] = asset('storage/' . rawurlencode($product->fst_image_doc));
//             if($product->sec_image_doc) $images[] = asset('storage/' . rawurlencode($product->sec_image_doc));
//             if($product->trd_image_doc) $images[] = asset('storage/' . rawurlencode($product->trd_image_doc));
            
//             $product->image_array = count($images) > 0 ? $images : [asset('images/placeholder.png')];
//             return $product;
//         });

//         return response()->json(['status' => 'success', 'data' => $products]);
//     }

//     public function store(Request $request)
//     {
//         // 1. Backend Validation
//         $validator = Validator::make($request->all(), [
//             'req_store_id' => 'nullable|integer',
//             'send_store_id' => 'nullable|integer',
//             'items' => 'required|array|min:1',
//             'items.*.product_id' => 'required|integer',
//             'items.*.qty' => 'required|numeric|min:1',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation Error. Please check your inputs.',
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         // 2. Database Transaction & Try-Catch
//         DB::beginTransaction();
//         try {
//             // Generate Unique Requisition ID (REQ-YYYYMMDD-XXXX)
//             $lastReq = Requisition::orderBy('id', 'desc')->first();
//             $sequence = $lastReq ? $lastReq->id + 1 : 1;
//             $reqId = 'REQ-' . Carbon::now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

//             $requisition = Requisition::create([
//                 'user_id' => Auth::id() ?? 1,
//                 'req_store_id' => $request->req_store_id,
//                 'send_store_id' => $request->send_store_id,
//                 'status' => 4, // 4 = on-hold
//                 'ip_address' => $request->ip(),
//                 'req_id' => $reqId,
//                 'remarks' => $request->remarks,
//             ]);

//             foreach ($request->items as $item) {
//                 RequisitionItem::create([
//                     'req_details_id' => $requisition->id,
//                     'product_id' => $item['product_id'],
//                     'quantity' => $item['qty'],
//                     'uom' => $item['uom'] ?? null,
//                     'ip_address' => $request->ip()
//                 ]);
//             }

//             DB::commit();

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Requisition generated successfully! ID: ' . $reqId
//             ], 200);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'System Error: ' . $e->getMessage()
//             ], 500);
//         }
//     }

// }