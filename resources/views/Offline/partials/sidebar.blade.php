<aside class="sidebar">
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
</aside>