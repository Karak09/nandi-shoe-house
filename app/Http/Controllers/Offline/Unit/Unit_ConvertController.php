<?php

namespace App\Http\Controllers\Offline\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Unit\Unit_Convert;
use App\Models\Unit\Unit;
use App\Models\Product\Product; 

class Unit_ConvertController extends Controller
{
    public function index()
    {
        $conversions = Unit_Convert::with(['fromUnit', 'toUnit'])
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        $units = Unit::where('is_active', true)->where('is_deleted', false)->get();

        $conversions->map(function ($c) {
            $c->encrypted_id = Crypt::encryptString($c->id);
            return $c;
        });
        
        return view('Offline.Unit.unit_convert', compact('conversions', 'units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:120',
            'from_unit'    => 'required|integer',
            'to_unit'      => 'required|integer|different:from_unit', 
            'unit_factor'  => 'required|numeric|min:0.001',
            'price_factor' => 'required|numeric|min:0.001',
        ], [
            'to_unit.different' => 'From Unit and To Unit cannot be the same.'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        try {
            $data = $request->except(['is_active', 'packet']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['packet'] = $request->has('packet') ? $request->packet : false;
            
            Unit_Convert::create($data);
            return response()->json(['status' => 'success', 'message' => 'Conversion rule added!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Save failed.'], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:120',
            'from_unit'    => 'required|integer',
            'to_unit'      => 'required|integer|different:from_unit',
            'unit_factor'  => 'required|numeric|min:0.001',
            'price_factor' => 'required|numeric|min:0.001',
        ], [
            'to_unit.different' => 'From Unit and To Unit cannot be the same.'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        try {
            $data = $request->except(['is_active', 'packet']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['packet'] = $request->has('packet') ? $request->packet : false;
            
            Unit_Convert::findOrFail($id)->update($data);
            return response()->json(['status' => 'success', 'message' => 'Rule updated!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);

            // --- DELETE PROTECTION ---
            // Check if this Conversion Rule is used in Products table
            $isUsed = Product::where('uom', $id)->where('is_deleted', false)->exists();

            if ($isUsed) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot delete! This conversion rule is assigned to active Products.'
                ], 403);
            }
            // -------------------------

            Unit_Convert::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            return response()->json(['status' => 'success', 'message' => 'Rule deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}