@extends('Offline.layouts.guest')

@section('title', 'Registration Successful - Shoe ERP')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
    <div style="background: var(--brand-light); padding: 48px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); text-align: center; max-width: 500px; width: 100%;">
        
        <div style="font-size: 64px; margin-bottom: 16px;">⏳</div>
        
        <h1 style="font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">
            Account Under Verification
        </h1>
        
        <p style="font-size: 15px; color: var(--text-muted); line-height: 1.6; margin-bottom: 32px;">
            Thank you for registering. Your profile details have been securely submitted to the Nandi Shoe House System system. You will be able to log in once your account is reviewed and approved.
        </p>

        <a href="{{ url('/') }}" style="display: inline-block; padding: 12px 24px; background: var(--bg-base); color: var(--text-main); font-weight: 600; text-decoration: none; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 14px; transition: 0.2s;">
            Return to Homepage
        </a>
    </div>
</div>
@endsection