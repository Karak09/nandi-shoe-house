<?php

namespace App\Http\Controllers\Offline\Price;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\PriceMaster\PriceMaster;
use App\Models\Product\Product;

class PriceController extends CommonController
{
    public function index()
    {
        $prices = PriceMaster::with('product')
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        // Get active products for the dropdown
        $products = Product::with('uomRelation')->where('is_active', true)->where('is_deleted', false)->get();

        // Track which products already have a price configured
        $existingPriceProductIds = PriceMaster::where('is_deleted', false)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $prices->map(function ($p) {
            $p->encrypted_id = Crypt::encryptString($p->id);
            return $p;
        });

        return view('Offline.Price.price', compact('prices', 'products', 'existingPriceProductIds'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|integer',
            'pro_mrp_price'  => 'required|numeric|min:0',
            'pro_sale_price' => 'required|numeric|min:0',
            'pro_online'     => 'nullable|numeric|min:0',
            'pro_size'       => 'nullable|numeric|min:0',
            'pro_unit'       => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        // Prevent duplicate pricing for same product
        if (PriceMaster::where('product_id', $request->product_id)->where('is_deleted', false)->exists()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['product_id' => ['This product already has a price configuration. You can edit the existing one.']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['is_active']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_deleted'] = false;
            
            // Total GST is just CGST + SGST
            $data['gst_rate'] = floatval($request->cgst_rate ?? 0) + floatval($request->sgst_rate ?? 0);

            PriceMaster::create($data); 
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Price configuration added!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $validator = Validator::make($request->all(), [
            'product_id'     => 'required|integer',
            'pro_mrp_price'  => 'required|numeric|min:0',
            'pro_sale_price' => 'required|numeric|min:0',
            'pro_online'     => 'nullable|numeric|min:0',
            'pro_size'       => 'nullable|numeric|min:0',
            'pro_unit'       => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        // Prevent duplicate pricing — exclude current record
        if (PriceMaster::where('product_id', $request->product_id)->where('id', '!=', $id)->where('is_deleted', false)->exists()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['product_id' => ['This product already has a price configuration by another record.']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $price = PriceMaster::findOrFail($id);
            $data = $request->except(['is_active']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['gst_rate'] = floatval($request->cgst_rate ?? 0) + floatval($request->sgst_rate ?? 0);

            $price->update($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Price configuration updated!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
            PriceMaster::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            return response()->json(['status' => 'success', 'message' => 'Price deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}