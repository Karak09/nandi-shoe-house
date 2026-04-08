<header class="topbar">
    <div class="page-title">
        <h1>@yield('page_title', 'Dashboard')</h1>
    </div>
    
    <div id="liveClock" style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-left: 20px; margin-right: auto;"></div>

    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="text-align: right;">
            <div id="headerUserName" style="font-size: 13px; font-weight: 700; color: var(--text-main);">Guest User</div>
            <div id="headerUserRole" style="font-size: 11px; color: var(--text-muted);">Role: None</div>
        </div>
        <div id="headerUserAvatar" style="width: 36px; height: 36px; border-radius: 8px; background: var(--brand-color, #4f46e5); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">U</div>
        
        <button type="button" id="btnLogoutSafe" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; color: #ef4444; border-color: #ef4444; background: white; cursor: pointer;">Logout</button>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. POPULATE USER DATA
        const userData = JSON.parse(localStorage.getItem('erp_user'));
        if (userData) {
            document.getElementById('headerUserName').innerText = userData.name;
            document.getElementById('headerUserRole').innerText = 'Role: ' + userData.role;
            document.getElementById('headerUserAvatar').innerText = userData.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        }

        // 2. LIVE CLOCK
        setInterval(() => {
            const now = new Date();
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('liveClock').innerText = now.toLocaleDateString('en-IN', options);
        }, 1000);

        // 3. LOGOUT LOGIC
        document.getElementById('btnLogoutSafe').addEventListener('click', async function() {
            try {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token') }
                });
            } catch(e) {} // Ignore API errors, force clear local storage
            
            localStorage.removeItem('erp_jwt_token');
            localStorage.removeItem('erp_user');
            window.location.href = '/login';
        });
    });
</script>