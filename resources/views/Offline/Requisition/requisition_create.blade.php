@extends('Offline.layouts.app')
@section('title', 'Create Requisition')

@section('content')
<style>
    :root { --brand-dark: #0f172a; --brand-light: #fff; --bg-base: #f1f5f9; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --border-strong: #cbd5e1; --accent: #2563eb; --success: #10b981; --danger: #ef4444; --radius: 8px; }
    .req-layout { display: grid; grid-template-columns: 1fr 380px; gap: 0; height: calc(100vh - 120px); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: var(--brand-light); }
    .catalog-section { display: flex; flex-direction: column; background: var(--bg-base); border-right: 1px solid var(--border); overflow: hidden; }
    .header-dropdowns { display: flex; gap: 10px; padding: 12px 20px; background: var(--brand-light); border-bottom: 1px solid var(--border); flex-wrap: wrap; }
    .header-dropdowns select { flex: 1; min-width: 180px; padding: 10px; border: 1px solid var(--border-strong); border-radius: var(--radius); font-size: 13px; outline: none; }
    .search-bar { padding: 12px 20px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
    .search-input { width: 100%; padding: 10px 16px; font-size: 14px; border: 2px solid var(--border-strong); border-radius: var(--radius); outline: none; transition: .2s; box-sizing: border-box; }
    .search-input:focus { border-color: var(--accent); }
    .product-grid { flex: 1; padding: 16px 20px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; align-content: flex-start; }
    .product-card { background: var(--brand-light); border: 1px solid var(--border); border-radius: 10px; padding: 12px; cursor: pointer; transition: .2s; }
    .product-card:hover { border-color: var(--accent); box-shadow: 0 4px 12px rgba(37,99,235,.08); }
    .p-image-container { height: 110px; background: var(--bg-base); border-radius: var(--radius); margin-bottom: 10px; overflow: hidden; position: relative; border: 1px dashed var(--border-strong); }
    .p-image { width: 100%; height: 100%; object-fit: contain; }
    .zoom-icon { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,.6); color: white; border-radius: 4px; padding: 3px 6px; font-size: 11px; cursor: pointer; z-index: 10; }
    .img-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,.5); color: white; padding: 3px 7px; cursor: pointer; z-index: 10; font-size: 13px; border-radius: 4px; display: none; }
    .product-card:hover .img-nav { display: block; }
    .img-nav.left { left: 4px; }
    .img-nav.right { right: 4px; }
    .p-name { font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 2px; line-height: 1.3; }
    .p-code { font-size: 11px; color: var(--text-muted); margin-bottom: 2px; }
    .p-details { font-size: 10px; color: var(--text-muted); }
    .p-color-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; border: 1px solid var(--border); vertical-align: middle; margin-right: 2px; }

    .cart-section { display: flex; flex-direction: column; background: var(--brand-light); overflow: hidden; }
    .cart-header { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .cart-header h3 { font-size: 15px; font-weight: 700; margin: 0; }
    .clear-cart { font-size: 12px; font-weight: 600; color: var(--danger); cursor: pointer; border: none; background: none; }
    .cart-items { flex: 1; overflow-y: auto; padding: 12px 20px; display: flex; flex-direction: column; gap: 10px; }
    .empty-cart { text-align: center; color: var(--text-muted); font-size: 13px; font-weight: 500; margin-top: 30px; }
    .cart-item { display: flex; flex-direction: column; padding-bottom: 12px; border-bottom: 1px dashed var(--border); }
    .ci-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
    .ci-name { font-size: 13px; font-weight: 600; color: var(--text-main); width: 65%; line-height: 1.3; }
    .ci-code { font-size: 11px; color: var(--text-muted); display: block; margin-top: 2px; }
    .ci-price { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 700; color: var(--accent); }
    .ci-bottom { display: flex; justify-content: space-between; align-items: center; }
    .qty-controls { display: flex; align-items: center; border: 1px solid var(--border-strong); border-radius: 6px; }
    .qty-btn { width: 28px; height: 28px; background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: var(--text-muted); }
    .qty-btn:hover { color: var(--text-main); background: var(--bg-base); }
    .qty-val { width: 36px; text-align: center; font-size: 14px; font-weight: 700; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); outline: none; font-family: 'JetBrains Mono', monospace; background: white; }
    .btn-remove { background: none; border: none; color: var(--danger); font-size: 12px; font-weight: 600; cursor: pointer; }
    .checkout-area { border-top: 1px solid var(--border); background: #f8fafc; padding: 14px 20px; }
    .totals-box { background: #e2e8f0; border-radius: var(--radius); padding: 12px 16px; margin-bottom: 12px; }
    .t-row { display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 4px; }
    .t-val { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
    .t-row.total { font-size: 16px; font-weight: 800; color: var(--accent); }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-strong); border-radius: var(--radius); font-size: 13px; outline: none; box-sizing: border-box; }
    .form-control:focus { border-color: var(--accent); }
    .btn-submit { width: 100%; padding: 14px; background: var(--success); color: white; border: none; border-radius: var(--radius); font-size: 15px; font-weight: 700; cursor: pointer; transition: .2s; }
    .btn-submit:hover { background: #059669; }
    .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
    .loader { border: 3px solid #f3f3f3; border-top: 3px solid var(--accent); border-radius: 50%; width: 32px; height: 32px; animation: spin .8s linear infinite; margin: 30px auto; display: none; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .image-modal { display: none; position: fixed; inset: 0; z-index: 999999; justify-content: center; align-items: center; background: rgba(0,0,0,.6); backdrop-filter: blur(4px); }
    .modal-image { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,.3); background: white; padding: 8px; }
    .modal-close { position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; z-index: 1000000; font-weight: bold; }
    .modal-arrow { position: absolute; top: 50%; transform: translateY(-50%); font-size: 50px; color: white; cursor: pointer; z-index: 1000000; padding: 10px; opacity: .8; }
    .modal-arrow:hover { opacity: 1; }
    .modal-prev { left: 20px; }
    .modal-next { right: 20px; }

    @media (max-width: 900px) {
        .req-layout { grid-template-columns: 1fr; height: auto; min-height: calc(100vh - 120px); }
        .cart-section { border-top: 2px solid var(--border); }
        .header-dropdowns { flex-direction: column; }
        .header-dropdowns select { width: 100%; }
    }
    @media (max-width: 480px) {
        .product-grid { grid-template-columns: repeat(2, 1fr); padding: 12px; gap: 8px; }
        .product-card { padding: 10px; }
        .p-image-container { height: 90px; }
        .p-name { font-size: 12px; }
        .header-dropdowns, .search-bar, .cart-header { padding: 10px 14px; }
        .cart-items, .checkout-area { padding: 10px 14px; }
    }
</style>

<header class="topbar">
    <h1 style="font-size:18px; font-weight:600; color:#0f172a; margin:0;">Create Requisition</h1>
</header>

<template id="tpl-product">
    <div class="product-card item-search">
        <div class="p-image-container">
            <div class="img-nav left">&#10094;</div>
            <img src="" class="p-image slider-img">
            <div class="img-nav right">&#10095;</div>
            <div class="zoom-icon">&#128269;</div>
        </div>
        <div class="p-click-area">
            <div class="p-name search-target">
                <span class="p-color-dot colour-dot" style="display:none;"></span>
                <span class="name-text"></span>
            </div>
            <div class="p-code search-target code-target"></div>
            <div class="p-details details-target"></div>
        </div>
    </div>
</template>

<template id="tpl-cart-item">
    <div class="cart-item">
        <div class="ci-top">
            <div>
                <div class="ci-name"></div>
                <div class="ci-code"></div>
            </div>
            <div class="ci-price"></div>
        </div>
        <div class="ci-bottom">
            <div class="qty-controls">
                <button class="qty-btn btn-minus">-</button>
                <input type="text" class="qty-val" value="1">
                <button class="qty-btn btn-plus">+</button>
            </div>
            <button class="btn-remove">Remove</button>
        </div>
    </div>
</template>

<div class="req-layout">
    <section class="catalog-section">
        @if(in_array($userTypeId, [1, 2, 3, 6, 8]))
        <div class="header-dropdowns">
            <select id="req_type" onchange="handleLocationSelect(this.value)">
                <option value="">Which location to requisition?</option>
                @if($userTypeId != 6)
                    <option value="godown">Godown</option>
                @endif
                <option value="store">Store</option>
            </select>
            <select id="send_store_id" style="display:none;" onchange="handleStoreSelect(this.value)">
                <option value="">Select Sending Store</option>
                @foreach($stores as $store)
                    @if($store->id != $userStoreId)
                        <option value="{{ $store->id }}">{{ $store->store_name ?? $store->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        @endif

        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="Search Product..." onkeyup="divSearch('searchInput', 'productGrid')" disabled>
        </div>
        <div id="loader" class="loader"></div>
        <div class="product-grid" id="productGrid"></div>
    </section>

    <section class="cart-section">
        <div class="cart-header">
            <h3>Requisition Items</h3>
            <button class="clear-cart" onclick="clearCart()">Clear All</button>
        </div>
        <div class="cart-items" id="reqItems"></div>
        <div class="checkout-area">
            <div class="totals-box">
                <div class="t-row">
                    <span>Total Products:</span>
                    <span class="t-val" id="displayTotalProducts">0</span>
                </div>
                <div class="t-row">
                    <span>Total Quantity:</span>
                    <span class="t-val" id="displayTotalQty">0</span>
                </div>
                <div class="t-row total">
                    <span>Total Amount:</span>
                    <span class="t-val" id="displayTotalAmount">₹ 0.00</span>
                </div>
            </div>
            <textarea id="remarks" class="form-control" placeholder="Remarks / Notes..." rows="2"></textarea>
            <button class="btn-submit" id="btnSubmit" disabled onclick="submitRequisition()">Submit Requisition</button>
        </div>
    </section>
</div>

<div id="imageModal" class="image-modal">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <span class="modal-arrow modal-prev" onclick="modalNav(-1)">&#10094;</span>
    <img id="modalImg" class="modal-image" src="">
    <span class="modal-arrow modal-next" onclick="modalNav(1)">&#10095;</span>
</div>

@push('scripts')
<script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) window.location.reload();
    });

    const userDetailsId = {{ $userDetailsId }};
    const userTypeId = {{ $userTypeId }};
    let reqCart = {};

    if ([1,3,7,8].includes(userTypeId)) {
        document.getElementById('searchInput').disabled = true;
    }

    function handleLocationSelect(value) {
        document.getElementById('productGrid').innerHTML = '';
        clearCart();
        const dd = document.getElementById('send_store_id');
        if (value === 'godown') { dd.style.display = 'none'; dd.value = ''; fetchProducts('godown'); }
        else if (value === 'store') { dd.style.display = 'block'; dd.value = ''; document.getElementById('searchInput').disabled = true; }
        else { dd.style.display = 'none'; dd.value = ''; document.getElementById('searchInput').disabled = true; }
    }

    function handleStoreSelect(storeId) {
        clearCart();
        storeId ? fetchProducts('store', storeId) : document.getElementById('productGrid').innerHTML = '';
    }

    async function fetchProducts(type, storeId = null) {
        document.getElementById('loader').style.display = 'block';
        document.getElementById('productGrid').innerHTML = '';
        try {
            const r = await fetch(`{{ route('requisition.get_products') }}?type=${type}` + (storeId ? `&store_id=${storeId}` : ''));
            const result = await r.json();
            if (result.status === 'success') { renderProductGrid(result.data); document.getElementById('searchInput').disabled = false; }
        } catch (e) { toastr.error('Network Error'); }
        finally { document.getElementById('loader').style.display = 'none'; }
    }

    function renderProductGrid(products) {
        const grid = document.getElementById('productGrid');
        const template = document.getElementById('tpl-product');
        if (products.length === 0) { grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);">No products available.</div>'; return; }
        products.forEach(p => {
            const clone = template.content.cloneNode(true);
            const size = p.pro_size || 'N/A';
            const fullName = `${p.name} (${size})`;
            const imagesArr = p.image_array;
            const img = clone.querySelector('.slider-img');
            img.src = imagesArr[0];
            let idx = 0;

            if (imagesArr.length > 1) {
                clone.querySelector('.img-nav.left').onclick = (e) => { e.stopPropagation(); idx = (idx - 1 + imagesArr.length) % imagesArr.length; img.src = imagesArr[idx]; };
                clone.querySelector('.img-nav.right').onclick = (e) => { e.stopPropagation(); idx = (idx + 1) % imagesArr.length; img.src = imagesArr[idx]; };
            } else {
                clone.querySelector('.img-nav.left').remove();
                clone.querySelector('.img-nav.right').remove();
            }

            clone.querySelector('.zoom-icon').onclick = (e) => { e.stopPropagation(); openModal(imagesArr, idx); };
            clone.querySelector('.p-click-area').onclick = () => addToReq(p.id, fullName, p.product_code, p.uom_id, p.price);
            clone.querySelector('.name-text').textContent = fullName;
            if (p.colour_name) {
                const dot = clone.querySelector('.colour-dot');
                dot.style.display = 'inline-block';
                dot.style.background = p.colour_name;
                clone.querySelector('.name-text').textContent = fullName + ' (' + p.colour_name + ')';
            }
            clone.querySelector('.code-target').textContent = `Code: ${p.product_code} | MRP: ₹${parseFloat(p.price).toFixed(2)}`;
            clone.querySelector('.details-target').textContent = `UOM: ${p.uom_name}`;
            grid.appendChild(clone);
        });
    }

    function renderReqCart() {
        const container = document.getElementById('reqItems');
        const template = document.getElementById('tpl-cart-item');
        container.innerHTML = '';
        const keys = Object.keys(reqCart);
        document.getElementById('btnSubmit').disabled = keys.length === 0;
        let totalQty = 0, totalAmount = 0;
        if (keys.length === 0) {
            container.innerHTML = '<div class="empty-cart">Cart empty.</div>';
            document.getElementById('displayTotalProducts').innerText = '0';
            document.getElementById('displayTotalQty').innerText = '0';
            document.getElementById('displayTotalAmount').innerText = '₹ 0.00';
            return;
        }
        document.getElementById('displayTotalProducts').innerText = keys.length;
        keys.forEach(id => {
            const item = reqCart[id];
            const clone = template.content.cloneNode(true);
            const t = item.qty * item.price;
            totalQty += item.qty;
            totalAmount += t;
            clone.querySelector('.ci-name').textContent = item.name;
            clone.querySelector('.ci-code').textContent = `Code: ${item.code}`;
            clone.querySelector('.ci-price').textContent = `₹${t.toFixed(2)}`;
            const inputField = clone.querySelector('.qty-val');
            inputField.value = item.qty;
            inputField.onchange = (e) => { const v = parseInt(e.target.value); if (isNaN(v) || v <= 0) { toastr.warning('Enter a valid positive number.'); e.target.value = reqCart[id].qty; return; } reqCart[id].qty = v; renderReqCart(); };
            clone.querySelector('.btn-minus').onclick = () => updateQty(id, -1);
            clone.querySelector('.btn-plus').onclick = () => updateQty(id, 1);
            clone.querySelector('.btn-remove').onclick = () => { delete reqCart[id]; renderReqCart(); };
            container.appendChild(clone);
        });
        document.getElementById('displayTotalQty').innerText = totalQty;
        document.getElementById('displayTotalAmount').innerText = `₹ ${totalAmount.toFixed(2)}`;
    }

    function addToReq(id, name, code, uom_id, price) {
        reqCart[id] = reqCart[id] ? { ...reqCart[id], qty: reqCart[id].qty + 1 } : { id, name, code, uom: uom_id, price: parseFloat(price), qty: 1 };
        renderReqCart();
    }

    function updateQty(id, change) {
        reqCart[id].qty += change;
        if (reqCart[id].qty <= 0) delete reqCart[id];
        renderReqCart();
    }

    function clearCart() { reqCart = {}; renderReqCart(); }

    let modalImages = [], modalIdx = 0;
    function openModal(images, index) {
        if (!images || !images.length) return;
        modalImages = images; modalIdx = index;
        document.getElementById('modalImg').src = images[index];
        document.getElementById('imageModal').style.display = 'flex';
    }
    function modalNav(dir) {
        if (!modalImages.length) return;
        modalIdx = (modalIdx + dir + modalImages.length) % modalImages.length;
        document.getElementById('modalImg').src = modalImages[modalIdx];
    }
    function closeModal() { document.getElementById('imageModal').style.display = 'none'; }
    document.getElementById('imageModal').addEventListener('click', function(e) { if (e.target.id === 'imageModal') closeModal(); });

    window.divSearch = function(inputId, containerId) {
        const f = document.getElementById(inputId).value.toLowerCase();
        Array.from(document.getElementById(containerId).getElementsByClassName('item-search')).forEach(el => {
            el.style.display = el.textContent.toLowerCase().indexOf(f) > -1 ? '' : 'none';
        });
    };

    async function submitRequisition() {
        const whereReq = document.getElementById('req_type') && document.getElementById('req_type').value === 'store' ? 'Store' : 'Godown';
        let total = 0;
        Object.values(reqCart).forEach(i => total += (i.qty * i.price));
        const payload = {
            where_req: whereReq,
            req_store_id: document.getElementById('send_store_id') ? document.getElementById('send_store_id').value : 1,
            total_amount: total.toFixed(2),
            remarks: document.getElementById('remarks').value,
            items: Object.values(reqCart).map(i => ({ product_id: i.id, qty: i.qty, uom: i.uom, price: i.price }))
        };
        document.getElementById('btnSubmit').disabled = true;
        try {
            const res = await fetch("{{ route('requisition.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                toastr.success(data.message); clearCart(); document.getElementById('remarks').value = '';
                setTimeout(() => window.location.href = "{{ route('requisition.list') }}", 1500);
            } else { toastr.error(data.message || 'Validation Failed.'); document.getElementById('btnSubmit').disabled = false; }
        } catch (e) { toastr.error('System error occurred.'); document.getElementById('btnSubmit').disabled = false; }
    }

    renderReqCart();
</script>
@endpush
@endsection