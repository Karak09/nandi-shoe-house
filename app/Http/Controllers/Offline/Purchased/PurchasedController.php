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
    // --- Prepare all Data PAGE ---
    public function index()
    {
        $vendors = VendorMaster::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        $products = Product::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        $units = Unit::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        return view('Offline.Purchased.purchased', compact('vendors', 'products', 'units'));
    }

    // --- image size & other validate PAGE ---
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

    // --- Product PurchasePAGE ---
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
            'products.*.quantity'   => 'required|integer|min:1',
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
                'transaction_type' => 1 
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

    // --- Product Purchase History PAGE ---
    public function history(Request $request)
    {
        $query = PurchaseDetails::with(['vendor', 'transactions.product', 'transactions.uomRelation'])
            ->where('transaction_type', 1)
            ->orderBy('challan_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('challan_date', [$request->start_date, $request->end_date]);
        } else {
            $query->whereDate('challan_date', Carbon::today());
        }

        $challans = $query->get();

        $challans->map(function ($challan) {
            $challan->enc_id = $this->encryptData((string) $challan->id);
            $challan->enc_product_id = $this->encryptData((string) $challan->id);
            return $challan;
        });

        return view('Offline.Purchased.purchased-history', compact('challans'));
    }

    // --- Product Purchase In & Out PAGE ---
    public function productHistory($enc_product_id)
    {
        // 1. Decrypt ID securely
        $product_id = (int) $this->decryptData($enc_product_id);

        if (!$product_id) {
            abort(404, 'Invalid Link Data');
        }

        // 2. Fetch Transactions from Godown Ledger (PurchaseTransactionDetails)
        $details = \App\Models\Purchased\PurchaseTransactionDetails::with(['product', 'uomRelation', 'purchaseDetails'])
            ->where('product_id', $product_id)
            ->orderBy('created_at', 'asc') // Ascending to calculate math perfectly
            ->get();

        // 3. Fetch Image
        $imageRecord = \Illuminate\Support\Facades\DB::table('product_images')->where('product_id', $product_id)->first();
        $imageUrl = ($imageRecord && $imageRecord->fst_image_doc) ? asset('storage/' . $imageRecord->fst_image_doc) : null;

        $totalIn = 0;
        $totalOut = 0;
        $runningStock = 0;

        // 4. Calculate Running Stock (1 = IN/Purchased, 2 = OUT/Transferred to Store)
        foreach($details as $detail) {
            $qty = (float) $detail->quantity;
            
            if($detail->transaction_type == 1) { 
                $totalIn += $qty;
                $runningStock += $qty;
                $detail->in_qty = $qty;
                $detail->out_qty = 0;
            } else { 
                $totalOut += $qty;
                $runningStock -= $qty;
                $detail->in_qty = 0;
                $detail->out_qty = $qty;
            }
            $detail->running_stock = $runningStock;
        }

        $available = $totalIn - $totalOut;

        // 5. Sort descending so newest is at the top
        $details = $details->sortByDesc('created_at')->values();

        return view('Offline.Purchased.godown_product_history', compact('details', 'totalIn', 'totalOut', 'available', 'imageUrl'));
    }

    // --- GODOWN STOCK PAGE ---
    public function stock()
    {
        $stocks = PurchasedStock::with(['product', 'uomRelation'])
            ->orderBy('quantity', 'desc')
            ->get();
            
        $stocks->map(function ($stock) {
            $stock->enc_product_id = $this->encryptData((string) $stock->product_id);
            return $stock;
        });

        return view('Offline.Purchased.purchased-stock', compact('stocks'));
    }

    // --- Product Purchase TRANSACTION LEDGER PAGE ---
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

    // --- Purchase Challan Print PAGE ---
    public function printChallan($encrypted_id)
    {
        $id = (int) $this->decryptData($encrypted_id);
        if (!$id) abort(404, 'Invalid or expired print link.');

        $challan = PurchaseDetails::with(['vendor', 'transactions.product', 'transactions.uomRelation', 'user.details'])
            ->findOrFail($id);

        $summary = [
            'totalQty' => 0, 'subTotal' => 0, 'totalCGST' => 0, 'totalSGST' => 0, 'netAmount' => 0,
        ];

        foreach($challan->transactions as $item) {
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

        return view('Offline.Purchased.purchased-print-challan', compact('challan', 'summary', 'amountInWords'));
    }

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
}