@extends('Offline.layouts.app')
@section('title', 'Offline Store Sale')

@section('content')
<style>
    /* Same CSS as previously provided */
    /* .pos-root { --brand-dark: #0f172a; --brand-light: #ffffff; --bg-base: #f1f5f9; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --border-strong: #cbd5e1; --accent: #2563eb; --success: #10b981; --radius-md: 8px; --radius-lg: 12px; } */
    .pos-wrapper { display: flex; height: calc(100vh - 60px); background-color: var(--bg-base); color: var(--text-main); font-family: 'Inter', sans-serif; overflow: hidden; margin: -1rem; }
    /* .sidebar-pos { width: 80px; background: var(--brand-dark); display: flex; flex-direction: column; align-items: center; padding-top: 20px; z-index: 10; border-right: 1px solid #1e293b; } */
    /* .brand-icon { width: 44px; height: 44px; background: var(--brand-light); color: var(--brand-dark); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; margin-bottom: 32px; } */
    .nav-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 8px; color: #94a3b8; transition: 0.2s; cursor: pointer; font-size: 20px; }
    .nav-icon:hover { color: white; background: rgba(255,255,255,0.1); }
    .nav-icon.active { background: var(--accent); color: white; }
    .pos-workspace { flex: 1; display: grid; grid-template-columns: 1fr 420px; height: 100%; overflow: hidden; }
    .catalog-section { display: flex; flex-direction: column; background: var(--bg-base); border-right: 1px solid var(--border); overflow: hidden; }
    .catalog-header { padding: 20px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .header-title { font-size: 20px; font-weight: 700; color: var(--text-main); }
    .search-bar { padding: 16px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
    .search-input { width: 100%; padding: 14px 20px; font-size: 15px; border: 2px solid var(--border-strong); border-radius: var(--radius-md); font-family: 'JetBrains Mono', monospace; font-weight: 600; outline: none; transition: 0.2s; }
    .search-input:focus { border-color: var(--accent); }
    .product-grid { flex: 1; padding: 24px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; align-content: flex-start; }
    .product-card { background: var(--brand-light); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px; cursor: pointer; transition: 0.2s; display: flex; flex-direction: column; }
    .product-card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: 0 8px 16px rgba(37, 99, 235, 0.1); }
    .p-image-placeholder { height: 120px; background: var(--bg-base); border-radius: var(--radius-md); margin-bottom: 12px; border: 1px dashed var(--border-strong); background-size: cover; background-position: center; }
    .p-name { font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; line-height: 1.3; }
    .p-price { font-family: 'JetBrains Mono', monospace; font-size: 16px; font-weight: 700; color: var(--accent); margin-bottom: 4px; }
    .p-barcode { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); margin-bottom: 12px; }
    .p-stock-info { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px; }
    .p-qty { font-size: 12px; font-weight: 600; color: var(--text-main); }
    .p-uom { background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .cart-section { display: flex; flex-direction: column; background: var(--brand-light); overflow: hidden; }
    .cart-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .cart-title { font-size: 16px; font-weight: 700; color: var(--text-main); }
    .clear-cart { font-size: 12px; font-weight: 600; color: #ef4444; cursor: pointer; border: none; background: none; }
    .cart-items { flex: 1; overflow-y: auto; padding: 16px 24px; display: flex; flex-direction: column; gap: 12px; }
    .empty-cart { text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 500; margin-top: 40px; }
    .cart-item { display: flex; flex-direction: column; padding-bottom: 16px; border-bottom: 1px dashed var(--border); }
    .cart-item-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .ci-name { font-size: 14px; font-weight: 600; color: var(--text-main); width: 70%; line-height: 1.3; }
    .ci-total { font-family: 'JetBrains Mono', monospace; font-size: 15px; font-weight: 700; color: var(--text-main); }
    .cart-item-bottom { display: flex; justify-content: space-between; align-items: center; }
    .qty-controls { display: flex; align-items: center; border: 1px solid var(--border-strong); border-radius: 6px; background: var(--brand-light); }
    .qty-btn { width: 28px; height: 28px; background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: var(--text-muted); }
    .qty-btn:hover { color: var(--text-main); background: var(--bg-base); }
    .qty-val { width: 36px; text-align: center; font-size: 14px; font-weight: 700; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); outline: none; font-family: 'JetBrains Mono', monospace;}
    .btn-remove { background: none; border: none; color: #ef4444; font-size: 12px; font-weight: 600; cursor: pointer; }
    .checkout-area { border-top: 1px solid var(--border); background: #f8fafc; padding: 20px 24px; }
    .totals-box { margin-bottom: 16px; }
    .t-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
    .t-row.grand { padding-bottom: 12px; border-bottom: 2px dashed var(--border-strong); font-size: 18px; font-weight: 800; color: var(--brand-dark); }
    .t-val { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
    .customer-box { margin-bottom: 16px; }
    .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-strong); border-radius: 6px; font-size: 13px; font-weight: 500; margin-bottom: 8px; outline: none; }
    .form-control.error { border-color: #ef4444; background: #fef2f2; }
    .form-control:focus { border-color: var(--accent); }
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 16px; }
    .pay-btn { padding: 10px; border: 1px solid var(--border-strong); border-radius: 6px; background: var(--brand-light); font-size: 12px; font-weight: 600; color: var(--text-muted); cursor: pointer; text-align: center; transition: 0.2s; }
    .pay-btn.selected { border-color: var(--accent); background: #eff6ff; color: var(--accent); box-shadow: 0 0 0 1px var(--accent); }
    .pay-btn.error { border-color: #ef4444; color: #ef4444; }
    .btn-submit { width: 100%; padding: 18px; background: var(--success); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2); }
    .btn-submit:hover { background: #059669; transform: translateY(-2px); }
    .btn-submit:disabled { background: var(--border-strong); cursor: not-allowed; transform: none; box-shadow: none; }
</style>

<div class="pos-root">
    <div class="pos-wrapper">
        <main class="pos-workspace">
            <section class="catalog-section">
                <!-- Dropdown for Admins -->
                @if(isset($stores) && count($stores) > 0)
                <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: var(--brand-light);">
                    <label for="store_id" style="font-size:12px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:6px;">Select Operating Store</label>
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
                    <input type="text" id="searchInput" class="search-input" placeholder="Type name or scan barcode here..." autofocus>
                </div>

                <div class="product-grid" id="productGrid">
                    <!-- Javascript will inject cards here -->
                </div>
            </section>

            <section class="cart-section">
                <div class="cart-header">
                    <div class="cart-title">Current Sale</div>
                    <button class="clear-cart" onclick="clearCart()">Clear All</button>
                </div>

                <div class="cart-items" id="cartItems">
                    <!-- HTML Generated by JS -->
                    <div class="empty-cart">🛒 Cart is empty.<br>Scan or click products to add.</div>
                </div>

                <div class="checkout-area">
                    <div class="totals-box">
                        <div class="t-row">
                            <span>Items Count</span>
                            <span class="t-val" id="totalItems">0</span>
                        </div>
                        <div class="t-row grand">
                            <span>Grand Total</span>
                            <span class="t-val" id="grandTotal">₹ 0.00</span>
                        </div>
                    </div>

                    <div class="customer-box">
                        <input type="tel" id="cusPhone" class="form-control" placeholder="Customer Mobile (Mandatory)*">
                        <input type="text" id="cusName" class="form-control" placeholder="Customer Name (Mandatory)*">
                    </div>

                    <div class="payment-methods" id="paymentBox">
                        <button class="pay-btn" onclick="selectPayment(this, 1)">💵 Cash</button>
                        <button class="pay-btn" onclick="selectPayment(this, 2)">📱 UPI / QR</button>
                        <button class="pay-btn" onclick="selectPayment(this, 3)">💳 Card</button>
                    </div>
                    <input type="hidden" id="selectedPaymentMode" value="">

                    <button class="btn-submit" id="btnSubmit" disabled onclick="submitSale()">Complete Sale</button>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    let allStoreProducts = @json($productsData);
    let cart = {};

    document.addEventListener("DOMContentLoaded", function() {
        if(allStoreProducts.length > 0) {
            renderProductGrid(allStoreProducts);
        }

        // Search Name OR Barcode
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const keyword = this.value.trim().toLowerCase();
            const filteredProducts = allStoreProducts.filter(p => {
                const nameMatch = p.name && p.name.toLowerCase().includes(keyword);
                const codeMatch = p.barcode && String(p.barcode).toLowerCase().includes(keyword);
                return nameMatch || codeMatch;
            });
            renderProductGrid(filteredProducts);
        });

        // Store Change Fetcher
        const storeSelect = document.getElementById('store_id');
        if (storeSelect && storeSelect.tagName === 'SELECT') {
            storeSelect.addEventListener('change', function () {
                let storeId = this.value;
                if(storeId === '') {
                    allStoreProducts = [];
                    renderProductGrid([]);
                    return;
                }

                // Add Accept application/json to prevent HTML error pages on crash
                fetch(`{{ url('/store-sale/get-store-products') }}/${storeId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(async response => {
                    if (!response.ok) throw new Error(await response.text());
                    return response.json();
                })
                .then(res => {
                    if(res.status === 'success') {
                        allStoreProducts = res.data;
                        renderProductGrid(allStoreProducts);
                        clearCart();
                    } else {
                        alert(res.message);
                    }
                })
                .catch(error => {
                    console.error('Server Error:', error);
                    alert("Failed to fetch store products.");
                });
            });
        }
    });

    function renderProductGrid(products) {
        const grid = document.getElementById('productGrid');
        grid.innerHTML = '';

        if (products.length === 0) {
            grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; color:red; margin-top:20px;">No matching stock found.</div>`;
            return;
        }

        products.forEach(p => {
            let bgImg = p.image ? `background-image: url('${p.image}');` : '';
            let safeObj = JSON.stringify(p).replace(/'/g, "\\'"); // escape quotes
            
            let card = `
                <div class="product-card" onclick='addCartObj(${safeObj})'>
                    <div class="p-image-placeholder" style="${bgImg}"></div>
                    <div class="p-name">${p.name}</div>
                    <div class="p-price">₹ ${parseFloat(p.price).toFixed(2)}</div>
                    <div class="p-barcode">|||| Barcode: ${p.barcode || 'N/A'}</div>
                    <div class="p-stock-info">
                        <span class="p-qty">Stock: ${p.stock}</span>
                        <span class="p-uom">${p.uom_name}</span>
                    </div>
                </div>
            `;
            grid.innerHTML += card;
        });
    }

    function addCartObj(product) {
        let id = String(product.cart_id);

        if (cart[id]) {
            if (cart[id].qty < product.stock) {
                cart[id].qty += 1;
            } else {
                alert(`Cannot exceed available stock for barcode ${product.barcode}!`);
            }
        } else {
            cart[id] = {
                id: product.id,
                name: product.name,
                barcode: product.barcode,
                price: parseFloat(product.price),
                mrp: parseFloat(product.mrp),
                cat_id: product.cat_id,
                uom_id: product.uom_id,
                uom_name: product.uom_name,
                qty: 1,
                maxStock: parseInt(product.stock)
            };
        }
        renderCart();
    }

    function updateQty(id, change) {
        id = String(id);
        if (cart[id]) {
            let newQty = cart[id].qty + change;
            if (newQty > cart[id].maxStock) {
                alert('Cannot exceed available stock!');
                return;
            }
            cart[id].qty = newQty;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
            renderCart();
        }
    }

    function removeItem(id) {
        delete cart[String(id)];
        renderCart();
    }

    function clearCart() {
        cart = {};
        renderCart();
    }

    function selectPayment(element, methodId) {
        document.querySelectorAll('.pay-btn').forEach(btn => btn.classList.remove('selected', 'error'));
        element.classList.add('selected');
        document.getElementById('selectedPaymentMode').value = methodId;
    }

    function renderCart() {
        const cartContainer = document.getElementById('cartItems');
        const submitBtn = document.getElementById('btnSubmit');
        
        let html = '';
        let totalItems = 0;
        let grandTotal = 0;
        const keys = Object.keys(cart);

        if (keys.length === 0) {
            submitBtn.disabled = true;
            cartContainer.innerHTML = `<div class="empty-cart">🛒 Cart is empty.<br>Scan or click products to add.</div>`;
        } else {
            submitBtn.disabled = false;

            keys.forEach(id => {
                let item = cart[id];
                let itemTotal = item.qty * item.price;
                totalItems += item.qty;
                grandTotal += itemTotal;

                html += `
                    <div class="cart-item">
                        <div class="cart-item-top">
                            <div class="ci-name">
                                ${item.name} <br>
                                <span style="font-size:11px; color:#64748b;">Code: ${item.barcode} | ₹${item.price.toFixed(2)} / ${item.uom_name}</span>
                            </div>
                            <div class="ci-total">₹ ${itemTotal.toFixed(2)}</div>
                        </div>
                        <div class="cart-item-bottom">
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="updateQty('${id}', -1)">-</button>
                                <input type="text" class="qty-val" value="${item.qty}" readonly>
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
        document.getElementById('grandTotal').innerText = `₹ ${grandTotal.toFixed(2)}`;
    }

    function submitSale() {
        const storeId = document.getElementById('store_id').value;
        const nameInput = document.getElementById('cusName');
        const phoneInput = document.getElementById('cusPhone');
        const payValue = document.getElementById('selectedPaymentMode').value;
        const submitBtn = document.getElementById('btnSubmit');

        if (!storeId) { alert('Please select a store first!'); return; }

        nameInput.classList.remove('error');
        phoneInput.classList.remove('error');
        document.querySelectorAll('.pay-btn').forEach(btn => btn.classList.remove('error'));

        let hasError = false;
        if (phoneInput.value.trim() === '') { phoneInput.classList.add('error'); hasError = true; }
        if (nameInput.value.trim() === '') { nameInput.classList.add('error'); hasError = true; }
        if (payValue === '') { document.querySelectorAll('.pay-btn').forEach(btn => btn.classList.add('error')); hasError = true; }

        if (hasError) {
            alert('Please fill out all mandatory customer details and select a payment method.');
            return;
        }

        const payload = {
            store_id: storeId,
            customer_name: nameInput.value.trim(),
            customer_phone: phoneInput.value.trim(),
            payment_mode: payValue,
            cart: Object.values(cart)
        };

        submitBtn.disabled = true;
        submitBtn.innerText = 'Processing...';

        fetch("{{ route('store.sale.checkout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', // CRITICAL: This forces Laravel to return JSON on crash
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            // Check if backend crashed and returned text instead of JSON
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;

            if (!res.ok) {
                const errorText = data && data.message ? data.message : await res.text();
                throw new Error(errorText);
            }
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Sale Complete! Bill No: ' + data.bill_no);
                window.location.reload();
            } else {
                alert('Error processing sale: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerText = 'Complete Sale';
            }
        })
        .catch(err => {
            console.error("Backend Error Trace:", err);
            alert("Database Error: " + err.message.substring(0, 100)); // Show short error
            submitBtn.disabled = false;
            submitBtn.innerText = 'Complete Sale';
        });
    }
</script>
@endsection