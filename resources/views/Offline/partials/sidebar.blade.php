<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">SE</div> Shoe ERP System
    </div>
    <nav class="nav-menu" id="erpSidebarMenu">
        
        <div class="nav-label">Main Menu</div>
        <a href="/dashboard" class="nav-item {{ request()->routeIs('offline.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        
        <div class="sidebar-section" data-allowed-roles="0,1">
            <div class="nav-label">Master Data</div>
            <a href="/vendors" class="nav-item {{ request()->routeIs('vendor.index') ? 'active' : '' }}">
                Vendor Master
            </a>
            <a href="/stores" class="nav-item {{ request()->routeIs('store.index') ? 'active' : '' }}">
                Store  Master
            </a>
            <a href="/online-shops" class="nav-item {{ request()->routeIs('online-shops.index') ? 'active' : '' }}">
                Online Shops Master
            </a>
            <a href="/categories" class="nav-item {{ request()->routeIs('categories.store') ? 'active' : '' }}">
                Category Master
            </a>
            <a href="/units" class="nav-item {{ request()->routeIs('units.store') ? 'active' : '' }}">
                Unit Master
            </a>
            <a href="/unit-conversions" class="nav-item {{ request()->routeIs('unit-conversions.store') ? 'active' : '' }}">
                Unit Convert Master
            </a>
            <a href="/products" class="nav-item {{ request()->routeIs('products.store') ? 'active' : '' }}">
                Products Master
            </a>
        </div>
                
        <div class="sidebar-section" data-allowed-roles="0,1">
            <div class="nav-label">Purchases Data</div>
            <a href="/purchases" class="nav-item {{ request()->routeIs('purchased.index') ? 'active' : '' }}">
                Product Purchases
            </a>
            <a href="/purchase-history" class="nav-item {{ request()->routeIs('purchased.history') ? 'active' : '' }}">
                Purchases History
            </a>
            <a href="/godown-stock" class="nav-item {{ request()->routeIs('purchased.stock') ? 'active' : '' }}">
                Purchases Stock
            </a>
            <a href="/transaction-ledger" class="nav-item {{ request()->routeIs('purchased.ledger') ? 'active' : '' }}">
                Purchases Transaction Ledger
            </a>
        </div>

        <div class="sidebar-section" data-allowed-roles="0,1">
            <div class="nav-label">Purchases Data</div>
            <a href="/store-transfers" class="nav-item {{ request()->routeIs('store_stock.index') ? 'active' : '' }}">
                Godown Stock Transfer
            </a>
            <a href="/store-total-stock" class="nav-item {{ request()->routeIs('store_stock.total') ? 'active' : '' }}">
                Store Total Stock
            </a>
            <a href="/store-purchase-history" class="nav-item {{ request()->routeIs('store_purchase_history.inward') ? 'active' : '' }}">
                Store Purchase History
            </a>
            <a href="/store-all-transaction" class="nav-item {{ request()->routeIs('store_all_stock.transaction') ? 'active' : '' }}">
                Stock All Transfer Transaction History
            </a>
        </div>

        <div class="sidebar-section" data-allowed-roles="0,1,4">
            <div class="nav-label">Inventory & Sales</div>
            <a href="#" class="nav-item">Purchase Entry</a>
            <a href="#" class="nav-item">Live Store Stock</a>
        </div>

    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Get the user data we saved during login
        const userData = JSON.parse(localStorage.getItem('erp_user'));
        
        if (userData && userData.role_id !== undefined) {
            const userRole = userData.role_id.toString();
            
            // 2. Loop through all restricted sidebar sections
            document.querySelectorAll('.sidebar-section').forEach(section => {
                const allowedRoles = section.getAttribute('data-allowed-roles').split(',');
                
                // 3. Hide the section if the user's role is not in the allowed list
                if (!allowedRoles.includes(userRole)) {
                    section.style.display = 'none';
                }
            });
        }
    });
</script>


<!-- <aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">SE</div> Shoe ERP System
    </div>
    <nav class="nav-menu">
        <div class="nav-label">Main Menu</div>
        <a href="#" class="nav-item {{ request()->routeIs('offline.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        
        <div class="nav-label">Master Data</div>
        <a href="#" class="nav-item {{ request()->routeIs('offline.product.*') ? 'active' : '' }}">
            Product Master
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('offline.category.*') ? 'active' : '' }}">
            Category Master
        </a>
        
        <div class="nav-label">Inventory</div>
        <a href="#" class="nav-item {{ request()->routeIs('offline.purchase.*') ? 'active' : '' }}">
            Purchase Entry
        </a>
        </nav>
</aside> -->