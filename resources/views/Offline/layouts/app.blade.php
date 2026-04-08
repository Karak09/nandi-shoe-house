<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Shoe ERP Offline System')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    
    @vite(['resources/css/offline/app.css', 'resources/js/offline/app.js'])
    
    @stack('styles')
</head>
<body>

    @include('Offline.partials.sidebar')

    <main class="main-content">
        @include('Offline.partials.header')

        <div class="workspace">
            @if(session('success'))
                <div style="background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between;">
                    {{ session('success') }}
                    <button class="close-alert" style="background:none; border:none; font-weight:bold; cursor:pointer;">×</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // --- 1. DYNAMIC HEADER INFO ---
        const userData = JSON.parse(localStorage.getItem('erp_user'));
        if (userData) {
            const nameEl = document.getElementById('headerUserName');
            const roleEl = document.getElementById('headerUserRole');
            const avatarEl = document.getElementById('headerUserAvatar');

            if(nameEl) nameEl.innerText = userData.name;
            if(roleEl) roleEl.innerText = 'Role: ' + userData.role;
            if(avatarEl) avatarEl.innerText = userData.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        }

        // --- 2. GLOBAL LOGOUT ---
        const logoutBtn = document.getElementById('btnLogoutSafe'); 
        if(logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                try {
                    await fetch('/api/auth/logout', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token') }
                    });
                } catch(e) {} 
                localStorage.removeItem('erp_jwt_token');
                localStorage.removeItem('erp_user');
                window.location.href = '/login';
            });
        }

        // --- 3. AUTO-LOGOUT (15 MIN INACTIVITY) ---
        let lastActivity = Date.now();
        const logoutTimeLimit = 15 * 60 * 1000; // 15 mins in milliseconds

        // Update timestamp safely without freezing the browser
        const updateActivity = () => { lastActivity = Date.now(); };
        window.addEventListener('mousemove', updateActivity, { passive: true });
        window.addEventListener('keypress', updateActivity, { passive: true });
        window.addEventListener('click', updateActivity, { passive: true });
        window.addEventListener('scroll', updateActivity, { passive: true });

        // Check inactivity only once every 30 seconds (Highly Efficient)
        setInterval(() => {
            if (Date.now() - lastActivity > logoutTimeLimit) {
                const logoutBtn = document.getElementById('btnLogoutSafe');
                if(logoutBtn) {
                    alert("You have been inactive for 15 minutes. For security reasons, you have been logged out.");
                    logoutBtn.click();
                }
            }
        }, 30000);

        // --- 4. GLOBAL TABLE SEARCH ---
        window.tableSearch = function(inputId, tableId) {
            const filter = document.getElementById(inputId).value.toLowerCase();
            const table = document.getElementById(tableId);
            if(!table) return;
            const tr = table.getElementsByTagName("tr");
            let visibleCount = 0;

            for (let i = 1; i < tr.length; i++) {
                let rowText = tr[i].textContent || tr[i].innerText;
                if (rowText.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    visibleCount++;
                } else {
                    tr[i].style.display = "none";
                }
            }

            let noDataRow = document.getElementById(tableId + '_noData');
            if (visibleCount === 0) {
                if (!noDataRow) {
                    table.getElementsByTagName('tbody')[0].insertAdjacentHTML('beforeend', `<tr id="${tableId}_noData"><td colspan="100%" style="text-align:center; padding: 24px; color: #64748b;">No matching records found.</td></tr>`);
                } else { noDataRow.style.display = ''; }
            } else if (noDataRow) { noDataRow.style.display = 'none'; }
        };

        // ==========================================
        //  GLOBAL TABLE SORTING
        // ==========================================
        window.sortTable = function(tableId, colIndex, type = 'string') {
            const table = document.getElementById(tableId);
            const tbody = table.tBodies[0];
            const rows = Array.from(tbody.rows);

            // Track sort direction per column
            let currentDir = table.getAttribute("data-sort-dir-" + colIndex) || "asc";
            let newDir = currentDir === "asc" ? "desc" : "asc";

            rows.sort((a, b) => {
                let x = a.cells[colIndex].innerText.trim().toLowerCase();
                let y = b.cells[colIndex].innerText.trim().toLowerCase();

                if (type === 'number') {
                    x = parseFloat(x) || 0;
                    y = parseFloat(y) || 0;
                }

                if (x < y) return newDir === "asc" ? -1 : 1;
                if (x > y) return newDir === "asc" ? 1 : -1;
                return 0;
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));

            // Save direction
            table.setAttribute("data-sort-dir-" + colIndex, newDir);
        };

        // ==========================================
        // 2. GLOBAL TABLE ENGINE (Pagination, Sorting, Filtering)
        // ==========================================
        // This automatically finds any table with the class "datatable"
        // and turns it into an advanced, paginated table.
        const dataTables = document.querySelectorAll('.datatable');
        dataTables.forEach(table => {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "Search anything...",
                    perPage: "records per page",
                    noRows: "No matching records found",
                    info: "Showing {start} to {end} of {rows} entries"
                }
            });
        });

        // --- . GLOBAL LOCATION DEPENDENCY ENGINE ---
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

        // Attach listeners to all class="loc-trigger"
        document.body.addEventListener('change', function(e) {
            if(e.target && e.target.classList.contains('loc-trigger')) {
                const targetId = e.target.getAttribute('data-target');
                const url = e.target.getAttribute('data-url');
                fetchLocationData(url, e.target.value, targetId);
            }
        });

        // Handle Area Type (Rural vs Urban) Toggle
        document.body.addEventListener('change', function(e) {
            if(e.target && e.target.name === 'area_type') {
                const distId = document.getElementById('district_id') ? document.getElementById('district_id').value : null;
                
                if (e.target.value === 'rural') {
                    document.getElementById('rural_section').style.display = 'block';
                    document.getElementById('urban_section').style.display = 'none';
                    if(distId) fetchLocationData('/api/get-blocks/', distId, 'block_id');
                    
                    // Require/Unrequire fields
                    if(document.getElementById('block_id')) document.getElementById('block_id').required = true;
                    if(document.getElementById('gp_id')) document.getElementById('gp_id').required = true;
                    if(document.getElementById('muni_id')) document.getElementById('muni_id').required = false;
                    if(document.getElementById('ward_id')) document.getElementById('ward_id').required = false;
                } else if(e.target.value === 'urban') {
                    document.getElementById('urban_section').style.display = 'block';
                    document.getElementById('rural_section').style.display = 'none';
                    if(distId) fetchLocationData('/api/get-municipalities/', distId, 'muni_id');
                    
                    // Require/Unrequire fields
                    if(document.getElementById('muni_id')) document.getElementById('muni_id').required = true;
                    if(document.getElementById('ward_id')) document.getElementById('ward_id').required = true;
                    if(document.getElementById('block_id')) document.getElementById('block_id').required = false;
                    if(document.getElementById('gp_id')) document.getElementById('gp_id').required = false;
                }
            }
        });

        // Show Area Type options only when District is selected
        const districtSelect = document.getElementById('district_id');
        if(districtSelect) {
            districtSelect.addEventListener('change', function() {
                const areaSec = document.getElementById('area_type_section');
                if(areaSec) areaSec.style.display = this.value ? 'block' : 'none';
            });
        }

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
    });
</script>

@stack('scripts')
</body>
</html>