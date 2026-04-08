<?php

namespace App\Http\Controllers\Offline\OnlineShop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\OnlineShop\OnlineShop;
use App\Models\StateMaster;

class OnlineShopController extends Controller
{
    public function index()
    {
        // Eager load relationships so the View Modal shows actual names!
        $shops = OnlineShop::with(['state', 'district', 'block', 'gramPanchayat', 'village', 'postOffice', 'municipality', 'ward'])
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        $states = StateMaster::where('is_active', true)->get();

        $shops->map(function ($s) {
            $s->encrypted_id = Crypt::encryptString($s->id);
            return $s;
        });

        return view('Offline.OnlineShop.online_shop_reg', compact('shops', 'states'));
    }

    public function store(Request $request)
    {
        // 1. Dynamic Validation Rules matching HTML names exactly
        $rules = [
            'store_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:online_shops,contact_no,NULL,id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:online_shops,email,NULL,id,is_deleted,0'],
            'flat_no'     => 'required|string|max:120',
            'address'     => 'required|string',
            'state_id'    => 'required|integer',
            'district_id' => 'required|integer',
            'area_type'   => 'required|in:rural,urban',
            'pin'         => 'required|digits:6',
        ];

        // Ensure keys exactly match HTML names
        if ($request->area_type === 'rural') {
            $rules['block_id'] = 'required|integer';
            $rules['gp_id']    = 'required|integer'; 
        } else {
            $rules['muni_id']  = 'required|integer';
            $rules['ward_id']  = 'required|integer';
        }

        $messages = [
            'store_name.required' => 'Please fill the Platform/Store Name.',
            'contact_no.unique'   => 'This Contact Number is already registered.',
            'email.unique'        => 'This Email is already registered.',
            'email.regex'         => 'Email must be @gmail.com or @yahoo.com',
            'area_type.required'  => 'Please select Rural or Urban area type.',
            'block_id.required'   => 'Please select a Block.',
            'gp_id.required'      => 'Please select a Gram Panchayat.', 
            'muni_id.required'    => 'Please select a Municipality.',   
            'ward_id.required'    => 'Please select a Ward.',
            'pin.digits'          => 'PIN must be exactly 6 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Please fix the validation errors below.', 
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // $data = $request->all();
            $data = $request->except('area_type');
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_deleted'] = false;
            
            // Map request data directly to db columns
            $data['block_id'] = $request->area_type === 'rural' ? $request->block_id : null;
            $data['gp_id']    = $request->area_type === 'rural' ? $request->gp_id : null;
            $data['vill_id']  = $request->area_type === 'rural' ? $request->vill_id : null;
            $data['post_id']  = $request->area_type === 'rural' ? $request->post_id : null;
            
            $data['muni_id']  = $request->area_type === 'urban' ? $request->muni_id : null;
            $data['ward_id']  = $request->area_type === 'urban' ? $request->ward_id : null;

            OnlineShop::create($data);
            
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Online Platform added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $rules = [
            'store_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:online_shops,contact_no,' . $id . ',id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:online_shops,email,' . $id . ',id,is_deleted,0'],
            'flat_no'     => 'required|string|max:120',
            'address'     => 'required|string',
            'state_id'    => 'required|integer',
            'district_id' => 'required|integer',
            'area_type'   => 'required|in:rural,urban',
            'pin'         => 'required|digits:6',
        ];

        if ($request->area_type === 'rural') {
            $rules['block_id'] = 'required|integer';
            $rules['gp_id']    = 'required|integer';
        } else {
            $rules['muni_id']  = 'required|integer';
            $rules['ward_id']  = 'required|integer';
        }

        // Same messages as store()
        $messages = [
            'store_name.required' => 'Please fill the Platform/Store Name.',
            'contact_no.unique'   => 'This Contact Number is already registered.',
            'email.unique'        => 'This Email is already registered.',
            'email.regex'         => 'Email must be @gmail.com or @yahoo.com',
            'area_type.required'  => 'Please select Rural or Urban area type.',
            'block_id.required'   => 'Please select a Block.',
            'gp_id.required'      => 'Please select a Gram Panchayat.', 
            'muni_id.required'    => 'Please select a Municipality.',   
            'ward_id.required'    => 'Please select a Ward.',
            'pin.digits'          => 'PIN must be exactly 6 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Please fix the validation errors below.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $shop = OnlineShop::findOrFail($id);
            // $data = $request->all();
            $data = $request->except('area_type');
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

            // Strict Nulling based on area_type
            $data['block_id'] = $request->area_type === 'rural' ? $request->block_id : null;
            $data['gp_id']    = $request->area_type === 'rural' ? $request->gp_id : null;
            $data['vill_id']  = $request->area_type === 'rural' ? $request->vill_id : null;
            $data['post_id']  = $request->area_type === 'rural' ? $request->post_id : null;
            
            $data['muni_id']  = $request->area_type === 'urban' ? $request->muni_id : null;
            $data['ward_id']  = $request->area_type === 'urban' ? $request->ward_id : null;

            $shop->update($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Online Platform updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        DB::beginTransaction();
        try {
            $id = Crypt::decryptString($encrypted_id);
            OnlineShop::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Platform deleted securely.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}