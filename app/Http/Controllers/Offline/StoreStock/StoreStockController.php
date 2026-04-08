<?php

namespace App\Http\Controllers\Offline\StoreStock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Purchased\PurchasedStock;
use App\Models\Purchased\PurchaseTransactionDetails;
use App\Models\StoreStock\StoreStock;
use App\Models\StoreStock\StoreStockDetails;
use App\Models\Store\StoreMaster;
use Illuminate\Support\Str;

class StoreStockController extends Controller
{
    public function index()
    {
        $stores = StoreMaster::where('is_active', true)->where('is_deleted', false)->get();
        
        // Fetch Godown Stock with Quantity > 0
        $godownStocks = PurchasedStock::with(['product', 'uomRelation'])
            ->where('quantity', '>', 0)
            ->get();

        // Get the latest Batch Number for each product
        foreach ($godownStocks as $stock) {
            $latestTx = PurchaseTransactionDetails::where('product_id', $stock->product_id)
                ->where('transaction_type', 1) // Inward Purchases only
                ->whereNotNull('batch_no')
                ->latest()
                ->first();
                
            $stock->latest_batch = $latestTx && $latestTx->batch_no ? implode(', ', $latestTx->batch_no) : 'N/A';
            $stock->purchase_details_id = $latestTx ? $latestTx->purchase_details_id : 1; // Required for OUT tx
        }

        return view('Offline.StoreStock.store-stock', compact('stores', 'godownStocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'     => 'required|integer',
            'product_id'   => 'required|integer',
            'quantity'     => 'required|numeric|min:0.1',
            'unit_price'   => 'required|numeric|min:0',
            'mrp'          => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $qtyToTransfer = floatval($request->quantity);
            
            // 1. Verify Godown Stock
            $godownStock = PurchasedStock::where('product_id', $request->product_id)->first();
            if (!$godownStock || $godownStock->quantity < $qtyToTransfer) {
                return response()->json(['status' => 'error', 'message' => 'Insufficient stock in Godown.'], 400);
            }

            // 2. Decrease Godown Stock
            $godownStock->quantity -= $qtyToTransfer;
            $godownStock->save();

            // 3. Create OUT Transaction in Purchase Ledger
            $gstPct = floatval($request->cgst ?? 0) + floatval($request->sgst ?? 0);
            $totalPrice = ($qtyToTransfer * $request->unit_price) * (1 + ($gstPct / 100));

            PurchaseTransactionDetails::create([
                'purchase_details_id' => $request->purchase_details_id ?? 1, // Reference the original challan
                'store_id'            => $request->store_id, // Target Store
                'product_id'          => $request->product_id,
                'quantity'            => $qtyToTransfer,
                'uom'                 => $godownStock->uom,
                'mrp'                 => $request->mrp,
                'unit_price'          => $request->unit_price,
                'total_price'         => $totalPrice,
                'batch_no'            => [$request->batch_no],
                'gst'                 => $gstPct,
                'cgst'                => $request->cgst ?? 0,
                'sgst'                => $request->sgst ?? 0,
                'is_packet'           => $request->is_packet ?? false,
                'transaction_type'    => 2 // 2 = OUTWARD (Transfer)
            ]);

            // 4. Increase Store Aggregate Stock
            $storeStock = StoreStock::firstOrNew([
                'store_id'   => $request->store_id,
                'product_id' => $request->product_id
            ]);
            $storeStock->quantity = ($storeStock->quantity ?? 0) + $qtyToTransfer;
            $storeStock->uom = $godownStock->uom;
            $storeStock->save();

            // 5. Create IN Transaction in Store Details
            $barcode = 'BAR-' . time() . '-' . mt_rand(1000, 9999); // Auto Generate Barcode

            StoreStockDetails::create([
                'user_id'       => auth()->id() ?? 1,
                'store_id'      => $request->store_id,
                'received_from' => 1, // Godown ID
                'product_id'    => $request->product_id,
                'quantity'      => $qtyToTransfer,
                'uom'           => $godownStock->uom,
                'mrp'           => $request->mrp,
                'unit_price'    => $request->unit_price,
                'total_price'   => $totalPrice,
                'batch_no'      => [$request->batch_no],
                'barcode_no'    => $barcode,
                'no_of_pack'    => $request->no_of_pack ?? 0,
                'each_pack_quantity' => $request->each_pack_quantity ?? null,
                'gst'           => $gstPct,
                'cgst'          => $request->cgst ?? 0,
                'sgst'          => $request->sgst ?? 0,
                'is_packet'     => $request->is_packet ?? false,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Stock Transferred to Store Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }
}