@extends('Offline.layouts.guest')
@section('title', 'Secure Login - Nandi Shoe House ERP')

@section('content')
<div class="login-layout" style="margin: -40px -24px; max-width: none;"> 
    <div class="auth-brand" style="background-image: url('{{ asset('images/shop-bg.jpeg') }}');">
        <div class="brand-content">
            <div class="auth-logo"><div class="auth-logo-icon">NS</div> Nandi Shoe House</div>
        </div>
    </div>

    <div class="auth-form-wrapper">
        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Please enter your credentials to access your workspace.</p>
        </div>

        <form id="loginForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="margin-bottom: 8px; display:block;">Login ID or Username</label>
                <input type="text" id="login_id" class="login-input" placeholder="e.g. employee@gmail.com" required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" style="margin-bottom: 8px; display:block;">Password</label>
                <div class="pwd-field">
                    <input type="password" id="password" class="login-input" placeholder="••••••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-pwd" onclick="togglePassword()">Show</button>
                </div>
            </div>

            <div class="form-utils">
                <div class="utils-row">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="rememberMe">
                        <span class="checkbox-label">Remember this device</span>
                    </label>
                    <a href="/forgot-password" class="auth-link">Forgot password?</a>
                </div>
                <div class="utils-row" style="justify-content: flex-end;">
                    <a href="/forgot-username" class="auth-link" style="color: var(--text-muted); font-size: 12px;">Forgot ID/Email?</a>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px; display: flex; justify-content: center;">
                <div class="g-recaptcha" data-sitekey="6LfEi4wsAAAAAH5ZQgikGadOD3F-FLrulusgoyjq"></div>
            </div>

            <button type="submit" id="loginBtn" class="btn-login">Sign in ➔</button>
        </form>

        <div class="auth-footer-links" style="display:flex; flex-direction:column; gap:10px; align-items:center;">
            <div>New employee? <a href="{{ route('register') }}" class="auth-link">Register here</a></div>
            <a href="{{ route('registration.status') }}" class="btn-outline" style="font-size:12px; padding:6px 12px; border-radius:4px; text-decoration:none; border:1px solid #cbd5e1; color:#0f172a; font-weight:600;">Check Registration Status</a>
        </div>
    </div>
</div>

<div id="deviceModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:450px; border-radius:8px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0; color:#ef4444; font-size:18px;">Device Limit Reached</h3>
        <p style="font-size:13px; color:#64748b; margin-bottom:20px;">You are currently logged into the maximum allowed devices (2). Select a device to log out of so you can access the dashboard here.</p>
        
        <div id="deviceList" style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;"></div>
        
        <div style="text-align:right;">
            <button onclick="document.getElementById('deviceModal').style.display='none'" style="padding:8px 16px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    let tempPassword = ''; 

    // 1. Primary Login Function
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const loginBtn = document.getElementById('loginBtn');
        loginBtn.disabled = true;
        loginBtn.innerHTML = "Authenticating...";

        const login_id = document.getElementById('login_id').value;
        const password = document.getElementById('password').value;
        tempPassword = password; // Save for the kick-out request

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ login_id, password })
            });

            const data = await response.json();

            // IF DEVICE LIMIT REACHED (409 Error)
            if (response.status === 409) {
                // FIXED: Name matches the function below perfectly
                showDeviceManager(data.active_devices, login_id); 
                loginBtn.disabled = false;
                loginBtn.innerHTML = "Sign in ➔";
                return;
            }

            if (!response.ok) throw data;

            // IF LOGIN SUCCESSFUL
            processSuccess(data);

        } catch (error) {
            toastr.error(error.message || "Login failed");
            loginBtn.disabled = false;
            loginBtn.innerHTML = "Sign in ➔";
        }
    });

    // 2. Render the Modal with the Active Devices
    function showDeviceManager(devices, loginId) {
        const container = document.getElementById('deviceList');
        container.innerHTML = ''; 

        if (!devices || Object.keys(devices).length === 0) {
            container.innerHTML = '<p style="color:red; font-size:12px;">No active sessions found.</p>';
            document.getElementById('deviceModal').style.display = 'flex';
            return;
        }

        for (const id in devices) {
            if (devices.hasOwnProperty(id)) {
                const dev = devices[id];
                const row = document.createElement('div');
                row.style = "padding:12px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;";
                
                row.innerHTML = `
                    <div style="text-align:left;">
                        <div style="font-weight:700; font-size:12px; color:#1e293b;">IP: ${dev.ip || 'Unknown'}</div>
                        <div style="font-size:11px; color:#64748b;">Logged in: ${dev.time || 'N/A'}</div>
                    </div>
                    <button onclick="forceLoginAndKick('${id}', '${loginId}')" 
                            style="padding:8px 12px; background:#ef4444; color:white; border:none; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;">
                        Logout This
                    </button>
                `;
                container.appendChild(row);
            }
        }
        document.getElementById('deviceModal').style.display = 'flex';
    }

    // 3. The "Kick and Auto-Login" Function
    // FIXED: Name matches the button's onclick event perfectly
    async function forceLoginAndKick(oldDeviceId, loginId) {
        try {
            document.getElementById('deviceModal').style.display = 'none';
            toastr.info("Kicking other device and logging you in...");

            const response = await fetch('/api/auth/force-logout-device', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ 
                    device_id: oldDeviceId, 
                    login_id: loginId, 
                    password: tempPassword 
                })
            });

            const data = await response.json();
            if (!response.ok) throw data;

            // Auto-Login the current session
            processSuccess(data);

        } catch (error) {
            toastr.error(error.message || "Failed to logout device");
        }
    }

    // 4. Shared Success Logic
    function processSuccess(data) {
        localStorage.setItem('erp_jwt_token', data.access_token);
        localStorage.setItem('erp_device_id', data.device_id);
        
        toastr.success("Authenticated! Redirecting...");
        setTimeout(() => {
            window.location.href = data.redirect_url;
        }, 1000);
    }

    function togglePassword() {
        const p = document.getElementById('password');
        p.type = p.type === 'password' ? 'text' : 'password';
    }
</script>
<!-- <script>
    let tempPassword = ''; // Temporarily store password for auto-login after device kick

    function togglePassword() {
        const pwdInput = document.getElementById('password');
        const pwdBtn = document.querySelector('.toggle-pwd');
        if (pwdInput.type === 'password') { pwdInput.type = 'text'; pwdBtn.textContent = 'Hide'; } 
        else { pwdInput.type = 'password'; pwdBtn.textContent = 'Show'; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedId = localStorage.getItem('erp_remembered_id');
        if (savedId) {
            document.getElementById('login_id').value = savedId;
            document.getElementById('rememberMe').checked = true;
        }
    });

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const loginBtn = document.getElementById('loginBtn');
        const originalText = loginBtn.innerHTML;
        loginBtn.disabled = true;
        loginBtn.innerHTML = 'Authenticating...';

        const loginId = document.getElementById('login_id').value;
        const password = document.getElementById('password').value;
        tempPassword = password; // Save for kick logic
        
        if (document.getElementById('rememberMe').checked) localStorage.setItem('erp_remembered_id', loginId);
        else localStorage.removeItem('erp_remembered_id');

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ login_id: loginId, password: password })
            });
            const data = await response.json();

            // Catch the 2-Device Limit Error!
            if(response.status === 409 && data.status === 'device_limit') {
                showDeviceManager(data.active_devices, loginId);
                throw new Error(data.message);
            }
            if (!response.ok) throw data;

            toastr.success(data.message);
            localStorage.setItem('erp_jwt_token', data.access_token);
            localStorage.setItem('erp_device_id', data.device_id);
            setTimeout(() => { window.location.href = data.redirect_url; }, 1000);

        } catch (error) {
            toastr.error(error.message || 'Login failed.');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalText;
        }
    });

    function showDeviceManager(devices, loginId) {
        let html = '';
        for (const [id, dev] of Object.entries(devices)) {
            html += `
                <div style="padding:12px; border:1px solid #e2e8f0; border-radius:6px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <div>
                        <div style="font-weight:600; font-size:13px;">IP Address: ${dev.ip}</div>
                        <div style="font-size:11px; color:#64748b;">Logged in: ${dev.time}</div>
                    </div>
                    <button onclick="forceLoginAndKick('${id}', '${loginId}')" style="padding:8px 16px; background:#ef4444; color:white; border:none; border-radius:4px; font-size:12px; font-weight:600; cursor:pointer;">Log Out Device & Enter</button>
                </div>
            `;
        }
        document.getElementById('deviceList').innerHTML = html;
        document.getElementById('deviceModal').style.display = 'flex';
    }

    async function forceLoginAndKick(deviceId, loginId) {
        try {
            document.getElementById('deviceModal').style.display = 'none';
            toastr.info("Logging out old device and entering dashboard...");

            const response = await fetch('/api/auth/force-logout-device', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                // Notice we send the password so the backend can securely log us in!
                body: JSON.stringify({ device_id: deviceId, login_id: loginId, password: tempPassword }) 
            });
            const data = await response.json();
            if(!response.ok) throw data;
            
            toastr.success(data.message);
            localStorage.setItem('erp_jwt_token', data.access_token);
            localStorage.setItem('erp_device_id', data.device_id);
            setTimeout(() => { window.location.href = data.redirect_url; }, 1000);
            
        } catch(e) { 
            toastr.error('Failed to log out device.'); 
            document.getElementById('loginBtn').disabled = false;
            document.getElementById('loginBtn').innerHTML = 'Sign in ➔';
        }
    }
</script> -->
@endsection