<?php

namespace App\Http\Controllers\Offline\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Unit\Unit;
use App\Models\Unit\Unit_Convert; // Needed for delete protection

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::where('is_deleted', false)->orderBy('id', 'desc')->get();
        
        $units->map(function ($u) {
            $u->encrypted_id = Crypt::encryptString($u->id);
            return $u;
        });

        return view('Offline.Unit.unit', compact('units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:120',
            'keyword' => 'required|string|max:120|unique:unit_masters,keyword,NULL,id,is_deleted,0',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        try {
            $data = $request->only(['name', 'keyword']);
            $data['keyword'] = strtoupper($data['keyword']); 
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            
            Unit::create($data);
            
            return response()->json(['status' => 'success', 'message' => 'Unit added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Save failed.'], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:120',
            'keyword' => 'required|string|max:120|unique:unit_masters,keyword,' . $id . ',id,is_deleted,0',
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        try {
            $data = $request->only(['name', 'keyword']);
            $data['keyword'] = strtoupper($data['keyword']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            
            Unit::findOrFail($id)->update($data);
            
            return response()->json(['status' => 'success', 'message' => 'Unit updated!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
            
            // --- DELETE PROTECTION ---
            // Check if this Unit is used in any Conversion Rule
            $isUsed = Unit_Convert::where('is_deleted', false)
                ->where(function($query) use ($id) {
                    $query->where('from_unit', $id)->orWhere('to_unit', $id);
                })->exists();

            if ($isUsed) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot delete! This Unit is currently being used in a Unit Conversion Rule.'
                ], 403);
            }
            // -------------------------

            Unit::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            return response()->json(['status' => 'success', 'message' => 'Unit deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}