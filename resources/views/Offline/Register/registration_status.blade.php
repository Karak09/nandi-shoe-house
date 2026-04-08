@extends('Offline.layouts.guest')
@section('title', 'Registration Status - Nandi Shoe House ERP')

@push('styles')
<style>
    /* Beautiful Status Cards */
    .status-card { text-align:center; padding:32px 24px; background:#fff; border-radius:12px; border:1px solid var(--border-light); margin-top:16px; box-shadow:0 4px 6px rgba(0,0,0,0.05); }
    .status-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px auto; }
    
    .status-approved .status-icon { background: #dcfce7; color: #10b981; }
    .status-approved .status-title { color: #10b981; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    
    .status-pending .status-icon { background: #fef3c7; color: #f59e0b; }
    .status-pending .status-title { color: #f59e0b; font-size: 20px; font-weight: 700; margin-bottom: 8px; }
    
    .status-desc { color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5; }
</style>
@endpush

@section('content')
<div class="login-layout" style="margin: -40px -24px; max-width: none;"> 
    <div class="auth-brand" style="background-image: url('{{ asset('images/shop-bg.jpeg') }}');">
        <div class="brand-content">
            <div class="auth-logo"><div class="auth-logo-icon">NS</div>Nandi Shoe House</div>
        </div>
    </div>

    <div class="auth-form-wrapper" style="overflow-y: auto; max-height: 100vh; padding-top: 40px; padding-bottom: 40px;">
        <div class="form-header" id="mainHeader">
            <h2>Registration Status</h2>
            <p>Verify if your employee account has been approved.</p>
        </div>

        <form id="statusForm">
            
            <div id="step1" style="margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="display:block;">Mobile No, Email, or App No</label>
                    <input type="text" id="identity" class="login-input" placeholder="Enter details..." required autofocus>
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
                
                <div id="cardApproved" class="status-card status-approved" style="display:none;">
                    <div class="status-icon">✓</div>
                    <div class="status-title">Access Granted!</div>
                    <div class="status-desc">Your account has been fully verified and approved by the Super Admin. You may now log in to the ERP.</div>
                    <a href="{{ route('login') }}" class="btn-login" style="text-decoration:none; display:inline-block; width:100%;">Proceed to Login</a>
                </div>

                <div id="cardPending" class="status-card status-pending" style="display:none;">
                    <div class="status-icon">⏳</div>
                    <div class="status-title">Verification Pending</div>
                    <div class="status-desc">Your application is currently under review by the Super Admin. You will be notified once approved.</div>
                    <a href="{{ route('login') }}" class="btn-login" style="text-decoration:none; display:inline-block; width:100%; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1;">Return to Login</a>
                </div>

            </div>
        </form>

        <div class="auth-footer-links">
            <a href="{{ route('login') }}" class="auth-link">← Back to Login</a>
        </div>
    </div>
</div>

<script>
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

            document.getElementById('otpDisplay').innerText = "OTP: " + data.demo_otp;
            document.getElementById('otpDisplay').style.display = 'block';
            toastr.info('OTP Sent!');
            startTimer(); 
        } catch (error) {
            toastr.error(error.message); 
        }
    }

    // Step 1: Send OTP
    document.getElementById('btnSendOtp').addEventListener('click', async function() {
        const identity = document.getElementById('identity').value;
        if(!identity) { toastr.error('Please enter your details'); return; }

        this.innerHTML = 'Verifying Account...';

        try {
            const res = await fetch('/api/auth/verify-status-identity', {
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

    // Step 2: Resend OTP
    document.getElementById('btnResendOtp').addEventListener('click', async function() {
        document.getElementById('otp_input').value = ''; 
        await triggerOtpAPI(document.getElementById('identity').value);
    });

    // Step 3: Verify OTP & Show Status Result
    document.getElementById('btnVerifyOtp').addEventListener('click', async function() {
        const identity = document.getElementById('identity').value;
        const otp = document.getElementById('otp_input').value;
        if(!otp) { toastr.error('Enter OTP first'); return; }

        this.innerHTML = 'Verifying...';

        try {
            // Verify OTP
            const otpRes = await fetch('/api/auth/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity, otp: otp })
            });
            const otpData = await otpRes.json();
            if(!otpRes.ok) throw otpData;

            // Clear inputs and fetch Status
            clearInterval(countdownInterval);
            document.getElementById('otp_input').readOnly = true;
            document.getElementById('otp_input').style.background = '#e2e8f0';
            document.getElementById('timerDisplay').innerText = '';
            document.getElementById('otpDisplay').style.display = 'none';
            this.style.display = 'none'; // Hide the Verify button

            const statusRes = await fetch('/api/auth/get-registration-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ identity: identity })
            });
            const statusData = await statusRes.json();
            if(!statusRes.ok) throw statusData;

            // HIDE THE MAIN HEADER AND SHOW STEP 3 Result
            document.getElementById('mainHeader').style.display = 'none';
            document.getElementById('step3').style.display = 'block';
            
            if(statusData.status === 'approved') {
                document.getElementById('cardApproved').style.display = 'block';
            } else {
                document.getElementById('cardPending').style.display = 'block';
            }

        } catch (error) {
            toastr.error(error.message);
            this.innerHTML = 'Verify OTP ➔';
        }
    });
</script>
@endsection