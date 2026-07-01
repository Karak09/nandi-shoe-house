@extends('Offline.layouts.app')
@section('title', 'Store Sale - Shoe ERP')

@section('content')
<style>
    :root {
        --brand-dark: #0f172a; --brand-light: #fff; --bg-base: #f1f5f9;
        --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0;
        --border-strong: #cbd5e1; --accent: #2563eb; --success: #10b981;
        --radius: 8px;
    }
    .pos-layout { display: grid; grid-template-columns: 1fr 380px; gap: 0; height: calc(100vh - 120px); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: var(--brand-light); }
    .catalog-section { display: flex; flex-direction: column; background: var(--bg-base); border-right: 1px solid var(--border); overflow: hidden; }
    .catalog-header { padding: 14px 20px; background: var(--brand-light); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .catalog-header h2 { font-size: 15px; font-weight: 700; margin: 0; }
    .store-select-wrap { padding: 12px 20px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
    .search-bar { padding: 12px 20px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
    .search-input { width: 100%; padding: 10px 16px; font-size: 14px; border: 2px solid var(--border-strong); border-radius: var(--radius); outline: none; transition: .2s; box-sizing: border-box; }
    .search-input:focus { border-color: var(--accent); }
    .product-grid { flex: 1; padding: 16px 20px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; align-content: flex-start; }
    .product-card { background: var(--brand-light); border: 1px solid var(--border); border-radius: 10px; padding: 12px; cursor: pointer; transition: .2s; display: flex; flex-direction: column; position: relative; }
    .product-card:hover { border-color: var(--accent); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.08); }
    .p-image { height: 90px; background: var(--bg-base); border-radius: var(--radius); margin-bottom: 10px; border: 1px dashed var(--border-strong); background-size: contain; background-position: center; background-repeat: no-repeat; }
    .p-name { font-size: 13px; font-weight: 700; color: var(--text-main); line-height: 1.3; }
    .p-meta { font-size: 11px; color: var(--text-muted); margin: 2px 0 6px; display: flex; gap: 8px; flex-wrap: wrap; }
    .p-meta span { background: var(--bg-base); padding: 1px 6px; border-radius: 4px; }
    .p-color-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 1px solid var(--border); vertical-align: middle; margin-right: 2px; }
    .p-price { font-family: 'JetBrains Mono', monospace; font-size: 15px; font-weight: 700; color: var(--accent); margin-bottom: 8px; }
    .p-barcode { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--text-muted); margin-bottom: 6px; }
    .p-stock-info { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 8px; }
    .p-qty { font-size: 11px; font-weight: 600; color: var(--text-main); }
    .p-uom { background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; }

    .cart-section { display: flex; flex-direction: column; background: var(--brand-light); overflow: hidden; min-height: 0; }
    .cart-header { padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .cart-header h3 { font-size: 15px; font-weight: 700; margin: 0; }
    .clear-cart { font-size: 12px; font-weight: 600; color: #ef4444; cursor: pointer; border: none; background: none; }
    .cart-items { flex: 1; overflow-y: auto; padding: 12px 20px; display: flex; flex-direction: column; gap: 10px; }
    .empty-cart { text-align: center; color: var(--text-muted); font-size: 13px; font-weight: 500; margin-top: 30px; }
    .cart-item { display: flex; flex-direction: column; padding-bottom: 12px; border-bottom: 1px dashed var(--border); }
    .cart-item-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
    .ci-name { font-size: 13px; font-weight: 600; color: var(--text-main); width: 65%; line-height: 1.3; }
    .ci-meta { font-size: 11px; color: var(--text-muted); display: block; margin-top: 2px; }
    .ci-total { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 700; color: var(--text-main); }
    .cart-item-bottom { display: flex; justify-content: space-between; align-items: center; }
    .qty-controls { display: flex; align-items: center; border: 1px solid var(--border-strong); border-radius: 6px; }
    .qty-btn { width: 28px; height: 28px; background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: var(--text-muted); }
    .qty-btn:hover { color: var(--text-main); background: var(--bg-base); }
    .qty-val { width: 36px; text-align: center; font-size: 14px; font-weight: 700; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); outline: none; font-family: 'JetBrains Mono', monospace; background: white; }
    .btn-remove { background: none; border: none; color: #ef4444; font-size: 12px; font-weight: 600; cursor: pointer; }
    .checkout-area { border-top: 1px solid var(--border); background: #f8fafc; padding: 14px 20px; overflow-y: auto; }
    .totals-box { margin-bottom: 12px; }
    .t-row { display: flex; justify-content: space-between; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 4px; }
    .t-row.summary { font-size: 11px; color: var(--text-muted); padding-bottom: 6px; border-bottom: 1px dashed var(--border); margin-bottom: 6px; }
    .t-row.grand { padding-bottom: 8px; border-bottom: 2px dashed var(--border-strong); font-size: 16px; font-weight: 800; color: var(--brand-dark); }
    .t-val { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
    .customer-box { display: flex; gap: 8px; margin-bottom: 8px; }
    .customer-box .form-control { margin-bottom: 0; }
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 12px; }
    .pay-btn { padding: 8px; border: 1px solid var(--border-strong); border-radius: 6px; background: var(--brand-light); font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; text-align: center; transition: .2s; }
    .pay-btn.selected { border-color: var(--accent); background: #eff6ff; color: var(--accent); box-shadow: 0 0 0 1px var(--accent); }
    .dynamic-fields { padding: 12px; background: var(--brand-light); border: 1px solid var(--border-strong); border-radius: 6px; margin-bottom: 12px; display: none; }
    .btn-submit { width: 100%; padding: 14px; background: var(--success); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: .2s; }
    .btn-submit:hover { background: #059669; }
    .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

    .cart-badge { display: none; }
    .mobile-cart-toggle { display: none; }

    @media (max-width: 900px) {
        .pos-layout { grid-template-columns: 1fr; height: auto; min-height: calc(100vh - 120px); }
        .cart-section { border-top: 2px solid var(--border); max-height: 70vh; display: none; }
        .cart-section.open { display: flex; }
        .cart-items { flex: 1; min-height: 80px; }
        .mobile-cart-toggle {
            display: flex; align-items: center; gap: 8px;
            position: fixed; bottom: 20px; right: 20px; z-index: 999;
            background: var(--brand-dark); color: white; border: none;
            padding: 14px 20px; border-radius: 50px; font-size: 14px; font-weight: 700;
            cursor: pointer; box-shadow: 0 4px 16px rgba(0,0,0,.25);
        }
        .cart-badge {
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--accent); color: white; font-size: 11px; font-weight: 700;
            min-width: 22px; height: 22px; border-radius: 11px; padding: 0 6px;
        }
    }
    @media (max-width: 480px) {
        .product-grid { grid-template-columns: repeat(2, 1fr); padding: 12px; gap: 8px; }
        .product-card { padding: 10px; }
        .p-image { height: 75px; }
        .p-name { font-size: 12px; }
        .p-price { font-size: 13px; }
        .catalog-header, .store-select-wrap, .search-bar, .cart-header { padding: 10px 14px; }
        .cart-items, .checkout-area { padding: 10px 14px; }
        .payment-methods { grid-template-columns: 1fr; }
        .customer-box { flex-direction: column; gap: 0; }
        .customer-box .form-control { margin-bottom: 6px; }
    }
</style>

<header class="topbar">
    <h1 style="font-size:18px; font-weight:600; color:#0f172a; margin:0;">Store POS Terminal</h1>
</header>

<div class="pos-layout">
    <section class="catalog-section">
        @if(isset($stores) && count($stores) > 0)
        <div class="store-select-wrap">
            <select name="store_id" id="store_id" class="form-control" style="margin-bottom:0;">
                <option value="">-- Choose Store --</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>
        @else
            <input type="hidden" id="store_id" value="{{ $user->store_id }}">
        @endif
        <div class="search-bar">
            <input type="text" id="searchInput" class="search-input" placeholder="Search name or barcode..." autofocus>
        </div>
        <div class="product-grid" id="productGrid"></div>
    </section>

    <section class="cart-section" id="cartSection">
        <div class="cart-header">
            <h3>Current Sale <span class="cart-badge" id="cartBadge">0</span></h3>
            <button class="clear-cart" onclick="clearCart()">Clear All</button>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="empty-cart">Cart is empty.</div>
        </div>
        <div class="checkout-area">
            <div class="totals-box">
                <div class="t-row summary">
                    <span>Total Items: <strong id="totalItems">0</strong></span>
                    <span>Total Qty: <strong id="totalQty">0</strong></span>
                </div>
                <div class="t-row grand">
                    <span>Grand Total</span>
                    <span class="t-val" id="grandTotal">₹ 0.00</span>
                </div>
            </div>
            <input type="text" id="cusName" class="form-control" placeholder="Customer Name*">
            <br>
            <br>
            <div class="customer-box">
                <input type="number" id="cusPhone" class="form-control" placeholder="Mobile (10 Digits)*" style="flex:2;" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                <input type="number" id="cusAge" class="form-control" placeholder="Age*" style="flex:1;" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,3)">
            </div>
            <div class="payment-methods" id="paymentBox">
                <button class="pay-btn" data-method="1">Cash</button>
                <button class="pay-btn" data-method="2">UPI / QR</button>
                <button class="pay-btn" data-method="3">Card</button>
            </div>
            <input type="hidden" id="selectedPaymentMode" value="">
            <div id="cashFields" class="dynamic-fields">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted);">Receiving Amount*</label>
                <input type="number" id="recAmount" class="form-control" placeholder="Enter Amount Received">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted); margin-top:8px;">Refund Amount</label>
                <input type="number" id="refAmount" class="form-control" placeholder="0.00" readonly style="background:#f1f5f9; font-weight:bold;">
                <div id="cashError" style="color:#ef4444; font-size:12px; font-weight:600; margin-top:6px; display:none;">Receiving amount must be at least the Grand Total.</div>
            </div>
            <div id="upiFields" class="dynamic-fields">
                <label style="font-size:11px; font-weight:bold; color:var(--text-muted);">Transaction No (5 Digits)*</label>
                <input type="text" id="transNo" class="form-control" placeholder="e.g. 12345" maxlength="5" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,5)">
                <div id="transError" style="color:#ef4444; font-size:12px; font-weight:600; margin-top:6px; display:none;">Transaction No must be exactly 5 digits.</div>
            </div>
            <button class="btn-submit" id="btnSubmit" disabled onclick="submitSale()">Complete Sale</button>
        </div>
    </section>
</div>

<button class="mobile-cart-toggle" id="mobileCartToggle" onclick="toggleCart()">
    <span>View Cart</span>
    <span class="cart-badge" id="mobileCartBadge">0</span>
</button>

@push('scripts')
<script>
    toastr.options = { "positionClass": "toast-top-right", "timeOut": "3000" };

    let allStoreProducts = @json($productsData);
    let cart = {};
    let cartOpen = false;

    function toggleCart() {
        const el = document.getElementById('cartSection');
        cartOpen = !cartOpen;
        el.classList.toggle('open', cartOpen);
        document.getElementById('mobileCartToggle').innerHTML = cartOpen
            ? '<span>Hide Cart</span>'
            : '<span>View Cart</span><span class="cart-badge" id="mobileCartBadge">0</span>';
        if (cartOpen) updateMobileBadge();
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (allStoreProducts.length > 0) renderProductGrid(allStoreProducts);

        document.getElementById('searchInput').addEventListener('input', function() {
            const k = this.value.trim().toLowerCase();
            renderProductGrid(allStoreProducts.filter(p => (p.name && p.name.toLowerCase().includes(k)) || (p.barcode && p.barcode.toLowerCase().includes(k))));
        });

        const storeSelect = document.getElementById('store_id');
        if (storeSelect && storeSelect.tagName === 'SELECT') {
            storeSelect.addEventListener('change', function() {
                const sid = this.value;
                if (!sid) { allStoreProducts = []; renderProductGrid([]); return; }
                fetch(`{{ url('/store-sale/get-store-products') }}/${sid}`, { headers: { 'Accept': 'application/json' } })
                .then(async r => { if (!r.ok) throw new Error(await r.text()); return r.json(); })
                .then(r => { if (r.status === 'success') { allStoreProducts = r.data; renderProductGrid(allStoreProducts); clearCart(); } })
                .catch(() => toastr.error("Failed to load store data."));
            });
        }

        document.querySelectorAll('.pay-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                const m = parseInt(this.dataset.method);
                document.getElementById('selectedPaymentMode').value = m;
                document.getElementById('cashFields').style.display = m === 1 ? 'block' : 'none';
                document.getElementById('upiFields').style.display = m >= 2 ? 'block' : 'none';
                if (m !== 1) { document.getElementById('recAmount').value = ''; document.getElementById('refAmount').value = ''; }
                if (m < 2) { document.getElementById('transNo').value = ''; document.getElementById('transError').style.display = 'none'; }
                validateSubmitReady();
            });
        });

        document.getElementById('recAmount').addEventListener('input', function() {
            const g = parseFloat(document.getElementById('grandTotal').innerText.replace('₹ ', '')) || 0;
            const r = parseFloat(this.value) || 0;
            document.getElementById('refAmount').value = r > g ? (r - g).toFixed(2) : '0.00';
            validateSubmitReady();
        });

        document.getElementById('transNo').addEventListener('input', function() {
            document.getElementById('transError').style.display = 'none';
            validateSubmitReady();
        });
    });

    function validateSubmitReady() {
        const mode = parseInt(document.getElementById('selectedPaymentMode').value);
        const grand = parseFloat(document.getElementById('grandTotal').innerText.replace('₹ ', '')) || 0;
        const rec = parseFloat(document.getElementById('recAmount').value) || 0;
        const transNo = document.getElementById('transNo').value.trim();
        const cashError = document.getElementById('cashError');
        const transError = document.getElementById('transError');
        const submitBtn = document.getElementById('btnSubmit');

        let blocked = false;

        // Cash validation
        if (mode === 1 && grand > 0 && rec < grand) {
            cashError.style.display = 'block';
            blocked = true;
        } else {
            cashError.style.display = 'none';
        }

        // UPI/Card transaction number validation
        if ((mode === 2 || mode === 3) && transNo.length !== 5) {
            transError.style.display = transNo.length > 0 ? 'block' : 'none';
            blocked = true;
        } else {
            transError.style.display = 'none';
        }

        submitBtn.disabled = blocked || Object.keys(cart).length === 0;
    }

    function renderProductGrid(products) {
        const grid = document.getElementById('productGrid');
        if (products.length === 0) {
            grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:40px; color:var(--text-muted);">No active stock found.</div>';
            return;
        }
        grid.innerHTML = products.map(p => {
            const bg = p.image ? `background-image: url('${p.image}');` : '';
            const meta = [];
            if (p.product_code) meta.push(`Code: ${p.product_code}`);
            if (p.colour_name) meta.push(`<span class="p-color-dot" style="background:${p.colour_name.toLowerCase()}"></span>${p.colour_name}`);
            const metaHtml = meta.length ? `<div class="p-meta">${meta.join(' | ')}</div>` : '';
            return `<div class="product-card" onclick='addCartObj(${JSON.stringify(p).replace(/'/g, "\\'")})'>
                <div class="p-image" style="${bg}"></div>
                <div class="p-name">${p.name}</div>
                ${metaHtml}
                <div class="p-barcode">Barcode: ${p.barcode || 'N/A'}</div>
                <div class="p-price">₹ ${parseFloat(p.price).toFixed(2)}</div>
                <div class="p-stock-info">
                    <span class="p-qty">Stock: ${p.stock}</span>
                    <span class="p-uom">${p.uom_name}</span>
                </div>
            </div>`;
        }).join('');
    }

    function addCartObj(product) {
        const id = String(product.cart_id);
        if (cart[id]) {
            if (cart[id].qty < product.stock) cart[id].qty += 1;
            else { toastr.warning('Max stock reached for this barcode!'); return; }
        } else {
            cart[id] = {
                id: product.id, pure_name: product.pure_name, name: product.name,
                product_code: product.product_code, colour_name: product.colour_name,
                barcode: product.barcode, price: parseFloat(product.price),
                mrp: parseFloat(product.mrp), cat_id: product.cat_id,
                uom_id: product.uom_id, uom_name: product.uom_name,
                qty: 1, maxStock: parseInt(product.stock)
            };
        }
        renderCart();
        if (window.innerWidth <= 900 && !cartOpen) toggleCart();
    }

    function updateQty(id, change) {
        if (!cart[id]) return;
        const n = cart[id].qty + change;
        if (n > cart[id].maxStock) { toastr.warning('Cannot exceed available stock!'); return; }
        cart[id].qty = Math.max(0, n);
        if (cart[id].qty <= 0) delete cart[id];
        renderCart();
    }

    function removeItem(id) { delete cart[String(id)]; renderCart(); }
    function clearCart() { cart = {}; renderCart(); }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const submitBtn = document.getElementById('btnSubmit');
        let html = '', grandTotal = 0, totalQty = 0, totalItems = 0;

        if (Object.keys(cart).length === 0) {
            submitBtn.disabled = true;
            container.innerHTML = '<div class="empty-cart">Cart is empty.</div>';
        } else {
            submitBtn.disabled = false;
            totalItems = Object.keys(cart).length;
            Object.keys(cart).forEach(id => {
                const item = cart[id];
                const t = item.qty * item.price;
                grandTotal += t;
                totalQty += item.qty;
                const ciMeta = [item.product_code, item.colour_name].filter(Boolean).join(' | ');
                html += `<div class="cart-item">
                    <div class="cart-item-top">
                        <div class="ci-name">${item.pure_name}${ciMeta ? `<span class="ci-meta">${ciMeta}</span>` : ''}</div>
                        <div class="ci-total">₹ ${t.toFixed(2)}</div>
                    </div>
                    <div class="cart-item-bottom">
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateQty('${id}', -1)">-</button>
                            <input type="text" class="qty-val" value="${item.qty}" readonly>
                            <button class="qty-btn" onclick="updateQty('${id}', 1)">+</button>
                        </div>
                        <button class="btn-remove" onclick="removeItem('${id}')">Remove</button>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        document.getElementById('grandTotal').innerText = `₹ ${grandTotal.toFixed(2)}`;
        document.getElementById('totalItems').innerText = totalItems;
        document.getElementById('totalQty').innerText = totalQty;

        const badge = document.getElementById('cartBadge');
        const mobileBadge = document.getElementById('mobileCartBadge');
        if (badge) badge.innerText = totalItems;
        if (mobileBadge) mobileBadge.innerText = totalItems;

        const rec = parseFloat(document.getElementById('recAmount').value) || 0;
        document.getElementById('refAmount').value = rec > grandTotal ? (rec - grandTotal).toFixed(2) : '0.00';
        validateSubmitReady();
    }

    function updateMobileBadge() {
        const badge = document.getElementById('mobileCartBadge');
        if (badge) badge.innerText = document.getElementById('cartBadge').innerText;
    }

    function submitSale() {
        const storeId = document.getElementById('store_id').value;
        const cusName = document.getElementById('cusName').value.trim();
        const cusPhone = document.getElementById('cusPhone').value.trim();
        const cusAge = document.getElementById('cusAge').value.trim();
        const payMode = document.getElementById('selectedPaymentMode').value;
        const recAmt = document.getElementById('recAmount').value.trim();
        const refAmt = document.getElementById('refAmount').value.trim();
        const transNo = document.getElementById('transNo').value.trim();
        const submitBtn = document.getElementById('btnSubmit');
        const grandText = document.getElementById('grandTotal').innerText.replace('₹ ', '');
        const grandTotal = parseFloat(grandText) || 0;

        if (!storeId) { toastr.error('Select an Operating Store first.'); return; }
        if (!cusName) { toastr.error('Customer Name is required.'); return; }
        if (cusPhone.length !== 10) { toastr.error('Mobile Number must be exactly 10 digits.'); return; }
        if (!cusAge) { toastr.error('Customer Age is required.'); return; }
        if (!payMode) { toastr.error('Select a Payment Method.'); return; }
        if (payMode == '1') {
            if (!recAmt) { toastr.error('Receiving Amount is mandatory for Cash.'); return; }
            if (parseFloat(recAmt) < grandTotal) { toastr.error('Receiving amount must be at least the Grand Total.'); return; }
        }
        if ((payMode == '2' || payMode == '3') && transNo.length !== 5) { toastr.error('Transaction No must be exactly 5 digits.'); return; }

        submitBtn.disabled = true;
        submitBtn.innerText = 'Processing...';

        fetch("{{ route('store.sale.checkout') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({
                store_id: storeId, customer_name: cusName, customer_phone: cusPhone, customer_age: cusAge,
                payment_mode: payMode, recived_money: recAmt || null, refund_money: refAmt || null,
                transaction_no: transNo || null, cart: Object.values(cart)
            })
        })
        .then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d.message); return d; })
        .then(d => {
            toastr.success('Sale Completed Successfully!');
            window.open(`{{ url('/store-sale/print-bill') }}/${d.bill_id}`, '_blank');
            setTimeout(() => window.location.reload(), 1500);
        })
        .catch(err => {
            toastr.error(err.message);
            submitBtn.disabled = false;
            submitBtn.innerText = 'Complete Sale';
        });
    }
</script>
@endpush
@endsection