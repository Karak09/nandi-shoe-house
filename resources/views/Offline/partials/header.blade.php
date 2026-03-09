<header class="topbar">
    <div class="page-title">
        @yield('page_title', 'Shoe ERP')
    </div>
    
    <div style="display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 600;">
        {{ Auth::user()->user_name ?? 'Guest User' }}
        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--brand-dark); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px;">
            {{ strtoupper(substr(Auth::user()->f_name ?? 'U', 0, 1)) }}
        </div>
        
        <form method="POST" action="#" style="margin-left: 10px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; font-size: 12px;">
                Logout
            </button>
        </form>
    </div>
</header>