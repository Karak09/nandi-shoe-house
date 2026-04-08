<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users\User;
use App\Models\Users\UsersDetails; 
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'login_id' => 'required',
    //         'password' => 'required|string',
    //     ]);

    //     $loginId = $request->login_id;
    //     $password = $request->password;
        
    //     $freezeKey = 'login_freeze_' . $loginId;
    //     $attemptsKey = 'login_attempts_' . $loginId;

    //     if (Cache::has($freezeKey)) {
    //         $expireTime = Cache::get($freezeKey);
    //         $minsLeft = now()->diffInMinutes($expireTime) + 1;
    //         return response()->json(['status' => 'error', 'message' => "Your account is blocked after 3 failed attempts. {$minsLeft} min left."], 429);
    //     }

    //     $user = User::with(['details', 'userType'])
    //         ->where('login_id', $loginId)
    //         ->orWhere('username', $loginId)
    //         ->first();

    //     if (!$user) return response()->json(['status' => 'error', 'message' => 'Invalid ID or password'], 401);

    //     $isValidPassword = Hash::check($password, $user->password) || $password === $user->com_password;

    //     if (!$isValidPassword) {
    //         $attempts = Cache::get($attemptsKey, 0) + 1;
    //         if ($attempts >= 3) {
    //             $unblockTime = now()->addMinutes(60);
    //             Cache::put($freezeKey, $unblockTime, $unblockTime);
    //             Cache::forget($attemptsKey);
    //             return response()->json(['status' => 'error', 'message' => 'Your account is blocked. 60 min left.'], 429);
    //         }
    //         Cache::put($attemptsKey, $attempts, now()->addMinutes(60));
    //         $remaining = 3 - $attempts;
    //         return response()->json(['status' => 'error', 'message' => "Wrong password. You have {$remaining} attempts remaining."], 401);
    //     }

    //     Cache::forget($attemptsKey);

    //     // --- DEVICE LIMIT CHECK ---
    //     $deviceKey = 'active_devices_' . $user->id;
    //     $activeDevices = Cache::get($deviceKey, []);

    //     if (count($activeDevices) >= 2) {
    //         return response()->json([
    //             'status' => 'device_limit',
    //             'message' => 'Maximum device limit reached. Please logout from another device to continue.',
    //             'active_devices' => $activeDevices
    //         ], 409); 
    //     }

    //     if (!$user->is_active || $user->is_deleted) return response()->json(['status' => 'error', 'message' => 'Account blocked or deleted.'], 403);
    //     if ($user->details->verify_status_id != 1) return response()->json(['status' => 'error', 'message' => 'Account is pending Super Admin verification.'], 403);

    //     unset($activeDevices[$deviceIdToKick]);
    //     $token = auth('api')->login($user);
    //     Auth::guard('web')->login($user);
    //     $currentDeviceId = Str::uuid()->toString();
    //     // $newDeviceId = Str::uuid()->toString();

    //     // Store Token alongside Device ID so we can destroy it later!
    //     $activeDevices[$currentDeviceId] = [
    //         'id' => $currentDeviceId,
    //         'ip' => $request->ip(),
    //         'time' => now()->format('d M Y, h:i A'),
    //         'token' => $token
    //     ];
    //     Cache::put($deviceKey, $activeDevices, now()->addHours(24));

    //     // Get the proper URL using the helper function
    //     $dashboardUrl = $this->getDashboardUrl($user);

    //     return response()->json([
    //         'status'       => 'success',
    //         'message'      => 'Login successful!',
    //         'access_token' => $token,
    //         'token_type'   => 'bearer',
    //         'expires_in'   => auth('api')->factory()->getTTL() * 60,
    //         'redirect_url' => $dashboardUrl,
    //         'device_id'    => $deviceId,
    //         'user'         => [
    //             'name' => $user->details->f_name . ' ' . $user->details->l_name,
    //             'role' => $user->userType->u_type,
    //         ]
    //     ], 200);
    // }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required',
            'password' => 'required|string',
        ]);

        $loginId = $request->login_id;
        $password = $request->password;
        
        $freezeKey = 'login_freeze_' . $loginId;
        $attemptsKey = 'login_attempts_' . $loginId;

        if (Cache::has($freezeKey)) {
            $expireTime = Cache::get($freezeKey);
            $minsLeft = now()->diffInMinutes($expireTime) + 1;
            return response()->json(['status' => 'error', 'message' => "Your account is blocked. {$minsLeft} min left."], 429);
        }

        $user = User::with(['details', 'userType'])
            ->where('login_id', $loginId)
            ->orWhere('username', $loginId)
            ->first();

        if (!$user) return response()->json(['status' => 'error', 'message' => 'Invalid ID or password'], 401);

        $isValidPassword = Hash::check($password, $user->password) || $password === $user->com_password;

        if (!$isValidPassword) {
            $attempts = Cache::get($attemptsKey, 0) + 1;
            if ($attempts >= 3) {
                $unblockTime = now()->addMinutes(60);
                Cache::put($freezeKey, $unblockTime, $unblockTime);
                Cache::forget($attemptsKey);
                return response()->json(['status' => 'error', 'message' => 'Your account is blocked. 60 min left.'], 429);
            }
            Cache::put($attemptsKey, $attempts, now()->addMinutes(60));
            $remaining = 3 - $attempts;
            return response()->json(['status' => 'error', 'message' => "Wrong password. You have {$remaining} attempts remaining."], 401);
        }

        Cache::forget($attemptsKey);

        // --- DEVICE LIMIT CHECK ---
        $deviceKey = 'active_devices_' . $user->id;
        $activeDevices = Cache::get($deviceKey, []);

        if (count($activeDevices) >= 2) {
            return response()->json([
                'status' => 'device_limit',
                'message' => 'Maximum device limit reached.',
                'active_devices' => $activeDevices
            ], 409); 
        }

        if (!$user->is_active || $user->is_deleted) return response()->json(['status' => 'error', 'message' => 'Account blocked or deleted.'], 403);
        if ($user->details->verify_status_id != 1) return response()->json(['status' => 'error', 'message' => 'Account is pending Super Admin verification.'], 403);

        // --- LOGIN LOGIC ---
        $token = auth('api')->login($user);
        
        // Sync with Web Session for the redirect
        Auth::guard('web')->login($user);

        // Generate a unique ID for this specific session
        $currentDeviceId = Str::uuid()->toString();

        $activeDevices[$currentDeviceId] = [
            'id' => $currentDeviceId,
            'ip' => $request->ip(),
            'time' => now()->format('d M Y, h:i A'),
            'token' => $token
        ];
        
        Cache::put($deviceKey, $activeDevices, now()->addHours(24));

        return response()->json([
            'status'       => 'success',
            'message'      => 'Login successful!',
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'redirect_url' => $this->getDashboardUrl($user),
            'device_id'    => $currentDeviceId, // FIX: Changed from $deviceId to $currentDeviceId
            'user'         => [
                'name' => $user->details->f_name . ' ' . $user->details->l_name,
                'role' => $user->userType->u_type,
            ]
        ], 200);
    }

    // --- FIX: Ensure it returns properly ---
    private function getDashboardUrl($user) {
        $roleName = strtolower($user->userType->u_type);

        if (str_contains($roleName, 'super admin')) {
            return '/offline/dashboard/superadmin';
        } elseif (str_contains($roleName, 'admin')) {
            return '/offline/dashboard/admin';
        } elseif (str_contains($roleName, 'management')) {
            return '/offline/dashboard/management';
        } elseif (str_contains($roleName, 'sales manager')) {
            return '/offline/dashboard/sales';
        } elseif (str_contains($roleName, 'account')) {
            return '/offline/dashboard/account';
        } elseif (str_contains($roleName, 'reporter')) {
            return '/offline/dashboard/reporter';
        }
        
        return '/offline/dashboard/default';
    }

    // public function forceLogoutDevice(Request $request)
    // {
    //     $loginId = $request->login_id;
    //     $password = $request->password; 
    //     $deviceIdToKick = $request->device_id;

    //     $user = User::where('login_id', $loginId)->orWhere('username', $loginId)->first();
    //     if(!$user) return response()->json(['status' => 'error', 'message' => 'User not found'], 404);

    //     $isValidPassword = Hash::check($password, $user->password) || $password === $user->com_password;
    //     if (!$isValidPassword) return response()->json(['status' => 'error', 'message' => 'Authentication failed.'], 401);

    //     $deviceKey = 'active_devices_' . $user->id;
    //     $activeDevices = Cache::get($deviceKey, []);

    //     if(isset($activeDevices[$deviceIdToKick])) {
    //         $oldToken = $activeDevices[$deviceIdToKick]['token'];
            
    //         try { auth('api')->setToken($oldToken)->invalidate(); } catch (\Throwable $e) {}
            
    //         unset($activeDevices[$deviceIdToKick]);

    //         $newToken = auth('api')->login($user);
    //         $newDeviceId = Str::uuid()->toString();
    //         $activeDevices[$newDeviceId] = [
    //             'id' => $newDeviceId,
    //             'ip' => $request->ip(),
    //             'time' => now()->format('d M Y, h:i A'),
    //             'token' => $newToken
    //         ];
    //         Cache::put($deviceKey, $activeDevices, now()->addHours(24));

    //         // Generate URL properly
    //         $dashboardUrl = $this->getDashboardUrl($user);

    //         return response()->json([
    //             'status'       => 'success',
    //             'message'      => 'Previous device logged out! Entering dashboard...',
    //             'access_token' => $newToken,
    //             'device_id'    => $newDeviceId,
    //             'redirect_url' => $dashboardUrl,
    //             'user'         => ['name' => $user->details->f_name . ' ' . $user->details->l_name, 'role' => $user->userType->u_type]
    //         ]);
    //     }

    //     return response()->json(['status' => 'error', 'message' => 'Device not found.'], 400);
    // }

    public function forceLogoutDevice(Request $request)
    {
        $loginId = $request->login_id;
        $password = $request->password; 
        $deviceIdToKick = $request->device_id; // This is the ID from the button click in the Modal

        // 1. Find the user
        $user = User::where('login_id', $loginId)->orWhere('username', $loginId)->first();
        if(!$user) return response()->json(['status' => 'error', 'message' => 'User not found'], 404);

        // 2. Re-verify password for security
        $isValidPassword = Hash::check($password, $user->password) || $password === $user->com_password;
        if (!$isValidPassword) return response()->json(['status' => 'error', 'message' => 'Authentication failed.'], 401);

        $deviceKey = 'active_devices_' . $user->id;
        $activeDevices = Cache::get($deviceKey, []);

        // 3. Remove the old device chosen by the user
        if(isset($activeDevices[$deviceIdToKick])) {
            // 1. Kick the old one
            try { auth('api')->setToken($activeDevices[$deviceIdToKick]['token'])->invalidate(); } catch (\Exception $e) {}
            unset($activeDevices[$deviceIdToKick]);

            // 2. Log in the CURRENT session
            $newToken = auth('api')->login($user);
            \Illuminate\Support\Facades\Auth::guard('web')->login($user); // This allows the redirect to work

            $newId = \Illuminate\Support\Str::uuid()->toString();
            $activeDevices[$newId] = [
                'id' => $newId,
                'ip' => $request->ip(),
                'time' => now()->format('d M Y, h:i A'),
                'token' => $newToken
            ];
            \Illuminate\Support\Facades\Cache::put($deviceKey, $activeDevices, now()->addHours(24));

            return response()->json([
                'status' => 'success',
                'message' => 'Device kicked! Accessing dashboard...',
                'access_token' => $newToken,
                'device_id' => $newId,
                'redirect_url' => $this->getDashboardUrl($user)
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'The selected device is no longer active.'], 400);
    }

    public function logout(Request $request)
    {
        $user = auth('api')->user();
        if ($user) {
            $deviceKey = 'active_devices_' . $user->id;
            $activeDevices = Cache::get($deviceKey, []);
            
            $clientDeviceId = $request->header('X-Device-ID'); 
            if($clientDeviceId && isset($activeDevices[$clientDeviceId])) {
                unset($activeDevices[$clientDeviceId]);
                Cache::put($deviceKey, $activeDevices, now()->addHours(24));
            }
        }

        auth('api')->logout();
        return response()->json(['status' => 'success', 'message' => 'Successfully logged out']);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => auth('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'identity'     => 'required',
            'new_password' => 'required|min:8'
        ]);

        $user = User::where('login_id', $request->identity)
            ->orWhere('username', $request->identity)
            ->orWhereHas('details', function($q) use ($request) {
                $q->where('mobile', $request->identity);
            })->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'No account found.'], 404);
        }

        // --- PREVIOUS PASSWORD CHECK ---
        if (Hash::check($request->new_password, $user->password) || $request->new_password === $user->com_password) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Previous password & current password are the same. Please change your password to a new one.'
            ], 400);
        }

        // $user->com_password = $request->new_password; 
        $user->password = Hash::make($request->new_password);
        $user->pwd_chng_count = $user->pwd_chng_count + 1;
        $user->pwd_chng_ip = $request->ip();
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Password reset successfully!']);
    }

    public function verifyStatusIdentity(Request $request)
    {
        $exists = UsersDetails::where('mobile', $request->identity)
            ->orWhere('email', $request->identity)
            // ->orWhere('application_no', $request->identity) // Assuming this column exists
            ->exists();

        if (!$exists) {
            return response()->json(['status' => 'error', 'message' => 'No application found with these details.'], 404);
        }
        return response()->json(['status' => 'success', 'message' => 'Identity verified.']);
    }

    public function getRegistrationStatus(Request $request)
    {
        $details = UsersDetails::where('mobile', $request->identity)
            ->orWhere('email', $request->identity)
            // ->orWhere('application_no', $request->identity)
            ->first();

        if(!$details) return response()->json(['status' => 'error', 'message' => 'Not found'], 404);

        if($details->verify_status_id == 1) {
            return response()->json(['status' => 'approved', 'title' => 'Approved!', 'message' => 'Your account is approved by admin.']);
        } else {
            return response()->json(['status' => 'pending', 'title' => 'Under Review', 'message' => 'Your account is currently under verification.']);
        }
    }

    public function recoverUsername(Request $request)
    {
        // Find matching details
        $details = UsersDetails::where('f_name', $request->f_name)
            ->where('l_name', $request->l_name)
            ->where('mobile', $request->mobile)
            ->where('dob', $request->dob)
            ->first();

        if (!$details) {
            return response()->json([
                'status' => 'error', 
                'message' => 'No account found matching these exact details.'
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'username' => $details->user_name,
            'email' => $details->email
        ]);
    }

    // 1. Check if identity exists for Password Reset
    public function verifyPasswordIdentity(Request $request)
    {
        $exists = User::where('login_id', $request->identity)
            ->orWhere('username', $request->identity)
            ->orWhereHas('details', function($q) use ($request) {
                $q->where('mobile', $request->identity);
            })->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error', 
                'message' => 'No account found matching those details in our system.'
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Identity verified.'
        ]);
    }

    // 2. Check if identity exists for Username Recovery
    public function verifyUsernameIdentity(Request $request)
    {
        $exists = UsersDetails::where('f_name', $request->f_name)
            ->where('l_name', $request->l_name)
            ->where('mobile', $request->mobile)
            ->where('dob', $request->dob)
            ->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error', 
                'message' => 'No account found matching these exact details.'
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Identity verified.'
        ]);
    }

    public function sendOtp(Request $request)
    {
        $identity = $request->identity; // Mobile or Email
        $freezeKey = 'otp_freeze_' . $identity;
        $attemptsKey = 'otp_attempts_' . $identity;
        $otpKey = 'otp_code_' . $identity;

        // 1. Check if Frozen
        if (Cache::has($freezeKey)) {
            return response()->json(['status' => 'error', 'message' => 'Your account has been frozen for the next 3 hours. Try again after 3 hours.'], 429);
        }

        // 2. Check 3 Attempts Limit
        $attempts = Cache::get($attemptsKey, 0);
        if ($attempts >= 3) {
            Cache::put($freezeKey, true, now()->addHours(3)); // Freeze for 3 hours
            Cache::forget($attemptsKey);
            return response()->json(['status' => 'error', 'message' => 'Your account has been frozen for the next 3 hours. Try again after 3 hours.'], 429);
        }

        // 3. Generate & Store OTP for EXACTLY 45 SECONDS
        $otp = (string) rand(100000, 999999);
        Cache::put($otpKey, $otp, now()->addSeconds(45));

        // 4. Record Attempt (Expires in 1 hour)
        Cache::put($attemptsKey, $attempts + 1, now()->addHour());

        return response()->json([
            'status' => 'success',
            'message' => 'OTP Sent successfully!',
            'demo_otp' => $otp // REMOVE IN PRODUCTION
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $storedOtp = Cache::get('otp_code_' . $request->identity);

        if (!$storedOtp) {
            return response()->json(['status' => 'error', 'message' => 'OTP has expired. Please click resend.'], 400);
        }

        if ($storedOtp !== $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP.'], 400);
        }

        // Success! Clear the OTP and reset their attempts
        Cache::forget('otp_code_' . $request->identity);
        Cache::forget('otp_attempts_' . $request->identity);

        return response()->json(['status' => 'success', 'message' => 'OTP Verified!']);
    }
}


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use App\Models\Users\User;

// class AuthController extends Controller
// {
//     /**
//      * Login User & Return JWT Token with Role-Based Dashboard URL
//      */
//     public function login(Request $request)
//     {
//         $request->validate([
//             'login_id' => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $credentials = $request->only('login_id', 'password');

//         if (!$token = auth('api')->attempt($credentials)) {
//             return response()->json(['status' => 'error', 'message' => 'Invalid email or password'], 401);
//         }

//         $user = auth('api')->user();

//         // Security Check: Ensure account is active, not deleted, and approved
//         if (!$user->is_active || $user->is_deleted) {
//             auth('api')->logout();
//             return response()->json(['status' => 'error', 'message' => 'Account is blocked or deleted.'], 403);
//         }

//         if ($user->details->verify_status_id != 1) { // 1 = Approved
//             auth('api')->logout();
//             return response()->json(['status' => 'error', 'message' => 'Account is still under verification.'], 403);
//         }

//         // Determine Dashboard based on Role ID (Assuming your UserTypeMaster IDs map to these roles)
//         $dashboardUrl = '/dashboard';
//         switch ($user->userType->u_type) {
//             case 'Super Admin':
//                 $dashboardUrl = '/super-admin/dashboard'; // Sees everything
//                 break;
//             case 'Admin':
//                 $dashboardUrl = '/admin/dashboard';
//                 break;
//             case 'Management':
//                 $dashboardUrl = '/management/dashboard';
//                 break;
//             case 'Sales Manager':
//                 $dashboardUrl = '/sales/dashboard';
//                 break;
//             case 'Account':
//                 $dashboardUrl = '/accounts/dashboard';
//                 break;
//             case 'Reporter':
//                 $dashboardUrl = '/reports/dashboard';
//                 break;
//         }

//         return $this->respondWithToken($token, $user, $dashboardUrl);
//     }

//     /**
//      * Get the authenticated User
//      */
//     public function me()
//     {
//         return response()->json([
//             'status' => 'success',
//             'data' => auth('api')->user()->load('details', 'userType')
//         ]);
//     }

//     /**
//      * Log the user out (Invalidate the token)
//      */
//     public function logout()
//     {
//         auth('api')->logout();

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Successfully logged out'
//         ]);
//     }

//     /**
//      * Refresh a token
//      */
//     public function refresh()
//     {
//         return $this->respondWithToken(auth('api')->refresh());
//     }

//     /**
//      * Helper function to format the token response
//      */
//     protected function respondWithToken($token, $user = null, $dashboardUrl = null)
//     {
//         $response = [
//             'status' => 'success',
//             'access_token' => $token,
//             'token_type' => 'bearer',
//             'expires_in' => auth('api')->factory()->getTTL() * 60,
//         ];

//         if ($user) {
//             $response['user'] = [
//                 'id' => $user->id,
//                 'name' => $user->details->f_name . ' ' . $user->details->l_name,
//                 'role' => $user->userType->u_type,
//             ];
//             $response['redirect_url'] = $dashboardUrl;
//         }

//         return response()->json($response);
//     }
// }