@extends('Offline.layouts.guest')
@section('title', 'Forgot Password - Nandi Shoe House ERP')

@section('content')
<div class="login-layout" style="margin: -40px -24px; max-width: none;"> 
    <div class="auth-brand" style="background-image: url('{{ asset('images/shop-bg.jpeg') }}');">
        <div class="brand-content">
            <div class="auth-logo"><div class="auth-logo-icon">NS</div>Nandi Shoe House</div>
            <!-- <div class="brand-messaging">
                <h1>Password Recovery</h1>
                <p>Securely reset your access credentials.</p>
            </div> -->
        </div>
    </div>

    <div class="auth-form-wrapper">
        <div class="form-header" id="mainHeader">
            <h2>Reset Password</h2>
            <p>Verify your identity to create a new password.</p>
        </div>

        <form id="resetPwdForm">
            <div id="step1" style="margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:block;">Email ID, Username, or Mobile Number</label>
                    <input type="text" id="identity" class="login-input" placeholder="Enter details..." required>
                </div>
                <button type="button" id="btnSendOtp" class="btn-login">Verify Account & Send OTP ➔</button>
            </div>

            <div id="step2" style="display: none; padding-top: 24px; border-top: 1px solid var(--border-light); margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:flex; justify-content: space-between;">
                        Enter OTP 
                        <span id="timerDisplay" style="color: #ef4444; font-weight: 700;"></span>
                    </label>
                    <div id="otpDisplay" style="font-size: 12px; color: var(--accent); margin-bottom: 8px; font-weight: 600;"></div>
                    <input type="text" id="otp_input" class="login-input" placeholder="6-Digit OTP" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="button" id="btnVerifyOtp" class="btn-login" style="flex: 2;">Verify OTP ➔</button>
                    <button type="button" id="btnResendOtp" class="btn-login" style="flex: 1; background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-strong); display: none;">Resend</button>
                </div>
            </div>

            <div id="step3" style="display: none; padding-top: 24px; border-top: 1px solid var(--border-light);">
                <div style="font-size: 20px; font-weight: 700; margin-bottom: 24px;">Create New Password</div>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="display:block;">New Password</label>
                    <div class="pwd-field">
                        <input type="password" id="new_password" class="login-input" placeholder="Min 8 characters">
                        <button type="button" class="toggle-pwd" onclick="togglePwd('new_password', this)">Show</button>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:block;">Re-enter Password</label>
                    <div class="pwd-field">
                        <input type="password" id="confirm_password" class="login-input" placeholder="Match new password">
                        <button type="button" class="toggle-pwd" onclick="togglePwd('confirm_password', this)">Show</button>
                    </div>
                </div>
                <button type="submit" id="btnSubmitReset" class="btn-login">Update Password ➔</button>
            </div>
        </form>

        <div class="auth-footer-links">
            Remembered? <a href="{{ route('login') }}" class="auth-link">Back to Login</a>
        </div>
    </div>
</div>

<script>
    // Eye Icon Toggle Logic
    function togglePwd(inputId, btnElement) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btnElement.textContent = 'Hide';
        } else {
            input.type = 'password';
            btnElement.textContent = 'Show';
        }
    }

    let countdownInterval;

    function startTimer() {
        let timeLeft = 45;
        document.getElementById('timerDisplay').innerText = `Time left: ${timeLeft}s`;
        document.getElementById('btnResendOtp').style.display = 'none';
        document.getElementById('btnVerifyOtp').style.display = 'block';
        document.getElementById('otp_input').disabled = false;

        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            timeLeft--;
            document.getElementById('timerDisplay').innerText = `Time left: ${timeLeft}s`;

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('timerDisplay').innerText = 'Expired';
                document.getElementById('btnVerifyOtp').style.display = 'none'; 
                document.getElementById('otp_input').disabled = true; 
                document.getElementById('btnResendOtp').style.display = 'block'; 
            }
        }, 1000);
    }

    async function triggerOtpAPI(identity) {
        try {
            const res = await fetch('/api/auth/send-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity })
            });
            const data = await res.json();
            if(!res.ok) throw data;

            alert("Recovery OTP is: " + data.demo_otp); 
            document.getElementById('otpDisplay').innerText = "OTP: " + data.demo_otp;
            document.getElementById('otpDisplay').style.display = 'block';
            toastr.info('OTP Sent!');
            startTimer(); 
        } catch (error) {
            toastr.error(error.message); // 3-hour freeze message
        }
    }

    document.getElementById('btnSendOtp').addEventListener('click', async function() {
        const identity = document.getElementById('identity').value;
        if(!identity) { toastr.error('Please enter your details'); return; }

        this.innerHTML = 'Verifying Account...';

        try {
            const res = await fetch('/api/auth/verify-pwd-identity', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity })
            });
            const data = await res.json();
            if(!res.ok) throw data;

            document.getElementById('identity').readOnly = true;
            document.getElementById('identity').style.background = '#e2e8f0';
            this.style.display = 'none'; 
            document.getElementById('step2').style.display = 'block'; 
            
            await triggerOtpAPI(identity);
        } catch (error) {
            toastr.error(error.message);
            this.innerHTML = 'Verify Account & Send OTP ➔';
        }
    });

    document.getElementById('btnResendOtp').addEventListener('click', async function() {
        document.getElementById('otp_input').value = ''; 
        await triggerOtpAPI(document.getElementById('identity').value);
    });

    document.getElementById('btnVerifyOtp').addEventListener('click', async function() {
        const identity = document.getElementById('identity').value;
        const otp = document.getElementById('otp_input').value;
        if(!otp) { toastr.error('Enter OTP first'); return; }

        this.innerHTML = 'Verifying...';

        try {
            const otpRes = await fetch('/api/auth/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity, otp: otp })
            });
            const otpData = await otpRes.json();
            if(!otpRes.ok) throw otpData;

            clearInterval(countdownInterval);
            document.getElementById('otp_input').readOnly = true;
            document.getElementById('otp_input').style.background = '#e2e8f0';
            document.getElementById('timerDisplay').innerText = '';
            document.getElementById('otpDisplay').style.display = 'none';
            this.style.display = 'none';
            
            // HIDE THE MAIN HEADER
            document.getElementById('mainHeader').style.display = 'none';
            
            // SHOW STEP 3
            document.getElementById('step3').style.display = 'block';
            toastr.success('Identity Verified! Please enter new password.');

        } catch (error) {
            toastr.error(error.message);
            this.innerHTML = 'Verify OTP ➔';
        }
    });

    document.getElementById('resetPwdForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const newPass = document.getElementById('new_password').value;
        const confPass = document.getElementById('confirm_password').value;

        if(newPass !== confPass) { toastr.error('Passwords do not match!'); return; }
        if(newPass.length < 8) { toastr.error('Password must be at least 8 characters'); return; }

        const btn = document.getElementById('btnSubmitReset');
        btn.innerHTML = 'Updating...';

        try {
            const res = await fetch('/api/auth/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: document.getElementById('identity').value, new_password: newPass })
            });
            const data = await res.json();
            if(!res.ok) throw data;

            toastr.success(data.message);
            setTimeout(() => { window.location.href = '/login'; }, 1500);
        } catch (error) {
            toastr.error(error.message);
            btn.innerHTML = 'Update Password ➔';
        }
    });
</script>
@endsection

<!-- <script>
    let generatedOtp = null;

    document.getElementById('btnSendOtp').addEventListener('click', async function() {
        const identity = document.getElementById('identity').value;
        if(!identity) { toastr.error('Please enter your details'); return; }

        const btn = this;
        btn.innerHTML = 'Verifying Account...';

        try {
            // 1. Verify user exists in the database
            const res = await fetch('/api/auth/verify-pwd-identity', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity })
            });
            const data = await res.json();
            
            if(!res.ok) throw data;

            // 2. If exists, lock the input and show OTP block
            document.getElementById('identity').readOnly = true;
            document.getElementById('identity').style.background = '#e2e8f0';
            btn.style.display = 'none'; // Hide the send button

            generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
            alert("Recovery OTP is: " + generatedOtp); 

            document.getElementById('otpDisplay').innerText = "OTP Sent! Demo Code: " + generatedOtp;
            document.getElementById('step2').style.display = 'block'; // Show block
            toastr.info('OTP Sent to your registered contact!');

        } catch (error) {
            toastr.error(error.message || 'Verification failed');
            btn.innerHTML = 'Verify Account & Send OTP ➔';
        }
    });

    document.getElementById('btnVerifyOtp').addEventListener('click', function() {
        const enteredOtp = document.getElementById('otp_input').value;
        if(enteredOtp === generatedOtp) {
            document.getElementById('otp_input').readOnly = true;
            document.getElementById('otp_input').style.background = '#e2e8f0';
            this.style.display = 'none';

            document.getElementById('step3').style.display = 'block';
            toastr.success('Identity Verified! Please enter new password.');
        } else {
            toastr.error('Invalid OTP');
        }
    });

    // Submit New Password
    document.getElementById('resetPwdForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const newPass = document.getElementById('new_password').value;
        const confPass = document.getElementById('confirm_password').value;

        if(newPass !== confPass) { toastr.error('Passwords do not match!'); return; }
        if(newPass.length < 8) { toastr.error('Password must be at least 8 characters'); return; }

        const btn = document.getElementById('btnSubmitReset');
        btn.innerHTML = 'Updating...';

        try {
            const res = await fetch('/api/auth/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({
                    identity: document.getElementById('identity').value,
                    new_password: newPass
                })
            });
            const data = await res.json();
            if(!res.ok) throw data;

            toastr.success(data.message);
            setTimeout(() => { window.location.href = '/login'; }, 1500);

        } catch (error) {
            toastr.error(error.message || 'Update failed');
            btn.innerHTML = 'Update Password ➔';
        }
    });
</script> -->
