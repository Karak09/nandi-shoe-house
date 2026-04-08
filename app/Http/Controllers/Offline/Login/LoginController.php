<?php

namespace App\Http\Controllers\Offline\Login;
use App\Http\Controllers\Common\CommonController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends CommonController
{
    public function show()
    {
        return view('Offline.Login.login');
    }

    public function showForgotPassword()
    {
        return view('Offline.Login.forgot_password');
    }

    public function showForgotUsername() 
    { 
        return view('Offline.Login.forgot_username'); 
    }

    public function showRegistrationStatus() 
    { 
        return view('Offline.Register.registration_status'); 
    }
}