<?php

namespace App\Http\Controllers\Offline\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Stores\StoreMaster;
use App\Models\Users\User;
use App\Http\Controllers\Common\CommonController;
use App\Models\{StateMaster, DistrictMaster,BlockMaster,GramPanchayatMaster,
                MunicipalityMaster,PostOfficeMaster,VillageMaster,WardMaster};

class StoreMasterController extends CommonController
{
    public function index()
    {
        $stores = StoreMaster::with(['storeUser.details', 'state', 'district', 'block', 'gramPanchayat', 'village', 'postOffice', 'municipality', 'ward'])
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        $states = StateMaster::where('is_active', true)->get();

        // 1. DEFINE the variable
        $storeUsers = User::where('user_type_id', 3)->get();

        $stores->map(function ($s) {
            $s->encrypted_id = Crypt::encryptString($s->id);
            return $s;
        });

        // 2. PASS the variable to the view inside compact()
        return view('Offline.Store.store_reg', compact('stores', 'states', 'storeUsers'));
    }

    public function getStoreUsers()
    {
        $users = User::with('details')->where('user_type_id', 3)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'full_name' => $user->details ? trim($user->details->f_name . ' ' . $user->details->l_name) : $user->username
            ];
        });
        
        return response()->json($users);
    }

    public function store(Request $request)
    {
        // 1. Dynamic Validation Rules matching HTML names exactly
        $rules = [
            'user_id' => 'required|integer|unique:store_masters,user_id,NULL,id,is_deleted,0',
            'store_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:store_masters,contact_no,NULL,id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:store_masters,email,NULL,id,is_deleted,0'],
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
            'user_id.required' => 'Please select a Store User.',
            'user_id.unique'   => 'This user already has another store.',
            'store_name.required' => 'Please fill the Store Name.',
            'contact_no.unique'   => 'Mobile Number is already assigned.',
            'email.unique'        => 'Email is already assigned.',
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
            $data = $request->except('area_type'); // 🚀 remove before insert
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            
            // Map request data directly to db columns since names are identical now
            StoreMaster::create($data);
            
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Record added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        
        $rules = [
            'user_id' => 'required|integer|unique:store_masters,user_id,' . $id . ',id,is_deleted,0',
            'store_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:store_masters,contact_no,' . $id . ',id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:store_masters,email,' . $id . ',id,is_deleted,0'],
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

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Please fix the validation errors below.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $store = StoreMaster::findOrFail($id);
            // $data = $request->all();
            $data = $request->except('area_type'); // 🚀 remove before update
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

            // Make sure these are null if area type changes during update
            if($request->area_type === 'rural') {
                $data['muni_id'] = null;
                $data['ward_id'] = null;
            } else {
                $data['block_id'] = null;
                $data['gp_id'] = null;
                $data['vill_id'] = null;
                $data['post_id'] = null;
            }

            $store->update($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Updated successfully!']);
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
            StoreMaster::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Deleted securely.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}