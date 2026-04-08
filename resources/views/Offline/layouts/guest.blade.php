<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shoe ERP Registration')</title>

    @vite(['resources/css/offline/app.css', 'resources/js/offline/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @stack('styles')
</head>
<body class="guest-body">
    
    <header class="topbar guest-topbar">
        <div class="logo">
            <div class="logo-icon">NSH</div>Nandi Shoe House
        </div>
        <div class="header-status">
            <!-- <div class="secure-badge"></div> -->
            New User Registration
        </div>
    </header>

    <main class="guest-workspace">
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Generic Fetch Function
        window.fetchLocationData = async function(url, id, targetElementId) {
            const targetSelect = document.getElementById(targetElementId);
            if(!targetSelect) return;

            targetSelect.innerHTML = '<option value="">Loading...</option>';
            targetSelect.disabled = true;

            if (id) {
                try {
                    const res = await fetch(url + id);
                    const data = await res.json();
                    targetSelect.innerHTML = '<option value="">Select Option</option>';
                    data.forEach(item => { targetSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`; });
                    targetSelect.disabled = false;
                } catch(e) { targetSelect.innerHTML = '<option value="">Error loading data</option>'; }
            } else {
                targetSelect.innerHTML = '<option value="">Select Option</option>';
            }
        };

        // 2. Listen to all dropdowns with class "loc-trigger"
        document.body.addEventListener('change', function(e) {
            if(e.target && e.target.classList.contains('loc-trigger')) {
                const targetId = e.target.getAttribute('data-target');
                const url = e.target.getAttribute('data-url');
                fetchLocationData(url, e.target.value, targetId);
            }
        });

        // 3. Handle Area Type (Rural vs Urban) Toggle
        document.body.addEventListener('change', function(e) {
            if(e.target && e.target.name === 'area_type') {
                const distId = document.getElementById('district_id') ? document.getElementById('district_id').value : null;
                
                if (e.target.value === 'rural') {
                    document.getElementById('rural_section').style.display = 'block';
                    document.getElementById('urban_section').style.display = 'none';
                    if(distId) fetchLocationData('/api/get-blocks/', distId, 'block_id');
                    
                    if(document.getElementById('block_id')) document.getElementById('block_id').required = true;
                    if(document.getElementById('gp_id')) document.getElementById('gp_id').required = true;
                    if(document.getElementById('muni_id')) document.getElementById('muni_id').required = false;
                    if(document.getElementById('ward_id')) document.getElementById('ward_id').required = false;
                } else if(e.target.value === 'urban') {
                    document.getElementById('urban_section').style.display = 'block';
                    document.getElementById('rural_section').style.display = 'none';
                    if(distId) fetchLocationData('/api/get-municipalities/', distId, 'muni_id');
                    
                    if(document.getElementById('muni_id')) document.getElementById('muni_id').required = true;
                    if(document.getElementById('ward_id')) document.getElementById('ward_id').required = true;
                    if(document.getElementById('block_id')) document.getElementById('block_id').required = false;
                    if(document.getElementById('gp_id')) document.getElementById('gp_id').required = false;
                }
            }
        });

        // 4. Show Area Type Options only when District is selected
        document.body.addEventListener('change', function(e) {
            if(e.target && e.target.id === 'district_id') {
                const areaSec = document.getElementById('area_type_section');
                if(areaSec) areaSec.style.display = e.target.value ? 'block' : 'none';
            }
            
            // Reset everything if State changes
            if(e.target && e.target.id === 'state_id') {
                const areaSec = document.getElementById('area_type_section');
                if(areaSec) areaSec.style.display = 'none';
                const ruralSec = document.getElementById('rural_section');
                if(ruralSec) ruralSec.style.display = 'none';
                const urbanSec = document.getElementById('urban_section');
                if(urbanSec) urbanSec.style.display = 'none';
                document.querySelectorAll('input[name="area_type"]').forEach(r => r.checked = false);
            }
        });
    });

    window.erpOtpEngine = {
        timers: {},

        // 1. Send OTP via Backend
        send: async function(identity, type, uiConfig) {
            try {
                const res = await fetch('/api/auth/send-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ identity: identity })
                });
                const data = await res.json();
                if(!res.ok) throw data;

                // TODO: Remove this alert when moving to Production SMS/Email
                alert(`Your ${type} OTP is: ` + data.demo_otp); 
                
                const otpDisplayBox = document.getElementById(uiConfig.displayBoxId);
                if(otpDisplayBox) {
                    otpDisplayBox.innerText = "OTP: " + data.demo_otp;
                    otpDisplayBox.style.display = 'block';
                }

                toastr.info(`${type} OTP Sent successfully!`);
                this.startTimer(type, uiConfig);
                return true;
            } catch (error) {
                toastr.error(error.message || 'Failed to send OTP. Please try again.');
                return false;
            }
        },

        // 2. Verify OTP via Backend
        verify: async function(identity, otp) {
            try {
                const res = await fetch('/api/auth/verify-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ identity: identity, otp: otp })
                });
                const data = await res.json();
                if(!res.ok) throw data;
                return true;
            } catch (error) {
                toastr.error(error.message || 'Invalid or Expired OTP.');
                return false;
            }
        },

        // 3. Handle Frontend Timer & UI Toggles
        startTimer: function(type, uiConfig, duration = 45) {
            let timeLeft = duration;
            const timerDisplay = document.getElementById(uiConfig.timerTextId);
            const verifyBtn = document.getElementById(uiConfig.verifyBtnId);
            const resendBtn = document.getElementById(uiConfig.resendBtnId);
            const inputField = document.getElementById(uiConfig.inputId);

            if(timerDisplay) timerDisplay.innerText = `${timeLeft}s`;
            if(resendBtn) resendBtn.style.display = 'none';
            if(verifyBtn) verifyBtn.style.display = 'block';
            if(inputField) { inputField.disabled = false; inputField.value = ''; inputField.focus(); }

            clearInterval(this.timers[type]);

            this.timers[type] = setInterval(() => {
                timeLeft--;
                if(timerDisplay) timerDisplay.innerText = `${timeLeft}s`;

                if (timeLeft <= 0) {
                    clearInterval(this.timers[type]);
                    if(timerDisplay) timerDisplay.innerText = 'Expired';
                    if(verifyBtn) verifyBtn.style.display = 'none';
                    if(inputField) inputField.disabled = true;
                    if(resendBtn) resendBtn.style.display = 'block';
                }
            }, 1000);
        },

        // 4. Stop Timer on Success
        stopTimer: function(type) {
            clearInterval(this.timers[type]);
        }
    };
    </script>
    @stack('scripts')
</body>
</html>