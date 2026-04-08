<?php

namespace App\Http\Controllers\Offline\Register;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Users\User;
use App\Models\Users\UsersDetails;
use App\Models\Users\UserTypeMaster;
use App\Http\Controllers\Common\CommonController;
use App\Models\{StateMaster, DistrictMaster,BlockMaster,GramPanchayatMaster,
                MunicipalityMaster,PostOfficeMaster,VillageMaster,WardMaster};

class RegisterController extends CommonController
{

    public function show()
    {
        $userTypes = UserTypeMaster::where('is_active', true)->get();
        $states = StateMaster::where('is_active', true)->get();
        return view('Offline.Register.register', compact('userTypes', 'states'));
    }

    public function store(Request $request)
    {
        // 1. Dynamic Validation Rules
        $rules = [
            'f_name'       => 'required|string|max:120',
            'l_name'       => 'required|string|max:120',
            'user_name'    => 'required|string|max:120|unique:users_details,user_name',
            'email'        => 'required|email|max:120',
            'mobile'       => 'required|digits:10',
            'user_type_id' => 'required|integer',
            'dob'          => 'required',
            'gender'       => 'required',
            'address'      => 'required|string',
            'state_id'     => 'required|integer',
            'district_id'  => 'required|integer',
            'area_type'    => 'required|in:rural,urban',
            'pin'          => 'required|digits:6',
            // Image is nullable, Proof is required (if you validate base64 here)
        ];

        if ($request->area_type === 'rural') {
            $rules['block_id'] = 'required|integer';
            $rules['gp_id']    = 'required|integer';
            // Village and Post office are optional
            $rules['vill_id']  = 'nullable|integer';
            $rules['post_id']  = 'nullable|integer';
        } else {
            $rules['muni_id']  = 'required|integer';
            $rules['ward_id']  = 'required|integer';
        }

        $messages = [
            'f_name.required'       => 'First Name is required.',
            'l_name.required'       => 'Last Name is required.',
            'user_name.unique'      => 'This Username is already taken.',
            'mobile.digits'         => 'Mobile number must be exactly 10 digits.',
            'area_type.required'    => 'Please select Rural or Urban area type.',
            'block_id.required'     => 'Please select a Block.',
            'gp_id.required'        => 'Please select a Gram Panchayat.',
            'muni_id.required'      => 'Please select a Municipality.',
            'ward_id.required'      => 'Please select a Ward.',
            'pin.digits'            => 'PIN code must be exactly 6 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Please fix the validation errors below.', // Simplified global message
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Check if mobile/email is already registered
        $existingDetail = UsersDetails::where('email', $request->email)
                                      ->orWhere('mobile', $request->mobile)->first();
                                      
        if ($existingDetail) {
            if ($existingDetail->is_active == false && $existingDetail->is_deleted == true) {
                return response()->json(['status' => 'error', 'message' => 'Account blocked by Super Admin.'], 403);
            }
            if ($existingDetail->verify_status_id == 2) {
                return response()->json(['status' => 'error', 'message' => 'Account is already pending verification.'], 409);
            }
            if ($existingDetail->verify_status_id == 3) {
                User::where('user_details_id', $existingDetail->id)->delete();
                $existingDetail->delete();
            } else {
                return response()->json(['status' => 'error', 'message' => 'An account with this email/mobile already exists.'], 409);
            }
        }

        DB::beginTransaction();
        try {
            $commonCtrl = app(CommonController::class);
            
            // 3. Upload Images
            $imageDocPath = $commonCtrl->uploadBase64Image($request->image_doc_base64, 'uploads/users/profile');
            $proofDocPath = $commonCtrl->uploadBase64Image($request->proof_doc_base64, 'uploads/users/documents');
            
            // 4. Insert into users_details mapping Rural/Urban fields safely
            $userDetail = UsersDetails::create([
                'f_name'           => $request->f_name,
                'l_name'           => $request->l_name,
                'user_name'        => $request->user_name,
                'dob'              => $request->dob,
                'mobile'           => $request->mobile,
                'email'            => $request->email,
                'gender'           => $request->gender,
                'address'          => $request->address,
                'state_id'         => $request->state_id,
                'district_id'      => $request->district_id,
                // Assign geographic data based on area_type
                'block_id'         => $request->area_type === 'rural' ? $request->block_id : null,
                'gp_id'            => $request->area_type === 'rural' ? $request->gp_id : null,
                'vill_id'          => $request->area_type === 'rural' ? $request->vill_id : null,
                'muni_id'          => $request->area_type === 'urban' ? $request->muni_id : null,
                'ward_id'          => $request->area_type === 'urban' ? $request->ward_id : null,
                'pin'              => $request->pin,
                'image_doc'        => $imageDocPath,
                'image_file_name'  => $request->image_file_name,
                'proof_doc'        => $proofDocPath,
                'proof_file_name'  => $request->proof_file_name,
                'date_of_reg'      => now(),
                'verify_status_id' => 2, 
                'is_active'        => true,
                'is_deleted'       => false,
                'img_upload_ip'    => $request->ip(),
            ]);

            // 5. Insert into users table
            $comPassword = $request->mobile . '@0011';
            User::create([
                'user_details_id' => $userDetail->id,
                'user_type_id'    => $request->user_type_id,
                'username'        => $request->user_name,
                'login_id'        => $request->email,
                'com_password'    => $comPassword,
                'password'        => Hash::make($comPassword),
                'entry_time'      => now(),
                'entry_ip'        => $request->ip(),
                'is_active'       => true,
                'is_deleted'      => false,
            ]);

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Registration submitted successfully!',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
    // public function store(Request $request)
    // {
    //     // 1. Validation for API payload
    //     $validator = Validator::make($request->all(), [
    //         'f_name'       => 'required|string|max:120',
    //         'l_name'       => 'required|string|max:120',
    //         'user_name'    => 'required|string|max:120|unique:users_details,user_name',
    //         'email'        => 'required|email|max:120',
    //         'mobile'       => 'required|digits:10',
    //         'user_type_id' => 'required|integer',
    //         'dob'          => 'required',
    //         'gender'       => 'required',
    //         'address'      => 'required|string',
    //         'state_id'     => 'required|integer',
    //         'district_id'  => 'required|integer',
    //         'block_id'     => 'required|integer',
    //         'pin'          => 'required',
    //     ], [
    //         'f_name.required'       => 'First Name is required.',
    //         'l_name.required'       => 'Last Name is required.',
    //         'user_name.required'    => 'System Username is required.',
    //         'user_name.unique'      => 'This Username is already taken.',
    //         'email.required'        => 'Email is required.',
    //         'mobile.required'       => 'Mobile number is required.',
    //         'mobile.digits'         => 'Mobile number must be exactly 10 digits.',
    //         'user_type_id.required' => 'Please select a User Type.',
    //         'dob'                   => 'Please select a Date Of Birth.',
    //         'gender'                => 'Please select a Gender.',
    //         'address'               => 'Please select a address.',
    //         'state_id'              => 'Please select a Select.',
    //         'district_id'           => 'Please select a Disctric.',
    //         'block_id'              => 'Please select a Block.',
    //         'Pin'                   => 'Please select a Pin.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Please fix the validation errors.',
    //             'errors'  => $validator->errors()
    //         ], 422);
    //     }
    //     // 2. Business Logic: Check if user is blocked or rejected
    //     // FIXED: Added 's' to UsersDetails
    //     $existingDetail = UsersDetails::where('email', $request->email)
    //                                  ->orWhere('mobile', $request->mobile)->first();
    //     if ($existingDetail) {
    //         if ($existingDetail->is_active == false && $existingDetail->is_deleted == true) {
    //             return response()->json(['status' => 'error', 'message' => 'Account blocked by Super Admin.'], 403);
    //         }
    //         if ($existingDetail->verify_status_id == 2) {
    //             return response()->json(['status' => 'error', 'message' => 'Account is already pending verification.'], 409);
    //         }
    //         if ($existingDetail->verify_status_id == 3) {
    //             // Remove old rejected records to allow re-registration
    //             User::where('user_details_id', $existingDetail->id)->delete();
    //             $existingDetail->delete();
    //         } else {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'An account with this email/mobile already exists.'
    //             ], 409);
    //         }
    //     }
    //     DB::beginTransaction();
    //     try {
    //         $commonCtrl = app(CommonController::class);
    //         // 3. Handle Base64 Uploads directly from JSON payload
    //         $imageDocPath = $commonCtrl->uploadBase64Image($request->image_doc_base64, 'uploads/users/profile');
    //         $proofDocPath = $commonCtrl->uploadBase64Image($request->proof_doc_base64, 'uploads/users/documents');
    //         // 4. Insert into users_details
    //         // FIXED: Added 's' to UsersDetails
    //         $userDetail = UsersDetails::create([
    //             'f_name'           => $request->f_name,
    //             'l_name'           => $request->l_name,
    //             'user_name'        => $request->user_name,
    //             'dob'              => $request->dob,
    //             'mobile'           => $request->mobile,
    //             'email'            => $request->email,
    //             'gender'           => $request->gender,
    //             'address'          => $request->address,
    //             'state_id'         => $request->state_id,
    //             'district_id'      => $request->district_id,
    //             'pin'              => $request->pin,
    //             'image_doc'        => $imageDocPath,
    //             'image_file_name'  => $request->image_file_name,
    //             'proof_doc'        => $proofDocPath,
    //             'proof_file_name'  => $request->proof_file_name,
    //             'date_of_reg'      => now(),
    //             'verify_status_id' => 2, // Pending
    //             'is_active'        => true,
    //             'is_deleted'       => false,
    //             'img_upload_ip'    => $request->ip(),
    //         ]);
    //         // 5. Generate Password & Insert into users
    //         $comPassword = $request->mobile . '@0011';
    //         User::create([
    //             'user_details_id' => $userDetail->id,
    //             'user_type_id'    => $request->user_type_id,
    //             'username'        => $request->user_name,
    //             'login_id'        => $request->email,
    //             'com_password'    => $comPassword,
    //             'password'        => Hash::make($comPassword),
    //             'entry_time'      => now(),
    //             'entry_ip'        => $request->ip(),
    //             'is_active'       => true,
    //             'is_deleted'      => false,
    //         ]);
    //         DB::commit();
    //         // 6. Using your custom respond format
    //        return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Registration submitted successfully!',
    //             // 'data'    => [
    //             //     'redirect_url' => route('register.success')
    //             // ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Server Error: ' . $e->getMessage()
    //             ], 500);
    //     }
    // }

    public function success()
    {
        return view('Offline.Register.register_success');
    }

    public function checkUsername(Request $request)
    {
        $username = $request->user_name;
        if (!$username) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a username.'
            ]);
        }
        $exists = UsersDetails::where('user_name', $username)->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This username already exists.'
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'This username is available.'
            ]);
        }
    }
}