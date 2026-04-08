<?php

namespace App\Http\Controllers\Offline\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Vendor\VendorMaster;
use App\Models\{StateMaster, DistrictMaster,BlockMaster,GramPanchayatMaster,
                MunicipalityMaster,PostOfficeMaster,VillageMaster,WardMaster};

class VendorController extends Controller
{
    public function index()
    {
        // Eager load relationships so the View Modal shows actual names!
        $vendors = VendorMaster::with(['state', 'district', 'block', 'gramPanchayat', 'village', 'postOffice', 'municipality', 'ward'])
            ->where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();
            
        $states = StateMaster::where('is_active', true)->get();

        $vendors->map(function ($vendor) {
            $vendor->encrypted_id = Crypt::encryptString($vendor->id);
            return $vendor;
        });

        return view('Offline.Vendor.vendor_reg', compact('vendors', 'states'));
    }

    public function store(Request $request)
    {
        $rules = [
            'vendor_name' => 'required|string|max:120',
            'owner_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:vendor_masters,contact_no,NULL,id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:vendor_masters,email,NULL,id,is_deleted,0'],
            'flat_no'     => 'required|string|max:120',
            'location'    => 'required|string|max:150',
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

        $messages = [
            'vendor_name.required' => 'Please fill the Vendor Company Name.',
            'owner_name.required'  => 'Please provide the Owner name.',
            'contact_no.unique'    => 'Mobile Number is already assigned.',
            'email.unique'         => 'Email is already assigned.',
            'area_type.required'   => 'Please select Rural or Urban area type.',
            'block_id.required'    => 'Please select a Block.',
            'gp_id.required'       => 'Please select a Gram Panchayat.', 
            'muni_id.required'     => 'Please select a Municipality.',   
            'ward_id.required'     => 'Please select a Ward.',
            'pin.digits'           => 'PIN must be exactly 6 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Please fix the validation errors below.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // $data = $request->all();
            $data = $request->except('area_type'); // 🚀 remove before insert
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_deleted'] = false; 

            $data['block_id'] = $request->area_type === 'rural' ? $request->block_id : null;
            $data['gp_id']    = $request->area_type === 'rural' ? $request->gp_id : null;
            $data['vill_id']  = $request->area_type === 'rural' ? $request->vill_id : null;
            $data['post_id']  = $request->area_type === 'rural' ? $request->post_id : null;
            
            $data['muni_id']  = $request->area_type === 'urban' ? $request->muni_id : null;
            $data['ward_id']  = $request->area_type === 'urban' ? $request->ward_id : null;

            VendorMaster::create($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Vendor added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        // FIX: Must decrypt ID FIRST before running validation!
        $id = Crypt::decryptString($encrypted_id);
        
        $rules = [
            'vendor_name' => 'required|string|max:120',
            'owner_name'  => 'required|string|max:120',
            'contact_no'  => 'required|digits:10|unique:vendor_masters,contact_no,' . $id . ',id,is_deleted,0',
            'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:vendor_masters,email,' . $id . ',id,is_deleted,0'],
            'flat_no'     => 'required|string|max:120',
            'location'    => 'required|string|max:150',
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
            $vendor = VendorMaster::findOrFail($id);
            // $data = $request->all();
            $data = $request->except('area_type');
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

            $data['block_id'] = $request->area_type === 'rural' ? $request->block_id : null;
            $data['gp_id']    = $request->area_type === 'rural' ? $request->gp_id : null;
            $data['vill_id']  = $request->area_type === 'rural' ? $request->vill_id : null;
            $data['post_id']  = $request->area_type === 'rural' ? $request->post_id : null;
            
            $data['muni_id']  = $request->area_type === 'urban' ? $request->muni_id : null;
            $data['ward_id']  = $request->area_type === 'urban' ? $request->ward_id : null;

            $vendor->update($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Vendor updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Update failed. Invalid Data.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        DB::beginTransaction();
        try {
            $id = Crypt::decryptString($encrypted_id);
            VendorMaster::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Vendor deleted securely.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}

// class VendorController extends Controller
// {
//     public function index()
//     {
//         $vendors = VendorMaster::where('is_deleted', false)->orderBy('id', 'desc')->get();
//         $states = StateMaster::where('is_active', true)->get();

//         // Map over vendors to encrypt their IDs
//         $vendors->map(function ($vendor) {
//             $vendor->encrypted_id = Crypt::encryptString($vendor->id);
//             return $vendor;
//         });

//         return view('Offline.Vendor.vendor_reg', compact('vendors', 'states'));
//     }

//     public function store(Request $request)
//     {
//         // 1. Manual Validation with Custom Messages
//         $validator = Validator::make($request->all(), [
//             'vendor_name' => 'required|string|max:120',
//             'owner_name'  => 'required|string|max:120',
//             'contact_no'  => 'required|digits:10|unique:vendor_masters,contact_no,NULL,id,is_deleted,0',
//             'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:vendor_masters,email,NULL,id,is_deleted,0'],
//             'flat_no'     => 'required|string|max:120',
//             'location'    => 'required|string|max:150',
//             'address'     => 'required|string',
//             'state_id'    => 'required|integer',
//             'district_id' => 'required|integer',
//             'block_id'    => 'required|integer',
//             'pin'         => 'required|digits:6',
//         ], [
//             'vendor_name.required' => 'Please fill the Vendor Company Name.',
//             'owner_name.required'  => 'Please provide the Owner / Contact Person name.',
//             'contact_no.required'  => 'Contact number is mandatory.',
//             'contact_no.digits'    => 'Contact number must be exactly 10 digits.',
//             'email.required'       => 'Please provide an email address.',
//             'email.regex'          => 'Email must end with @gmail.com or @yahoo.com.',
//             'state_id.required'    => 'Please select a State.',
//             'district_id.required' => 'Please select a District.',
//             'block_id.required'    => 'Please select a Block.',
//             'pin.required'         => 'PIN code is mandatory.',
//             'pin.digits'           => 'PIN code must be exactly 6 digits.',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'Validation failed.', 
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         // 2. DB Transaction
//         DB::beginTransaction();
//         try {
//             $data = $request->all();
//             $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
//             $data['is_deleted'] = false; // explicitly set

//             VendorMaster::create($data);
            
//             DB::commit();
//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Vendor added successfully!'
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'Failed to save vendor. ' . $e->getMessage()
//             ], 500);
//         }
//     }

//     public function update(Request $request, $encrypted_id)
//     {
//         // 1. Manual Validation with Custom Messages
//         $validator = Validator::make($request->all(), [
//             'vendor_name' => 'required|string|max:120',
//             'owner_name'  => 'required|string|max:120',
//             'contact_no'  => 'required|digits:10|unique:vendor_masters,contact_no,' . $id . ',id,is_deleted,0',
//             'email'       => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com)$/i', 'unique:vendor_masters,email,' . $id . ',id,is_deleted,0'],
//             'flat_no'     => 'required|string|max:120',
//             'location'    => 'required|string|max:150',
//             'address'     => 'required|string',
//             'state_id'    => 'required|integer',
//             'district_id' => 'required|integer',
//             'block_id'    => 'required|integer',
//             'pin'         => 'required|digits:6',
//         ], [
//             // ... (Same custom messages as above) ...
//             'vendor_name.required' => 'Please fill the Vendor Company Name.',
//             'contact_no.digits'    => 'Contact number must be exactly 10 digits.',
//             'email.regex'          => 'Email must end with @gmail.com or @yahoo.com.',
//             'pin.digits'           => 'PIN code must be exactly 6 digits.',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'Validation failed.', 
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         // 2. DB Transaction
//         DB::beginTransaction();
//         try {
//             $id = Crypt::decryptString($encrypted_id);
//             $vendor = VendorMaster::findOrFail($id);
            
//             $data = $request->all();
//             $data['is_active'] = $request->has('is_active') ? $request->is_active : false;

//             $vendor->update($data);
            
//             DB::commit();
//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Vendor updated successfully!'
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'Update failed. Invalid Data.'
//             ], 500);
//         }
//     }

//     public function destroy($encrypted_id)
//     {
//         DB::beginTransaction();
//         try {
//             $id = Crypt::decryptString($encrypted_id);
//             $vendor = VendorMaster::findOrFail($id);
            
//             // Soft Delete ONLY
//             $vendor->update(['is_deleted' => true, 'is_active' => false]);
            
//             DB::commit();
//             return response()->json([
//                 'status' => 'success', 
//                 'message' => 'Vendor deleted securely.'
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json([
//                 'status' => 'error', 
//                 'message' => 'Deletion failed.'
//             ], 500);
//         }
//     }
// }