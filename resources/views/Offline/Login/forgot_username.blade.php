@extends('Offline.layouts.guest')
@section('title', 'Forgot Username - Nandi Shoe House ERP')

@section('content')
<div class="login-layout" style="margin: -40px -24px; max-width: none;"> 
    <div class="auth-brand" style="background-image: url('{{ asset('images/shop-bg.jpeg') }}');">
        <div class="brand-content">
            <div class="auth-logo"><div class="auth-logo-icon">NS</div>Nandi Shoe House</div>
        </div>
    </div>

    <div class="auth-form-wrapper">
        <div class="form-header" id="mainHeader">
            <h2>Find My Account</h2>
            <p>Please enter your exact registered details.</p>
        </div>

        <form id="recoverForm">
            <div id="step1" style="margin-bottom: 24px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group"><label class="form-label" style="display:block;">First Name</label><input type="text" id="f_name" class="login-input" required></div>
                    <div class="form-group"><label class="form-label" style="display:block;">Last Name</label><input type="text" id="l_name" class="login-input" required></div>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="display:block;">Mobile Number</label>
                    <input type="text" id="mobile" class="login-input" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:block;">Date of Birth</label>
                    <input type="date" id="dob" class="login-input" required>
                </div>
                <button type="button" id="btnSendOtp" class="btn-login">Verify Account & Send OTP ➔</button>
            </div>

            <div id="step2" style="display: none; padding-top: 24px; border-top: 1px solid var(--border-light); margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:flex; justify-content: space-between;">
                        Enter OTP <span id="timerDisplay" style="color: #ef4444; font-weight: 700;"></span>
                    </label>
                    <div id="otpDisplay" style="font-size: 12px; color: var(--accent); margin-bottom: 8px; font-weight: 600; display: none;"></div>
                    <input type="text" id="otp_input" class="login-input" placeholder="6-Digit OTP" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" id="btnVerifyOtp" class="btn-login" style="flex: 2;">Verify OTP ➔</button>
                    <button type="button" id="btnResendOtp" class="btn-login" style="flex: 1; background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-strong); display: none;">Resend</button>
                </div>
            </div>

            <div id="step3" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 24px; border-radius: 12px; text-align: center;">
                <div style="font-size: 13px; color: #166534; font-weight: 600; margin-bottom: 12px;">Account Found!</div>
                <div style="font-size: 12px; color: #15803d; margin-bottom: 4px;">System Username:</div>
                <div id="res_username" style="font-family: 'JetBrains Mono', monospace; font-size: 18px; font-weight: 700; color: #14532d; margin-bottom: 16px;"></div>
                <div style="font-size: 12px; color: #15803d; margin-bottom: 4px;">Login Email ID:</div>
                <div id="res_email" style="font-family: 'JetBrains Mono', monospace; font-size: 15px; font-weight: 700; color: #14532d;"></div>
            </div>
        </form>
        <div class="auth-footer-links"><a href="{{ route('login') }}" class="auth-link">Back to Login</a></div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Configure the UI elements for the Global OTP Engine
    const otpUiConfig = {
        displayBoxId: 'otpDisplay',
        timerTextId: 'timerDisplay',
        verifyBtnId: 'btnVerifyOtp',
        resendBtnId: 'btnResendOtp',
        inputId: 'otp_input'
    };

    // 2. SEND OTP (Step 1 -> Step 2)
    document.getElementById('btnSendOtp').addEventListener('click', async function() {
        const mobile = document.getElementById('mobile').value;
        const payload = { 
            f_name: document.getElementById('f_name').value, 
            l_name: document.getElementById('l_name').value, 
            mobile: mobile, 
            dob: document.getElementById('dob').value 
        };

        if(!payload.f_name || !payload.mobile || !payload.dob) { toastr.error('Fill required fields'); return; }

        this.innerHTML = 'Checking System...';

        try {
            // First, verify if the user exists in the system
            const verifyRes = await fetch('/api/auth/verify-usr-identity', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            const verifyData = await verifyRes.json();
            if(!verifyRes.ok) throw verifyData;

            // Lock Step 1 and Show Step 2
            document.getElementById('step1').style.opacity = '0.5';
            document.getElementById('step1').style.pointerEvents = 'none'; 
            document.getElementById('step2').style.display = 'block';
            
            // Call the Global Engine to send the OTP
            await window.erpOtpEngine.send(mobile, 'Mobile', otpUiConfig);
            
        } catch (error) {
            toastr.error(error.message);
            this.innerHTML = 'Verify Account & Send OTP ➔';
        }
    });

    // 3. RESEND OTP
    document.getElementById('btnResendOtp').addEventListener('click', async function() {
        const mobile = document.getElementById('mobile').value;
        await window.erpOtpEngine.send(mobile, 'Mobile', otpUiConfig);
    });

    // 4. VERIFY OTP (Step 2 -> Step 3)
    document.getElementById('btnVerifyOtp').addEventListener('click', async function() {
        const mobile = document.getElementById('mobile').value;
        const otp = document.getElementById('otp_input').value;
        if(!otp) { toastr.error('Enter OTP first'); return; }

        this.innerHTML = 'Verifying...';

        // Call the Global Engine to check the OTP
        const isSuccess = await window.erpOtpEngine.verify(mobile, otp);

        if (isSuccess) {
            window.erpOtpEngine.stopTimer('Mobile');
            
            try {
                // If OTP is correct, securely fetch their username/email
                const finalRes = await fetch('/api/auth/recover-username', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ 
                        f_name: document.getElementById('f_name').value, 
                        l_name: document.getElementById('l_name').value, 
                        mobile: mobile, 
                        dob: document.getElementById('dob').value 
                    })
                });
                const finalData = await finalRes.json();
                if(!finalRes.ok) throw finalData;

                // Hide Step 2, Show Step 3
                document.getElementById('step2').style.display = 'none';
                document.getElementById('mainHeader').style.display = 'none';
                document.getElementById('step3').style.display = 'block';
                document.getElementById('res_username').innerText = finalData.username;
                document.getElementById('res_email').innerText = finalData.email;
                toastr.success('Details retrieved successfully');

            } catch (error) {
                toastr.error(error.message || 'Failed to retrieve account details.');
                this.innerHTML = 'Verify OTP ➔';
            }
        } else {
            this.innerHTML = 'Verify OTP ➔';
        }
    });
</script>
@endpush
@endsection