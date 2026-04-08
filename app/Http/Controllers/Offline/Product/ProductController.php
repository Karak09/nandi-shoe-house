<?php

namespace App\Http\Controllers\Offline\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Common\CommonController;
use App\Models\Product\Product;
use App\Models\Product\ProductImage;
use App\Models\Category\Category;
use App\Models\Unit\Unit;

class ProductController extends CommonController
{
    public function index()
    {
        $products = Product::with(['category.parent', 'uomRelation', 'images'])
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        $categories = Category::with('parent')->where('is_active', true)->where('is_deleted', false)->get();
        $units = Unit::where('is_active', true)->where('is_deleted', false)->get();

        $products->map(function ($p) {
            $p->encrypted_id = Crypt::encryptString($p->id);
            return $p;
        });

        return view('Offline.Product.product', compact('products', 'categories', 'units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:150|unique:product_masters,name,NULL,id,is_deleted,0',
            'product_code' => 'required|string|max:150|unique:product_masters,product_code,NULL,id,is_deleted,0',
            'sku'          => 'nullable|string|max:50|unique:product_masters,sku,NULL,id,is_deleted,0',
            'hsn_code'     => 'nullable|string|max:120|unique:product_masters,hsn_code,NULL,id,is_deleted,0',
            'cat_id'       => 'required|integer',
            'pro_size'     => 'required|numeric|min:0',
            'uom'          => 'required|integer',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        $imageSlots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig'];
        $uploadedData = [];
        $imageErrors = [];

        // 1. Process Images First and CATCH SPECIFIC ERRORS
        foreach ($imageSlots as $slot) {
            $base64 = $request->input("{$slot}_image_base64");
            $fileName = $request->input("{$slot}_image_name");
            
            if ($base64 && strpos($base64, 'data:image') === 0) {
                try {
                    // This calls your CommonController!
                    $path = $this->uploadBase64Image($base64, 'products');
                    if ($path) {
                        $uploadedData[$slot] = ['doc' => $path, 'name' => $fileName];
                    }
                } catch (\Exception $e) {
                    // MAGIC: We map the exact CommonController error to the specific image slot!
                    $imageErrors["{$slot}_image_base64"] = [$e->getMessage()];
                }
            }
        }

        // If ANY image failed size/format validation, stop and return the specific errors
        if (!empty($imageErrors)) {
            // Clean up any files that were successfully uploaded before the error hit
            foreach ($uploadedData as $data) {
                Storage::disk('public')->delete($data['doc']);
            }
            // Return 422 so Javascript places it exactly under the box!
            return response()->json([
                'status' => 'error', 
                'message' => 'Please fix the image upload errors.', 
                'errors' => $imageErrors
            ], 422);
        }

        // 2. Save to Database
        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'ben_name', 'product_code', 'product_des', 'sku', 'cat_id', 'uom', 'hsn_code', 'pro_size']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_packet'] = $request->has('is_packet') ? $request->is_packet : false;
            $data['is_deleted'] = false;

            $product = Product::create($data);

            $imageRecord = new ProductImage(['product_id' => $product->id]);
            foreach ($uploadedData as $slot => $fileData) {
                $imageRecord->{"{$slot}_image_doc"} = $fileData['doc'];
                $imageRecord->{"{$slot}_image_file_name"} = $fileData['name'];
            }
            $imageRecord->save();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Product added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploadedData as $data) { Storage::disk('public')->delete($data['doc']); }
            return response()->json(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:150|unique:product_masters,name,' . $id . ',id,is_deleted,0',
            'product_code' => 'required|string|max:150|unique:product_masters,product_code,' . $id . ',id,is_deleted,0',
            'sku'          => 'nullable|string|max:50|unique:product_masters,sku,' . $id . ',id,is_deleted,0',
            'hsn_code'     => 'nullable|string|max:120|unique:product_masters,hsn_code,' . $id . ',id,is_deleted,0',
            'cat_id'       => 'required|integer',
            'pro_size'     => 'required|numeric|min:0',
            'uom'          => 'required|integer',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        $imageSlots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig'];
        $uploadedData = [];
        $imageErrors = [];

        // 1. Process Images First and CATCH SPECIFIC ERRORS
        foreach ($imageSlots as $slot) {
            $base64 = $request->input("{$slot}_image_base64");
            $fileName = $request->input("{$slot}_image_name");
            
            if ($base64 && strpos($base64, 'data:image') === 0) {
                try {
                    $path = $this->uploadBase64Image($base64, 'products');
                    if ($path) { 
                        $uploadedData[$slot] = ['doc' => $path, 'name' => $fileName]; 
                    }
                } catch (\Exception $e) {
                    $imageErrors["{$slot}_image_base64"] = [$e->getMessage()];
                }
            }
        }

        // If ANY image failed, stop and return the specific errors
        if (!empty($imageErrors)) {
            foreach ($uploadedData as $data) { Storage::disk('public')->delete($data['doc']); }
            return response()->json([
                'status' => 'error', 
                'message' => 'Please fix the image upload errors.', 
                'errors' => $imageErrors
            ], 422);
        }

        // 2. Database Update
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $data = $request->only(['name', 'ben_name', 'product_code', 'product_des', 'sku', 'cat_id', 'uom', 'hsn_code', 'pro_size']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_packet'] = $request->has('is_packet') ? $request->is_packet : false;

            $product->update($data);

            $imageRecord = ProductImage::firstOrCreate(['product_id' => $id]);
            foreach ($uploadedData as $slot => $fileData) {
                $imageRecord->{"{$slot}_image_doc"} = $fileData['doc'];
                $imageRecord->{"{$slot}_image_file_name"} = $fileData['name'];
            }
            $imageRecord->save();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploadedData as $data) { Storage::disk('public')->delete($data['doc']); }
            return response()->json(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
            Product::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            ProductImage::where('product_id', $id)->update(['is_deleted' => true, 'is_active' => false]);
            return response()->json(['status' => 'success', 'message' => 'Product deleted securely.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}