<style>
    /* Sidebar */
    .sidebar {
        width: 280px;
        height: 100vh;
        /* background: #fff; */
        border-right: 1px solid #ddd;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    /* Hidden Sidebar */
    .sidebar.hide {
        left: -280px;
    }

    .content-wrapper{
        margin-left:280px;
        transition:all .3s ease;
    }

    .sidebar.hide + .content-wrapper{
        margin-left:0;
    }

    /* Toggle Button */
    .sidebar-toggle {
        position: fixed;
        top: 12px;
        left: 280px; /* Sit right beside the 280px sidebar */
        z-index: 1100;
        border: none;
        padding: 8px 12px;
        border-radius: 0 5px 5px 0; /* Flat on the left, rounded on the right looks nice */
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease; /* This makes it slide smoothly with the sidebar */
        background: #f8f9fa; /* Optional: adjust background color to your theme */
        box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Optional: Adds a nice shadow */
    }

    /* Desktop Content */
    .main-content{
        margin-left:280px !important;
        transition:all .3s ease;
    }

    /* Push topbar content right so sidebar toggle doesn't overlap */
    .topbar {
        padding-left: 55px !important;
    }

    .sidebar-toggle.hide {
        left: 0; /* Slides back to the far left edge */
        border-radius: 5px; /* Fully rounded when sitting alone */
    }
    .main-content.full-width{
        margin-left:0 !important;
    }

    /* Mobile */
    @media (max-width: 768px) {
        body {
            display: block;
            min-height: 100vh;
        }

        .sidebar-toggle-wrap {
            height: 0;
            padding: 0 !important;
            overflow: visible;
        }

        /* Sidebar hidden by default; shown when .hide is removed (same class logic as desktop) */
        .sidebar,
        .sidebar.hide {
            left: -280px;
            box-shadow: none;
        }
        .sidebar:not(.hide) {
            left: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
        }

        /* Dark overlay when sidebar is open */
        .sidebar:not(.hide) ~ .main-content::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 5;
            pointer-events: none;
        }

        .main-content {
            margin-left: 0;
        }

        .sidebar-toggle,
        .sidebar-toggle.hide {
            left: 0;
            top: 10px;
            padding: 6px 10px;
            font-size: 16px;
        }
        .sidebar-toggle:not(.hide) {
            left: 280px;
        }

        .topbar {
            padding-left: 45px !important;
        }
    }
</style> 

<div class="sidebar-toggle-wrap" style="display: flex; align-items: center; padding: 15px;">
    <button id="sidebarToggle" class="sidebar-toggle">
        ☰
    </button>
</div>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-icon">NSE</div> Nandi Shoe House
    </div>
    <nav class="nav-menu" id="erpSidebarMenu">
        
        @php
            // Get the logged-in user's type. Default to 0 if not logged in.
            $userType = auth()->check() ? auth()->user()->user_type_id : 0;
        @endphp

        <div class="nav-label">Main Menu</div>
        @if(in_array($userType, [1, 2, 3]))
            <a href="/dashboard" class="nav-item {{ request()->routeIs('offline.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
        @endif
        
        @if(in_array($userType, [1, 2, 3]))
        <div class="sidebar-section">
            <div class="nav-label">Master Data</div>
            
            @if(in_array($userType, [1, 3]))
                <a href="/vendors" class="nav-item {{ request()->routeIs('vendor.index') ? 'active' : '' }}">
                    Vendor Master
                </a>
            @endif
            
            @if(in_array($userType, [1, 3]))
                <a href="/online-shops" class="nav-item {{ request()->routeIs('online-shops.index') ? 'active' : '' }}">
                    Online Shops Master
                </a>
            @endif
            
            @if(in_array($userType, [1, 2]))
                <a href="/products" class="nav-item {{ request()->routeIs('products.store') ? 'active' : '' }}">
                    Products Master
                </a>
            @endif
            
            @if(in_array($userType, [1, 2]))
                <a href="/prices" class="nav-item {{ request()->routeIs('price.index') ? 'active' : '' }}">
                    Price Master
                </a>
            @endif

            @if($userType == 1)
                <a href="/stores" class="nav-item {{ request()->routeIs('store.index') ? 'active' : '' }}">Store Master</a>
                <a href="/categories" class="nav-item {{ request()->routeIs('categories.store') ? 'active' : '' }}">Category Master</a>
                <a href="/colours" class="nav-item {{ request()->routeIs('colour.index') ? 'active' : '' }}">Colour Master</a>
                <a href="/units" class="nav-item {{ request()->routeIs('units.store') ? 'active' : '' }}">Unit Master</a>
                <a href="/unit-conversions" class="nav-item {{ request()->routeIs('unit-conversions.store') ? 'active' : '' }}">Unit Convert Master</a>
            @endif
        </div>
        @endif
                
        @if($userType == 1)
        <div class="sidebar-section">
            <div class="nav-label">Purchases Data</div>
            <a href="/purchases" class="nav-item {{ request()->routeIs('purchased.index') ? 'active' : '' }}">Product Purchases</a>
            <a href="/purchase-history" class="nav-item {{ request()->routeIs('purchased.history') ? 'active' : '' }}">Purchases History</a>
            <a href="/godown-stock" class="nav-item {{ request()->routeIs('purchased.stock') ? 'active' : '' }}">Purchases Stock</a>
            <a href="/transaction-ledger" class="nav-item {{ request()->routeIs('purchased.ledger') ? 'active' : '' }}">Purchases Transaction Ledger</a>
        </div>

        <div class="sidebar-section">
            <div class="nav-label">Store & Stock</div>
            <a href="/store-transfers" class="nav-item {{ request()->routeIs('store_stock.index') ? 'active' : '' }}">Godown Stock Transfer</a>
            <a href="/store-total-stock" class="nav-item {{ request()->routeIs('store_stock.total') ? 'active' : '' }}">Store Total Stock</a>
            <a href="/store-purchase-history" class="nav-item {{ request()->routeIs('store_purchase_history.inward') ? 'active' : '' }}">Store Purchase History</a>
            <a href="/store-all-transaction" class="nav-item {{ request()->routeIs('store_all_stock.transaction') ? 'active' : '' }}">Stock All Transfer Transaction History</a>
            <a href="/combo/list" class="nav-item {{ request()->routeIs('combo.list') ? 'active' : '' }}">Combo List</a>
        </div>

        <div class="sidebar-section">
            <div class="nav-label">Inventory & Sales</div>
            <a href="/store-sale" class="nav-item {{ request()->routeIs('store.sale.index') ? 'active' : '' }}">Store Sale (POS)</a>
            <a href="/requisition/create" class="nav-item {{ request()->routeIs('requisition.create') ? 'active' : '' }}">Create Requisition</a>
            <a href="/requisition/list" class="nav-item {{ request()->routeIs('requisition.list') ? 'active' : '' }}">Requisition List</a>
            <a href="#" class="nav-item">Purchase Entry</a>
            <a href="#" class="nav-item">Live Store Stock</a>
        </div>
        @endif

    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainContent = document.querySelector('.main-content');

        // On mobile, sidebar overlay starts hidden regardless of saved state
        if (window.innerWidth <= 768) {
            sidebar.classList.add('hide');
            toggleBtn.classList.add('hide');
            if(mainContent) mainContent.classList.add('full-width');
        } else {
            // Restore state only on desktop
            if(localStorage.getItem('sidebarState') === 'hidden'){
                sidebar.classList.add('hide');
                toggleBtn.classList.add('hide');
                if(mainContent) mainContent.classList.add('full-width');
            }
        }

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('hide');
            toggleBtn.classList.toggle('hide');
            if(mainContent) mainContent.classList.toggle('full-width');

            if(sidebar.classList.contains('hide')){
                localStorage.setItem('sidebarState', 'hidden');
            } else {
                localStorage.setItem('sidebarState', 'show');
            }
        });

        // --- Role / User Type Logic ---
        const userData = JSON.parse(localStorage.getItem('erp_user'));

        // Check for user_type_id instead of role_id
        if (userData && userData.user_type_id !== undefined) {
            const userTypeId = userData.user_type_id.toString();

            // 1. Hide/Show individual links based on data-allowed-types
            document.querySelectorAll('.nav-item').forEach(item => {
                const allowedTypes = item.getAttribute('data-allowed-types');
                
                if (allowedTypes) {
                    const typesArray = allowedTypes.split(',');
                    // If the user's type is NOT in the allowed array, hide the link
                    if (!typesArray.includes(userTypeId)) {
                        item.style.display = 'none';
                    }
                } else {
                    // If a link accidentally has no attribute, hide it for everyone except Super Admin (1)
                    if (userTypeId !== '1') {
                        item.style.display = 'none';
                    }
                }
            });

            // 2. Hide entire sections if all their links got hidden
            document.querySelectorAll('.sidebar-section').forEach(section => {
                const links = section.querySelectorAll('.nav-item');
                // Check if at least one link inside this section is visible
                const hasVisibleLinks = Array.from(links).some(link => link.style.display !== 'none');
                
                if (!hasVisibleLinks) {
                    // Hide the whole section (including the nav-label) if empty
                    section.style.display = 'none';
                }
            });
        }

    });
</script>

