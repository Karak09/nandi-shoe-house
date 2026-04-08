@extends('Offline.layouts.guest')
@section('title', 'User Onboarding - Shoe ERP')

@section('content')
<div class="page-header">
    <h1 class="page-title">Register New System User</h1>
    <p class="page-desc">Complete the employee profile. Submission requires administrator verification before system access is granted.</p>
</div>

<form id="registrationForm" class="onboarding-grid" novalidate>
    
    <div class="profile-card">
        <div class="avatar-upload" id="avatarUploadBox">
            <div class="upload-icon">📷</div>
            <div class="upload-text" id="avatarText">Upload Photo</div>
            <input type="file" id="imageFile" accept=".jpg,.jpeg,.png" style="opacity:0; position:absolute; width:100%; height:100%; cursor:pointer;">
            <input type="hidden" name="image_doc_base64" id="image_doc_base64">
            <input type="hidden" name="image_file_name" id="image_file_name">
        </div>
        
        <div class="profile-title">New User Profile</div>

        <div class="profile-sys-info" style="margin-top: 24px;">
            <div class="sys-lbl" style="margin-bottom:8px;">Upload ID Proof<span>*</span></div>
            <div style="display: flex; gap:10px;">
                <input type="file" id="proofFile" class="form-control" accept=".jpg,.jpeg,.png" required>
                <input type="hidden" name="proof_doc_base64" id="proof_doc_base64">
                <input type="hidden" name="proof_file_name" id="proof_file_name">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-card">
            <div class="card-title">👤 Personal Information</div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">First Name<span>*</span></label>
                    <input type="text" name="f_name" class="form-control" placeholder="e.g. Rahul" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name<span>*</span></label>
                    <input type="text" name="l_name" class="form-control" placeholder="e.g. Sharma" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username<span>*</span></label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" name="user_name" id="user_name" class="form-control username-input" placeholder="e.g. rahul.sharma" required>
                        <button type="button" class="btn btn-outline" id="btnCheckUsername">Check</button>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">This will be used for ERP login.</div>
                    <input type="hidden" id="is_username_checked" value="false">
                </div>

                <div class="form-group">
                    <label class="form-label">System Role<span>*</span></label>
                    <select name="user_type_id" class="form-control" required>
                        <option value="">Select Role</option>
                        @foreach($userTypes as $role)
                            <option value="{{ $role->id }}">{{ $role->u_type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date of Birth<span>*</span></label>
                    <input type="date" name="dob" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender<span>*</span></label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="card-title">📞 Contact & Location Details</div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Mobile Number (mobile) <span>*</span></label>
                    <div style="display:flex; gap:10px;">
                        <input type="tel" name="mobile" id="mobile_input" class="form-control" placeholder="+91 00000 00000" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        <button type="button" class="btn btn-outline" id="btnSendMobileOtp">Get OTP</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; justify-content: space-between;">
                        Verify Mobile <span id="mobileTimerDisplay" style="color: #ef4444; font-weight: 700;"></span>
                    </label>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <div id="mobileOtpDisplay" style="font-size: 12px; color: var(--accent); margin-bottom: 4px; display: none; font-weight: 600;"></div>
                            <input type="text" name="otp_mobile" id="otp_mobile_input" class="form-control" placeholder="Enter OTP" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <input type="hidden" id="mobile_verify_time" value="0">
                        </div>
                        <button type="button" class="btn btn-outline" id="btnVerifyMobileOtp" style="color: var(--accent);">Verify</button>
                        <button type="button" class="btn btn-outline" id="btnResendMobileOtp" style="display: none; background: var(--bg-base);">Resend</button>
                    </div>
                    <input type="hidden" id="is_mobile_verified" value="false">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (email)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="email" name="email" id="email_input" class="form-control" placeholder="employee@shoeerp.com" required>
                        <button type="button" class="btn btn-outline" id="btnSendEmailOtp">Get OTP</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; justify-content: space-between;">
                        Verify Email <span id="emailTimerDisplay" style="color: #ef4444; font-weight: 700;"></span>
                    </label>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <div id="emailOtpDisplay" style="font-size: 12px; color: var(--accent); margin-bottom: 4px; display: none; font-weight: 600;"></div>
                            <input type="text" name="otp_email" id="otp_email_input" class="form-control" placeholder="Enter OTP" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <input type="hidden" id="email_verify_time" value="0">
                        </div>
                        <button type="button" class="btn btn-outline" id="btnVerifyEmailOtp" style="color: var(--accent);">Verify</button>
                        <button type="button" class="btn btn-outline" id="btnResendEmailOtp" style="display: none; background: var(--bg-base);">Resend</button>
                    </div>
                    <input type="hidden" id="is_email_verified" value="false">
                </div>

                <div class="form-group col-span-full">
                    <label class="form-label">Residential Address<span>*</span></label>
                    <textarea name="address" class="form-control" placeholder="Enter complete street address..." required></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; border-top: 1px solid var(--border-light); padding-top: 20px;">
                <div class="card-title" style="margin-bottom: 16px;">🌍 Geographic Details</div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">State Name<span>*</span></label>
                        <select name="state_id" id="state_id" class="form-control loc-trigger" data-target="district_id" data-url="/api/get-districts/" required>
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">District Name<span>*</span></label>
                        <select name="district_id" id="district_id" class="form-control" disabled required>
                            <option value="">Select District</option>
                        </select>
                    </div>
                </div>

                <div id="area_type_section" class="form-group" style="display: none; margin-top: 16px; padding: 12px; background: var(--bg-base); border-radius: 6px;">
                    <label class="form-label">Select Area Type <span>*</span></label>
                    <div style="display: flex; gap: 20px;">
                        <label style="font-size: 13px; font-weight: 500;"><input type="radio" name="area_type" value="rural" id="type_rural"> Rural (Block/Panchayat)</label>
                        <label style="font-size: 13px; font-weight: 500;"><input type="radio" name="area_type" value="urban" id="type_urban"> Urban (Municipality/Ward)</label>
                    </div>
                </div>

                <div id="rural_section" style="display: none; margin-top: 16px;">
                    <div class="grid-2" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Block <span>*</span></label>
                            <select id="block_id" name="block_id" class="form-control loc-trigger" data-target="gp_id" data-url="/api/get-gram-panchayats/">
                                <option value="">Select Block</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gram Panchayat <span>*</span></label>
                            <select id="gp_id" name="gp_id" class="form-control loc-trigger" data-target="vill_id" data-url="/api/get-villages/">
                                <option value="">Select Panchayat</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Village (Optional)</label>
                            <select id="vill_id" name="vill_id" class="form-control loc-trigger" data-target="post_id" data-url="/api/get-post-offices-by-village/">
                                <option value="">Select Village</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Post Office (Optional)</label>
                            <select id="post_id" name="post_id" class="form-control" disabled>
                                <option value="">Select Post Office</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="urban_section" style="display: none; margin-top: 16px;">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Municipality / Corp <span>*</span></label>
                            <select id="muni_id" name="muni_id" class="form-control loc-trigger" data-target="ward_id" data-url="/api/get-wards/">
                                <option value="">Select Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ward <span>*</span></label>
                            <select id="ward_id" name="ward_id" class="form-control">
                                <option value="">Select Ward</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 16px; width: 33%;">
                    <label class="form-label">PIN Code<span>*</span></label>
                    <input type="text" name="pin" class="form-control" placeholder="e.g. 700091" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                </div>
            </div>
            </div>

        <div class="form-group" style="margin-bottom: 16px; display: flex; justify-content: center;">
           <div class="g-recaptcha" data-sitekey="6LfEi4wsAAAAAH5ZQgikGadOD3F-FLrulusgoyjq"></div>
        </div>

        <div class="action-footer">
            <button type="button" class="btn btn-outline" onclick="window.location.reload();">Cancel</button>
            <button type="submit" id="submitBtn" class="btn btn-primary">Save Profile & Submit for Verification ➔</button>
        </div>

    </div>
</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@push('scripts')
<script>
    // ==========================================
    // 1. DYNAMIC LOCATION ENGINE
    // ==========================================
    window.fetchLocationData = async function(url, id, targetElementId) {
        const targetSelect = document.getElementById(targetElementId);
        if(!targetSelect) return;

        targetSelect.innerHTML = '<option value="">Loading...</option>';
        targetSelect.disabled = true;

        // Find the next dropdown in the chain and clear it
        const nextTargetId = targetSelect.getAttribute('data-target');
        if (nextTargetId) {
            const nextSelect = document.getElementById(nextTargetId);
            if (nextSelect) {
                nextSelect.innerHTML = '<option value="">Select Option</option>';
                nextSelect.disabled = true;
            }
        }

        if (id) {
            try {
                const res = await fetch(url + id);
                const data = await res.json();
                targetSelect.innerHTML = '<option value="">Select Option</option>';
                data.forEach(item => { targetSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`; });
                targetSelect.disabled = false;
            } catch(e) { targetSelect.innerHTML = '<option value="">Error loading</option>'; }
        } else { targetSelect.innerHTML = '<option value="">Select Option</option>'; }
    };

    // Listen to all dropdowns with class "loc-trigger"
    document.body.addEventListener('change', function(e) {
        if(e.target && e.target.classList.contains('loc-trigger')) {
            const targetId = e.target.getAttribute('data-target');
            const url = e.target.getAttribute('data-url');
            fetchLocationData(url, e.target.value, targetId);
        }
    });

    // Handle Area Type (Rural vs Urban) Radio Buttons
    document.body.addEventListener('change', function(e) {
        if(e.target && e.target.name === 'area_type') {
            const distId = document.getElementById('district_id') ? document.getElementById('district_id').value : null;
            
            if (e.target.value === 'rural') {
                document.getElementById('rural_section').style.display = 'block';
                document.getElementById('urban_section').style.display = 'none';
                if(distId) fetchLocationData('/api/get-blocks/', distId, 'block_id');
                
                // Clear out urban data
                document.getElementById('muni_id').innerHTML = '<option value="">Select Municipality</option>';
                document.getElementById('ward_id').innerHTML = '<option value="">Select Ward</option>';
            } else if(e.target.value === 'urban') {
                document.getElementById('urban_section').style.display = 'block';
                document.getElementById('rural_section').style.display = 'none';
                if(distId) fetchLocationData('/api/get-municipalities/', distId, 'muni_id');
                
                // Clear out rural data
                document.getElementById('block_id').innerHTML = '<option value="">Select Block</option>';
                document.getElementById('gp_id').innerHTML = '<option value="">Select Panchayat</option>';
                document.getElementById('vill_id').innerHTML = '<option value="">Select Village</option>';
                document.getElementById('post_id').innerHTML = '<option value="">Select Post Office</option>';
            }
        }
    });

    // Show Area Type Options only when District is selected
    document.getElementById('district_id').addEventListener('change', function(e) {
        const areaSec = document.getElementById('area_type_section');
        if(areaSec) areaSec.style.display = this.value ? 'block' : 'none';
    });

    // Reset fields if State changes
    document.getElementById('state_id').addEventListener('change', function(e) {
        document.getElementById('area_type_section').style.display = 'none';
        document.getElementById('rural_section').style.display = 'none';
        document.getElementById('urban_section').style.display = 'none';
        document.querySelectorAll('input[name="area_type"]').forEach(r => r.checked = false);
    });

    // ==========================================
    // 2. EXISTING OTP & USERNAME LOGIC
    // ==========================================
    
    document.getElementById('btnCheckUsername').addEventListener('click', async function() {
        const username = document.getElementById('user_name').value;
        if (!username) { toastr.error('Enter username first'); return; }

        this.innerHTML = '...';
        try {
            const res = await fetch('/api/check-username', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ user_name: username })
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                toastr.success(data.message);
                document.getElementById('is_username_checked').value = 'true';
                this.innerHTML = '✓';
                this.style.color = '#10b981';
                this.style.borderColor = '#10b981';
            } else {
                toastr.error(data.message);
                document.getElementById('is_username_checked').value = 'false';
                this.innerHTML = 'Check';
            }
        } catch (e) { toastr.error('Error checking username'); this.innerHTML = 'Check'; }
    });

    document.getElementById('user_name').addEventListener('input', function() {
        document.getElementById('is_username_checked').value = 'false';
        const btn = document.getElementById('btnCheckUsername');
        btn.innerHTML = 'Check';
        btn.style.color = '';
        btn.style.borderColor = '';
    });

    // ==========================================
    // OTP IMPLEMENTATION (Using Global Engine)
    // ==========================================
    
    let lockedMobile = null;
    let lockedEmail = null;

    const mobileUiConfig = {
        displayBoxId: 'mobileOtpDisplay',
        timerTextId: 'mobileTimerDisplay',
        verifyBtnId: 'btnVerifyMobileOtp',
        resendBtnId: 'btnResendMobileOtp',
        inputId: 'otp_mobile_input'
    };

    document.getElementById('btnSendMobileOtp').addEventListener('click', async function() {
        if(document.getElementById('is_mobile_verified').value === 'true') return; 
        const mobile = document.getElementById('mobile_input').value;
        if (!mobile || mobile.length < 10) { toastr.error('Enter valid 10-digit mobile.'); return; }
        
        lockedMobile = mobile;
        await window.erpOtpEngine.send(mobile, 'Mobile', mobileUiConfig);
    });

    document.getElementById('btnResendMobileOtp').addEventListener('click', async function() {
        await window.erpOtpEngine.send(lockedMobile, 'Mobile', mobileUiConfig);
    });

    document.getElementById('btnVerifyMobileOtp').addEventListener('click', async function() {
        const otp = document.getElementById('otp_mobile_input').value;
        if(!otp) { toastr.error('Enter OTP first'); return; }
        
        this.innerHTML = '...';
        
        const isSuccess = await window.erpOtpEngine.verify(lockedMobile, otp);
        
        if (isSuccess) {
            window.erpOtpEngine.stopTimer('Mobile');
            document.getElementById('is_mobile_verified').value = 'true';
            document.getElementById('mobile_verify_time').value = Date.now();
            document.getElementById('mobile_input').value = lockedMobile;
            document.getElementById('mobile_input').readOnly = true;
            document.getElementById('mobile_input').style.background = '#f1f5f9';
            document.getElementById('otp_mobile_input').readOnly = true;
            document.getElementById('otp_mobile_input').style.background = '#f1f5f9';
            document.getElementById('mobileTimerDisplay').innerText = '';
            document.getElementById('mobileOtpDisplay').style.display = 'none';
            this.innerText = 'Verified ✓';
            toastr.success('Mobile Verified!');
        } else {
            this.innerHTML = 'Verify';
        }
    });

    const emailUiConfig = {
        displayBoxId: 'emailOtpDisplay',
        timerTextId: 'emailTimerDisplay',
        verifyBtnId: 'btnVerifyEmailOtp',
        resendBtnId: 'btnResendEmailOtp',
        inputId: 'otp_email_input'
    };

    document.getElementById('btnSendEmailOtp').addEventListener('click', async function() {
        if(document.getElementById('is_email_verified').value === 'true') return; 
        const email = document.getElementById('email_input').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) { toastr.error('Please enter a valid email.'); return; }
        
        lockedEmail = email;
        await window.erpOtpEngine.send(email, 'Email', emailUiConfig);
    });

    document.getElementById('btnResendEmailOtp').addEventListener('click', async function() {
        await window.erpOtpEngine.send(lockedEmail, 'Email', emailUiConfig);
    });

    document.getElementById('btnVerifyEmailOtp').addEventListener('click', async function() {
        const otp = document.getElementById('otp_email_input').value;
        if(!otp) { toastr.error('Enter OTP first'); return; }
        
        this.innerHTML = '...';
        
        const isSuccess = await window.erpOtpEngine.verify(lockedEmail, otp);
        
        if (isSuccess) {
            window.erpOtpEngine.stopTimer('Email');
            document.getElementById('is_email_verified').value = 'true';
            document.getElementById('email_verify_time').value = Date.now();    
            document.getElementById('email_input').value = lockedEmail;
            document.getElementById('email_input').readOnly = true;
            document.getElementById('email_input').style.background = '#f1f5f9';
            document.getElementById('otp_email_input').readOnly = true;
            document.getElementById('otp_email_input').style.background = '#f1f5f9';
            document.getElementById('emailTimerDisplay').innerText = '';
            document.getElementById('emailOtpDisplay').style.display = 'none';
            this.innerText = 'Verified ✓';
            toastr.success('Email Verified!');
        } else {
            this.innerHTML = 'Verify';
        }
    });

    // ==========================================
    // 3. FINAL FORM SUBMISSION
    // ==========================================
    document.getElementById('registrationForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // CLEAR OLD INLINE ERRORS
        document.querySelectorAll('.custom-error-text').forEach(e => e.remove());
        document.querySelectorAll('.form-control').forEach(e => e.style.borderColor = '');

        if (document.getElementById('is_username_checked').value !== 'true') { 
            toastr.error('Check if Username is available.'); 
            document.getElementById('user_name').style.borderColor = '#ef4444';
            return; 
        }
        if (document.getElementById('is_mobile_verified').value !== 'true') { 
            toastr.error('Verify your Mobile OTP.'); 
            document.getElementById('mobile_input').style.borderColor = '#ef4444';
            return; 
        }
        if (document.getElementById('is_email_verified').value !== 'true') { 
            toastr.error('Verify your Email OTP.'); 
            document.getElementById('email_input').style.borderColor = '#ef4444';
            return; 
        }
        
        const recaptchaResponse = grecaptcha.getResponse();
        if (recaptchaResponse.length === 0) {
            toastr.error('Please complete the human verification (CAPTCHA).');
            return; 
        }

        const mobileTime = parseInt(document.getElementById('mobile_verify_time').value);
        const emailTime = parseInt(document.getElementById('email_verify_time').value);
        const now = Date.now();
        const twoMins = 120000; 

        if (mobileTime > 0 && (now - mobileTime > twoMins)) {
            toastr.error("Mobile OTP expired because form was not submitted in time. Please verify again.");
            document.getElementById('btnVerifyMobileOtp').innerText = 'Verify';
            document.getElementById('is_mobile_verified').value = 'false';
            document.getElementById('otp_mobile_input').readOnly = false;
            document.getElementById('otp_mobile_input').value = '';
            return;
        }

        if (emailTime > 0 && (now - emailTime > twoMins)) {
            toastr.error("Email OTP expired because form was not submitted in time. Please verify again.");
            document.getElementById('btnVerifyEmailOtp').innerText = 'Verify';
            document.getElementById('is_email_verified').value = 'false';
            document.getElementById('otp_email_input').readOnly = false;
            document.getElementById('otp_email_input').value = '';
            return;
        }

        // MANDATORY PRE-CHECKS
        

        const proofInput = document.getElementById('proofFile');
        if(!proofInput.files.length) { 
            toastr.error('Submission Failed: You must upload an ID proof.'); 
            proofInput.style.borderColor = '#ef4444';
            return; 
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = 'Processing...';
        submitBtn.disabled = true; // Disable to prevent double-clicks

        // 1. Temporarily un-disable fields so FormData can grab their values
        const disabledFields = this.querySelectorAll(':disabled');
        disabledFields.forEach(field => field.disabled = false);

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        // 2. Re-disable fields immediately
        disabledFields.forEach(field => field.disabled = true);

        // Map Base64 hidden inputs manually
        payload.image_doc_base64 = document.getElementById('image_doc_base64').value;
        payload.proof_doc_base64 = document.getElementById('proof_doc_base64').value;

        try {
            const response = await fetch("{{ route('register.store') }}", {
                method: "POST",
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload)
            });

            // If Laravel throws a 422 error, we must parse it here before jumping to the catch block!
            if (!response.ok) {
                const errorData = await response.json();
                throw errorData; 
            }

            const data = await response.json();
            toastr.success(data.message || 'Registration Submitted!');
            setTimeout(() => { window.location.href = data.data?.redirect_url || '/register/success'; }, 1000);

        } catch (error) {
            // Fix: Re-enable the button instantly if there is an error
            submitBtn.innerHTML = 'Save Profile & Submit for Verification ➔';
            submitBtn.disabled = false;
            
            try { grecaptcha.reset(); } catch(e) {}

            toastr.error(error.message || 'Please fix the errors highlighted below.');
            
            // INJECT INLINE ERRORS
            // Fix: Changed 'errorResponse' back to 'error' to match the catch variable
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    
                    // Look for standard input/select/textarea
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    
                    // Special case for radio buttons (area_type)
                    if (!field && document.querySelector(`input[name="${fieldName}"]`)) {
                        field = document.querySelector(`input[name="${fieldName}"]`).closest('.form-group');
                    }

                    if (field) {
                        field.style.borderColor = '#ef4444';
                        
                        // Avoid stacking multiple errors on the same field
                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('custom-error-text')) {
                            field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                        }
                    }
                }
            }
        }
    });

    document.getElementById('imageFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(!file) {
            document.getElementById('image_doc_base64').value = '';
            document.getElementById('avatarText').innerText = 'Upload Photo';
            return;
        }
        document.getElementById('avatarText').innerText = file.name;
        document.getElementById('image_file_name').value = file.name;
        const reader = new FileReader();
        reader.onload = (e) => document.getElementById('image_doc_base64').value = e.target.result;
        reader.readAsDataURL(file);
    });

    document.getElementById('proofFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(!file) return;
        document.getElementById('proof_file_name').value = file.name;
        const reader = new FileReader();
        reader.onload = (e) => document.getElementById('proof_doc_base64').value = e.target.result;
        reader.readAsDataURL(file);
    });
</script>
@endpush

@endsection

