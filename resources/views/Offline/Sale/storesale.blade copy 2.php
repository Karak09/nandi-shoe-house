@extends('Offline.layouts.app')
@section('title', 'Offline Store Sale')

@section('content')
<style>
    .pos-root { --brand-dark: #0f172a; --brand-light: #ffffff; --bg-base: #f1f5f9; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --border-strong: #cbd5e1; --accent: #2563eb; --success: #10b981; --radius-md: 8px; --radius-lg: 12px; }
    .pos-wrapper { display: flex; height: calc(100vh - 60px); background-color: var(--bg-base); color: var(--text-main); font-family: 'Inter', sans-serif; overflow: hidden; margin: -1rem; }
    .sidebar-pos { width: 80px; background: var(--brand-dark); display: flex; flex-direction: column; align-items: center; padding-top: 20px; z-index: 10; border-right: 1px solid #1e293b; }
    .brand-icon { width: 44px; height: 44px; background: var(--brand-light); color: var(--brand-dark); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; margin-bottom: 32px; }
    .nav-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 8px; color: #94a3b8; transition: 0.2s; cursor: pointer; font-size: 20px; }
    .nav-icon.active { background: var(--accent); color: white; }
    
    .pos-workspace { flex: 1; display: grid; grid-template-columns: 1fr 420px; height: 100%; overflow: hidden; }
    .catalog-section { display: flex; flex-direction: column; background: var(--bg-base); border-right: 1px solid var(--border); overflow: hidden; }
    .catalog-header { padding: 20px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .search-bar { padding: 16px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
    .search-input { width: 100%; padding: 14px 20px; font-size: 15px; border: 2px solid var(--border-strong); border-radius: var(--radius-md); font-family: 'JetBrains Mono', monospace; outline: none; }
    .product-grid { flex: 1; padding: 24px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; align-content: flex-start; }
    .product-card { background: var(--brand-light); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px; cursor: pointer; transition: 0.2s; display: flex; flex-direction: column; }
    .product-card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.1); }
    .p-image-placeholder { height: 120px; background: var(--bg-base); border-radius: var(--radius-md); margin-bottom: 12px; border: 1px dashed var(--border-strong); background-size: contain; background-position: center; background-repeat: no-repeat; }
    .p-name { font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
    .p-price { font-family: 'JetBrains Mono', monospace; font-size: 16px; font-weight: 700; color: var(--accent); margin-bottom: 4px; }
    .p-barcode { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); margin-bottom: 12px; }
    .p-stock-info { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px; font-size: 12px; font-weight: 600; }
    
    .cart-section { display: flex; flex-direction: column; background: var(--brand-light); overflow: hidden; }
    .cart-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .clear-cart { font-size: 12px; font-weight: 600; color: #ef4444; cursor: pointer; border: none; background: none; }
    .cart-items { flex: 1; overflow-y: auto; padding: 16px 24px; display: flex; flex-direction: column; gap: 12px; }
    .empty-cart { text-align: center; color: var(--text-muted); font-size: 14px; margin-top: 40px; }
    
    .cart-item { display: flex; flex-direction: column; padding-bottom: 16px; border-bottom: 1px dashed var(--border); }
    .cart-item-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .ci-name { font-size: 14px; font-weight: 600; color: var(--text-main); width: 70%; line-height: 1.3; }
    .ci-total { font-family: 'JetBrains Mono', monospace; font-size: 15px; font-weight: 700; }
    .qty-controls { display: flex; align-items: center; border: 1px solid var(--border-strong); border-radius: 6px; }
    .qty-btn { width: 28px; height: 28px; background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; }
    .qty-val { width: 40px; text-align: center; font-size: 14px; font-weight: 700; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); outline: none; }
    
    .checkout-area { border-top: 1px solid var(--border); background: #f8fafc; padding: 20px 24px; }
    .t-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
    .t-row.grand { padding-bottom: 12px; border-bottom: 2px dashed var(--border-strong); font-size: 18px; font-weight: 800; color: var(--brand-dark); }
    
    .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-strong); border-radius: 6px; font-size: 13px; margin-bottom: 8px; outline: none; }
    .form-control.error { border-color: #ef4444; background: #fef2f2; }
    
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 16px; }
    .pay-btn { padding: 10px; border: 1px solid var(--border-strong); border-radius: 6px; background: var(--brand-light); font-size: 12px; font-weight: 600; cursor: pointer; }
    .pay-btn.selected { border-color: var(--accent); background: #eff6ff; color: var(--accent); box-shadow: 0 0 0 1px var(--accent); }
    
    .btn-submit { width: 100%; padding: 18px; background: var(--success); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn-submit:disabled { background: var(--border-strong); cursor: not-allowed; }
</style>

<div class="pos-root">
    <div class="pos-wrapper">
        <main class="pos-workspace">
            <section class="catalog-section">
                <!-- Dropdown for Admins -->
                @if(isset($stores) && count($stores) > 0)
                <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--brand-light);">
                    <label style="font-size:12px; font-weight:600; display:block; margin-bottom:6px;">Select Operating Store</label>
                    <select id="store_id" class="form-control" style="margin-bottom:0;">
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
                    <input type="text" id="searchInput" class="search-input" placeholder="Search product name strictly..." autofocus>
                </div>
                <div class="product-grid" id="productGrid"></div>
            </section>

            <section class="cart-section">
                <div class="cart-header">
                    <div style="font-size: 16px; font-weight: 700;">Current Sale</div>
                    <button class="clear-cart" onclick="clearCart()">Clear All</button>
                </div>
                <div class="cart-items" id="cartItems"></div>

                <div class="checkout-area">
                    <div class="t-row">
                        <span>Items Count</span>
                        <span class="t-val" id="totalItems">0</span>
                    </div>

                    <div class="t-row grand">
                        <span>Grand Total</span>
                        <span id="grandTotal">₹ 0.00</span>
                    </div>

                    <!-- Customer Block -->
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="cusPhone" class="form-control" placeholder="Mobile (10 Digits)*" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                        <input type="text" id="cusAge" class="form-control" placeholder="Age*" maxlength="3" style="width: 80px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                    <input type="text" id="cusName" class="form-control" placeholder="Customer Name*">

                    <!-- Payment Box -->
                    <div class="payment-methods">
                        <button class="pay-btn" onclick="selectPayment(1)">💵 Cash</button>
                        <button class="pay-btn" onclick="selectPayment(2)">📱 UPI / QR</button>
                        <button class="pay-btn" onclick="selectPayment(3)">💳 Card</button>
                    </div>
                    <input type="hidden" id="selectedPaymentMode" value="">

                    <!-- Dynamic Fields -->
                    <div id="cashFields" style="display:none; gap:8px; margin-bottom:10px;">
                        <input type="text" id="recvAmount" placeholder="Receiving Amount*" class="form-control" oninput="this.value=this.value.replace(/[^0-9]/g,''); calcRefund();">
                        <input type="text" id="refundAmount" placeholder="Refund" class="form-control" readonly style="background:#f1f5f9;">
                    </div>
                    <div id="upiFields" style="display:none; margin-bottom:10px;">
                        <input type="text" id="transNo" placeholder="Transaction No (Exactly 5 digits)*" class="form-control" maxlength="5" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>

                    <button class="btn-submit" id="btnSubmit" disabled onclick="submitSale()">Complete Sale</button>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    let allStoreProducts = @json($productsData);
    let cart = {};
    let globalTotal = 0;

    document.addEventListener("DOMContentLoaded", function() {
        if(allStoreProducts.length > 0) renderProductGrid(allStoreProducts);
        renderCart();

        // Strictly Name Search
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const keyword = this.value.trim().toLowerCase();
            const filteredProducts = allStoreProducts.filter(p => p.display_name && p.display_name.toLowerCase().includes(keyword));
            renderProductGrid(filteredProducts);
        });

        const storeSelect = document.getElementById('store_id');
        if (storeSelect && storeSelect.tagName === 'SELECT') {
            storeSelect.addEventListener('change', function () {
                let storeId = this.value;
                if(!storeId) { allStoreProducts = []; renderProductGrid([]); return; }

                fetch(`{{ url('/store-sale/get-store-products') }}/${storeId}`, { headers: { 'Accept': 'application/json' } })
                .then(async res => { if (!res.ok) throw new Error("Error fetching data"); return res.json(); })
                .then(res => {
                    if(res.status === 'success') {
                        allStoreProducts = res.data;
                        renderProductGrid(allStoreProducts);
                        clearCart();
                    }
                }).catch(err => alert("Failed to fetch store products."));
            });
        }
    });

    function renderProductGrid(products) {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';
        if (products.length === 0) { grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; color:red; margin-top:20px;">No stock found.</div>`; return; }

        products.forEach(p => {
            let bgImg = p.image ? `background-image: url('${p.image}');` : '';
            let safeObj = JSON.stringify(p).replace(/'/g, "\\'"); 
            grid.innerHTML += `
                <div class="product-card" onclick='addCartObj(${safeObj})'>
                    <div class="p-image-placeholder" style="${bgImg}"></div>
                    <div class="p-name">${p.display_name}</div>
                    <div class="p-price">₹ ${parseFloat(p.price).toFixed(2)}</div>
                    <div class="p-barcode">|||| Barcode: ${p.barcode || 'N/A'}</div>
                    <div class="p-stock-info">
                        <span class="p-qty">Stock: ${p.stock}</span>
                        <span class="p-uom">${p.uom_name}</span>
                    </div>
                </div>
            `;
        });
    }

    function addCartObj(product) {
        let id = String(product.cart_id);
        if (cart[id]) {
            if (cart[id].qty < product.stock) cart[id].qty += 1;
            else alert(`Cannot exceed available stock!`);
        } else {
            cart[id] = { ...product, qty: 1, maxStock: parseInt(product.stock) };
        }
        renderCart();
    }

    function handleManualQty(id, val) {
        let num = parseInt(val);
        if (isNaN(num) || num <= 0) num = 1;
        if (num > cart[id].maxStock) {
            alert(`Error: You entered ${num}, but available stock in store is only ${cart[id].maxStock}.`);
            num = cart[id].maxStock;
        }
        cart[id].qty = num;
        renderCart();
    }

    function updateQty(id, change) {
        let newQty = cart[id].qty + change;
        if (newQty > cart[id].maxStock) { alert('Cannot exceed available stock!'); return; }
        if (newQty <= 0) delete cart[id];
        else cart[id].qty = newQty;
        renderCart();
    }

    function removeItem(id) { delete cart[id]; renderCart(); }
    function clearCart() { cart = {}; renderCart(); }

    function selectPayment(methodId) {
        document.querySelectorAll('.pay-btn').forEach((btn, idx) => {
            btn.classList.toggle('selected', idx + 1 === methodId);
        });
        document.getElementById('selectedPaymentMode').value = methodId;

        document.getElementById('cashFields').style.display = methodId === 1 ? 'flex' : 'none';
        document.getElementById('upiFields').style.display = methodId === 2 ? 'block' : 'none';
    }

    function calcRefund() {
        let recv = parseFloat(document.getElementById('recvAmount').value) || 0;
        let refund = recv - globalTotal;
        document.getElementById('refundAmount').value = refund >= 0 ? refund : 0;
    }

    function renderCart() {
        const cartContainer = document.getElementById('cartItems');
        const submitBtn = document.getElementById('btnSubmit');
        
        let html = ''; globalTotal = 0; let totalItems = 0;
        const keys = Object.keys(cart);

        if (keys.length === 0) {
            submitBtn.disabled = true;
            cartContainer.innerHTML = `<div class="empty-cart">🛒 Cart is empty.<br>Click products to add.</div>`;
        } else {
            submitBtn.disabled = false;
            keys.forEach(id => {
                let item = cart[id];
                let itemTotal = item.qty * item.price;
                totalItems += item.qty; globalTotal += itemTotal;

                html += `
                    <div class="cart-item">
                        <div class="cart-item-top">
                            <div class="ci-name">
                                ${item.display_name} <br>
                                <span style="font-size:11px; color:#64748b;">Code: ${item.barcode} | ₹${item.price.toFixed(2)} / ${item.uom_name}</span>
                            </div>
                            <div class="ci-total">₹ ${itemTotal.toFixed(2)}</div>
                        </div>
                        <div class="cart-item-bottom">
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="updateQty('${id}', -1)">-</button>
                                <!-- strict manual input validation -->
                                <input type="text" class="qty-val" value="${item.qty}" onchange="handleManualQty('${id}', this.value)">
                                <button class="qty-btn" onclick="updateQty('${id}', 1)">+</button>
                            </div>
                            <button class="btn-remove" onclick="removeItem('${id}')">Remove</button>
                        </div>
                    </div>
                `;
            });
            cartContainer.innerHTML = html;
        }
        document.getElementById('totalItems').innerText = totalItems;
        document.getElementById('grandTotal').innerText = `₹ ${globalTotal.toFixed(2)}`;
        calcRefund();
    }

    function submitSale() {
        const storeId = document.getElementById('store_id').value;
        const phone = document.getElementById('cusPhone').value.trim();
        const age = document.getElementById('cusAge').value.trim();
        const name = document.getElementById('cusName').value.trim();
        const payVal = document.getElementById('selectedPaymentMode').value;
        const submitBtn = document.getElementById('btnSubmit');

        if (!storeId) { alert('Please select a store!'); return; }

        let errors = [];
        if (phone.length !== 10) errors.push("Mobile number must be exactly 10 digits.");
        if (!age) errors.push("Age is required.");
        if (!name) errors.push("Customer Name is required.");
        if (!payVal) errors.push("Please select a payment mode.");

        if (payVal == 1 && (!document.getElementById('recvAmount').value || document.getElementById('recvAmount').value < globalTotal)) {
            errors.push("Receiving amount must be greater than or equal to Grand Total.");
        }
        if (payVal == 2 && document.getElementById('transNo').value.length !== 5) {
            errors.push("Transaction No must be exactly 5 digits.");
        }

        if (errors.length > 0) { alert(errors.join("\n")); return; }

        const payload = {
            store_id: storeId,
            customer_phone: phone,
            customer_age: age,
            customer_name: name,
            payment_mode: payVal,
            recv_amount: document.getElementById('recvAmount').value,
            refund_amount: document.getElementById('refundAmount').value,
            transaction_no: document.getElementById('transNo').value,
            cart: Object.values(cart)
        };

        submitBtn.disabled = true; submitBtn.innerText = 'Processing...';

        fetch("{{ route('store.sale.checkout') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || "Server Error");
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                // OPEN PRINT PAGE
                window.open(`{{ url('/store-sale/print-bill') }}/${data.transfer_id}`, '_blank');
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err); alert("Error: " + err.message);
            submitBtn.disabled = false; submitBtn.innerText = 'Complete Sale';
        });
    }
</script>
@endsection