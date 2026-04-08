<?php

namespace App\Http\Controllers\Offline\Purchased;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Common\CommonController;
use App\Models\Purchased\PurchaseDetails;
use App\Models\Purchased\PurchaseTransactionDetails;
use App\Models\Purchased\PurchasedStock;
use App\Models\Product\Product;
use App\Models\Vendor\VendorMaster;
use App\Models\Unit\Unit;
use Carbon\Carbon;

class PurchasedController extends CommonController
{
    public function index()
    {
        $vendors = VendorMaster::where('is_active', true)->where('is_deleted', false)->get();
        $products = Product::where('is_active', true)->where('is_deleted', false)->get();
        $units = Unit::where('is_active', true)->where('is_deleted', false)->get();

        return view('Offline.Purchased.purchased', compact('vendors', 'products', 'units'));
    }

    // Explicitly validate image sizes before touching the DB
    private function validateImages($request)
    {
        $imageSlots = ['fst', 'sec', 'trd', 'foth', 'fiv'];
        $errors = [];

        foreach ($imageSlots as $slot) {
            $base64 = $request->input("{$slot}_image_base64");
            if ($base64 && strpos($base64, 'data:image') === 0) {
                $sizeInBytes = (strlen($base64) * (3 / 4)) - substr_count(substr($base64, -2), '=');
                $sizeInKb = $sizeInBytes / 1024;
                
                if ($sizeInKb > 70) {
                    $errors["{$slot}_image_base64"] = ["Image size is " . round($sizeInKb, 1) . "KB. It must be below 70KB."];
                }
            }
        }
        return $errors;
    }

    public function store(Request $request)
    {
        // 1. Strict Validation including Unique Challan No
        $validator = Validator::make($request->all(), [
            'vendor_id'    => 'required|integer',
            'challan_no'   => 'required|string|max:120|unique:purchase_details,challan_no,NULL,id',
            'challan_date' => 'required|date',
            'command'      => 'nullable|string',
            'products'     => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity'   => 'required|numeric|min:0.1',
            'products.*.uom'        => 'required|integer',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.mrp'        => 'required|numeric|min:0',
        ], [
            'challan_no.unique' => 'This Challan No already exists in the system.',
            'products.*.product_id.required' => 'Product is required.',
            'products.*.quantity.required' => 'Qty required.',
            'products.*.uom.required' => 'UOM required.',
            'products.*.unit_price.required' => 'Price required.',
            'products.*.mrp.required' => 'MRP required.',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        // 2. Pre-Validate Image Sizes
        $imageErrors = $this->validateImages($request);
        if (!empty($imageErrors)) {
            return response()->json(['status' => 'error', 'message' => 'Please check image sizes.', 'errors' => $imageErrors], 422);
        }

        // 3. Process Images safely
        $imageSlots = ['fst', 'sec', 'trd', 'foth', 'fiv'];
        $uploadedData = [];

        try {
            foreach ($imageSlots as $slot) {
                $base64 = $request->input("{$slot}_image_base64");
                $fileName = $request->input("{$slot}_image_name");
                if ($base64 && strpos($base64, 'data:image') === 0) {
                    $path = $this->uploadBase64Image($base64, 'purchases');
                    if ($path) { $uploadedData[$slot] = ['doc' => $path, 'name' => $fileName]; }
                }
            }
        } catch (\Exception $e) {
            foreach ($uploadedData as $data) { Storage::disk('public')->delete($data['doc']); }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        // 4. Batch Number: NSH-[3-Random-Digits]/[Month]/[MinutesSeconds]
        $randomDigit = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $batchNumber = 'NSH-' . $randomDigit . '/' . Carbon::now()->format('m/is');

        // 5. Database Transaction (Full Rollback on any failure)
        DB::beginTransaction();
        try {
            $challan = PurchaseDetails::create([
                'user_id'      => auth()->id() ?? 1,
                'vendor_id'    => $request->vendor_id,
                'challan_no'   => $request->challan_no,
                'challan_date' => $request->challan_date,
                'command'      => $request->command, // Added Command
                'ip_address'   => $request->ip(),
                'total'        => 0, 
            ]);

            foreach ($uploadedData as $slot => $fileData) {
                $challan->{"{$slot}_image_doc"} = $fileData['doc'];
                $challan->{"{$slot}_image_file_name"} = $fileData['name'];
            }

            $grandTotal = 0;

            foreach ($request->products as $prod) {
                $qty = floatval($prod['quantity']);
                $price = floatval($prod['unit_price']);
                $gstPct = floatval($prod['gst'] ?? 0);
                
                $baseTotal = $qty * $price;
                $gstAmount = $baseTotal * ($gstPct / 100);
                $lineTotal = $baseTotal + $gstAmount;
                $grandTotal += $lineTotal;

                PurchaseTransactionDetails::create([
                    'purchase_details_id' => $challan->id,
                    'product_id'  => $prod['product_id'],
                    'quantity'    => $qty,
                    'uom'         => $prod['uom'],
                    'mrp'         => $prod['mrp'],
                    'unit_price'  => $price,
                    'total_price' => $lineTotal,
                    'batch_no'    => [$batchNumber], // Array cast natively handles this
                    'gst'         => $gstPct,
                    'cgst'        => $gstPct / 2,
                    'sgst'        => $gstPct / 2,
                    'transaction_type' => 1 
                ]);

                $stock = PurchasedStock::firstOrNew(['product_id' => $prod['product_id']]);
                $stock->quantity = ($stock->quantity ?? 0) + $qty;
                $stock->uom = $prod['uom'] ?? $stock->uom;
                $stock->save();
            }

            $challan->update(['total' => $grandTotal]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Purchase Entry Saved Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploadedData as $data) { Storage::disk('public')->delete($data['doc']); }
            return response()->json(['status' => 'error', 'message' => 'Database Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        // Eager load uomRelation to show the unit name in the modal
        $query = PurchaseDetails::with(['vendor', 'transactions.product', 'transactions.uomRelation'])
            ->orderBy('challan_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('challan_date', [$request->start_date, $request->end_date]);
        } else {
            $query->whereDate('challan_date', Carbon::today());
        }

        $challans = $query->get();
        return view('Offline.Purchased.purchased-history', compact('challans'));
    }

    // 3. GODOWN STOCK PAGE
    public function stock()
    {
        $stocks = PurchasedStock::with(['product', 'uomRelation'])
            ->orderBy('quantity', 'desc')
            ->get();
            
        return view('Offline.Purchased.purchased-stock', compact('stocks'));
    }

    // 4. TRANSACTION LEDGER PAGE
    public function ledger(Request $request)
    {
        $query = PurchaseTransactionDetails::with(['purchaseDetails.vendor', 'product', 'uomRelation'])
            ->orderBy('id', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        $rawTransactions = $query->get();

        // FIX: Group by Challan No (purchase_details_id) to eliminate duplicates!
        $transactions = $rawTransactions->groupBy('purchase_details_id');

        return view('Offline.Purchased.purchased-transaction', compact('transactions'));
    }
}