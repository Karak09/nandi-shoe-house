@extends('Offline.layouts.app')
@section('title', 'Create Requisition')
@section('page_title', 'Create Requisition')

@section('content')

    <style>
        :root { --brand-dark: #0f172a; --brand-light: #ffffff; --bg-base: #f1f5f9; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --accent: #2563eb; --success: #10b981; --danger: #ef4444; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        /* Desktop Layout Fixes */
        body { background-color: var(--bg-base); height: 100vh; overflow: hidden; }
        .pos-workspace { display: grid; grid-template-columns: 1fr 420px; height: calc(100vh - 70px); /* Adjust 70px based on your top navbar height */ overflow: hidden; }
        
        /* Left Pane */
        .catalog-section { display: flex; flex-direction: column; background: var(--bg-base); border-right: 1px solid var(--border); overflow: hidden; height: 100%; }
        .header-dropdowns { display: flex; gap: 10px; padding: 16px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); flex-wrap: wrap; }
        .header-dropdowns select { flex: 1; min-width: 200px; padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; outline: none;}
        .search-bar { padding: 16px 24px; background: var(--brand-light); border-bottom: 1px solid var(--border); }
        .search-input { width: 100%; padding: 12px; font-size: 14px; border: 1px solid var(--border); border-radius: 6px; outline: none; }
        
        /* Grid */
        .product-grid { flex: 1; padding: 24px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; align-content: flex-start; }
        .product-card { background: var(--brand-light); border: 1px solid var(--border); border-radius: 8px; padding: 12px; cursor: pointer; transition: 0.2s; position: relative;}
        .product-card:hover { border-color: var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* Image Controls */
        .p-image-container { height: 140px; background: var(--bg-base); border-radius: 6px; margin-bottom: 12px; overflow: hidden; position: relative;}
        .p-image { width: 100%; height: 100%; object-fit: contain; } /* Changed to contain so shoes aren't cut off */
        .zoom-icon { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; border-radius: 4px; padding: 4px 6px; font-size: 11px; cursor: pointer; z-index: 10;}
        .img-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; padding: 4px 8px; cursor: pointer; z-index: 10; font-size: 14px; border-radius: 4px; display: none; }
        .product-card:hover .img-nav { display: block; } /* Only show arrows on hover to keep it clean */
        .img-nav.left { left: 4px; }
        .img-nav.right { right: 4px; }
        .img-nav:hover { background: rgba(0,0,0,0.8); }
        
        .p-name { font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
        .p-code { font-size: 12px; color: var(--text-muted); margin-bottom: 2px;}
        
        /* Right Pane (Cart) - FIXED FOR SCROLLING */
        .cart-section { display: flex; flex-direction: column; background: var(--brand-light); height: 100%; overflow: hidden; }
        .cart-header { padding: 20px; border-bottom: 1px solid var(--border); background: #f8fafc; font-weight: 700; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;}
        .clear-cart { color: var(--danger); font-size: 12px; cursor: pointer; background: none; border: none; font-weight: 600;}
        .cart-items { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .cart-item { display: flex; flex-direction: column; padding-bottom: 12px; border-bottom: 1px dashed var(--border); }
        .ci-top { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .ci-name { font-size: 13px; font-weight: 600; }
        .ci-code { font-size: 11px; color: var(--text-muted); }
        .ci-bottom { display: flex; justify-content: space-between; align-items: center; }
        .qty-controls { display: flex; align-items: center; border: 1px solid var(--border); border-radius: 4px; }
        .qty-btn { width: 24px; height: 24px; background: none; border: none; cursor: pointer; }
        .qty-val { width: 40px; text-align: center; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); outline: none; font-size: 13px; font-weight: 600;}
        .btn-remove { background: none; border: none; color: var(--danger); font-size: 12px; font-weight: 600; cursor: pointer; }
        
        .checkout-area { border-top: 1px solid var(--border); padding: 20px; background: #f8fafc; flex-shrink: 0; }
        .form-control { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 10px; font-size: 13px; outline: none; }
        .btn-submit { width: 100%; padding: 14px; background: var(--success); color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Modals Fixes */
        
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid var(--accent); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 40px auto; display: none;}
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Mobile Responsiveness */
        @media (max-width: 992px) {
            body { height: auto; overflow: auto; display: block; }
            .pos-workspace { display: flex; flex-direction: column; height: auto; min-height: 100vh; }
            .catalog-section { border-right: none; height: auto; overflow: visible; }
            .product-grid { overflow: visible; padding: 16px; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
            .cart-section { height: auto; border-top: 4px solid var(--brand-dark); box-shadow: 0 -4px 12px rgba(0,0,0,0.1); }
            .cart-items { max-height: 400px; overflow-y: auto; }
            .header-dropdowns { flex-direction: column; }
            .header-dropdowns select { width: 100%; }
        }

        /* =========================
        IMAGE MODAL
        ========================= */

        .image-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999999;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: 0.3s ease;
        }

        /* Modal Image */
        .modal-image {
            width: auto;
            height: auto;
            max-width: 100vw;
            max-height: 100vh;
            object-fit: contain;
            display: block;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            background: #fff;
            padding: 8px;
        }

        /* Close Button */
        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 42px;
            color: #111;
            cursor: pointer;
            user-select: none;
            z-index: 1000000;
        }

        /* Navigation Buttons */
        .modal-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            color: #111;
            cursor: pointer;
            user-select: none;
            z-index: 1000000;
            padding: 10px;
        }

        /* Left */
        .modal-prev {
            left: 25px;
        }

        /* Right */
        .modal-next {
            right: 25px;
        }

        /* Hover */
        .modal-arrow:hover,
        .modal-close:hover {
            opacity: 0.7;
        }

        /* Mobile */
        @media(max-width:768px){
            .modal-arrow{
                font-size:40px;
            }
            .modal-close{
                font-size:32px;
                top:15px;
                right:20px;
            }
        }
    </style>

    <template id="tpl-product">
        <div class="product-card item-search">
            <div class="p-image-container">
                <div class="img-nav left" onclick="navImage(event, this, -1)">&#10094;</div>
                <img src="" class="p-image slider-img" data-images="" data-index="0">
                <div class="img-nav right" onclick="navImage(event, this, 1)">&#10095;</div>
                <div class="zoom-icon">🔍</div>
            </div>
            <div class="p-click-area" style="margin-top: 8px;">
                <div class="p-name search-target"></div>
                <div class="p-code search-target code-target"></div>
                <div class="p-code details-target"></div>
            </div>
        </div>
    </template>

    <template id="tpl-cart-item">
        <div class="cart-item">
            <div class="ci-top">
                <div>
                    <div class="ci-name"></div>
                    <div class="ci-code text-muted" style="font-size: 11px;"></div>
                </div>
                <div class="ci-price font-weight-bold" style="font-size: 14px; color: var(--accent);"></div>
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

    <main class="pos-workspace">
        <section class="catalog-section" id="catalogSection">
            @if(in_array($userTypeId, [1, 2, 3, 6, 8]))
                <div class="header-dropdowns">
                    <select id="req_type" onchange="handleLocationSelect(this.value)">
                        <option value="">Which location do you want to requisition?</option>
                        
                        <!-- Hide Godown option if Role is 6 (Purchase Entry) -->
                        @if($userTypeId != 6)
                            <option value="godown">Godown</option>
                        @endif
                        
                        <option value="store">Store</option>
                    </select>
                    
                    <select id="send_store_id" style="display: none;" onchange="handleStoreSelect(this.value)">
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
                <span>Requisition Items</span>
                <button class="clear-cart" onclick="clearCart()">Clear All 🗑️</button>
            </div>
            <div class="cart-items" id="reqItems"></div>
            <div class="checkout-area">
                <input type="hidden" id="req_store_id" value="{{ in_array($userDetailsId, [1,2,3]) ? $userStoreId : 0 }}">
                
                <div class="totals-box p-3 mb-3" style="background: #e2e8f0; border-radius: 8px;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="font-weight-bold text-muted">Total Quantity:</span>
                        <span id="displayTotalQty" class="font-weight-bold" style="font-size: 16px;">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold text-muted">Total Amount:</span>
                        <span id="displayTotalAmount" class="font-weight-bold" style="font-size: 18px; color: var(--accent);">₹ 0.00</span>
                    </div>
                </div>

                <textarea id="remarks" class="form-control" placeholder="Remarks / Notes..." rows="2"></textarea>
                <button class="btn-submit" id="btnSubmit" disabled onclick="submitRequisition()">Submit Requisition</button>
            </div>
        </section>
    </main>

    <div id="imageModal" class="image-modal">

        <span class="modal-close" onclick="closeModal()">
            ×
        </span>

        <span class="modal-arrow modal-prev" onclick="modalNav(-1)">
            ❮
        </span>

        <img id="modalImg" class="modal-image" src="">

        <span class="modal-arrow modal-next" onclick="modalNav(1)">
            ❯
        </span>

    </div>

    @push('scripts')
        <script>
            // Prevent Browser Back Button from caching the previous form state
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload(); // Force a fresh reload if returning via back button
                }
            });

            const userDetailsId = {{ $userDetailsId }};
            let reqCart = {};
            
            const userTypeId = {{ $userTypeId }};

            if([1,3,7,8].includes(userTypeId)){
                document.getElementById('searchInput').disabled = true;
            }

            function handleLocationSelect(value) {
                const dropdown = document.getElementById('send_store_id');
                document.getElementById('productGrid').innerHTML = '';
                clearCart(); 
                
                if (value === 'godown') { dropdown.style.display = 'none'; dropdown.value = ''; fetchProducts('godown'); } 
                else if (value === 'store') { dropdown.style.display = 'block'; dropdown.value = ''; document.getElementById('searchInput').disabled = true; } 
                else { dropdown.style.display = 'none'; dropdown.value = ''; document.getElementById('searchInput').disabled = true; }
            }

            function handleStoreSelect(storeId) {
                clearCart(); 
                storeId ? fetchProducts('store', storeId) : document.getElementById('productGrid').innerHTML = '';
            }

            async function fetchProducts(type, storeId = null) {
                document.getElementById('loader').style.display = 'block';
                document.getElementById('productGrid').innerHTML = '';
                
                let url = `{{ route('requisition.get_products') }}?type=${type}` + (storeId ? `&store_id=${storeId}` : '');
                
                try {
                    const response = await fetch(url);
                    const result = await response.json();
                    if(result.status === 'success') {
                        renderProductGrid(result.data);
                        document.getElementById('searchInput').disabled = false;
                    }
                } catch (e) { toastr.error('Network Error'); } 
                finally { document.getElementById('loader').style.display = 'none'; }
            }

            function renderProductGrid(products) {
                const grid = document.getElementById('productGrid');
                const template = document.getElementById('tpl-product');

                if(products.length === 0) {
                    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;">No products available.</div>';
                    return;
                }

                products.forEach(p => {
                    const clone = template.content.cloneNode(true);
                    const size = p.pro_size || 'N/A';
                    const fullName = `${p.name} (${size})`;
                    const imagesArr = p.image_array;
                    
                    const img = clone.querySelector('.slider-img');
                    img.src = imagesArr[0];
                    
                    let currentImgIndex = 0;
                    const navLeft = clone.querySelector('.img-nav.left');
                    const navRight = clone.querySelector('.img-nav.right');

                    if (imagesArr.length > 1) {
                        navLeft.onclick = (e) => { 
                            e.stopPropagation(); 
                            currentImgIndex = (currentImgIndex - 1 + imagesArr.length) % imagesArr.length;
                            img.src = imagesArr[currentImgIndex];
                        };
                        navRight.onclick = (e) => { 
                            e.stopPropagation(); 
                            currentImgIndex = (currentImgIndex + 1) % imagesArr.length;
                            img.src = imagesArr[currentImgIndex];
                        };
                    } else {
                        navLeft.remove();
                        navRight.remove();
                    }

                    clone.querySelector('.zoom-icon').onclick = (e) => {
                        e.stopPropagation();
                        openModal(imagesArr, currentImgIndex);
                    };
                    
                    // Pass PRICE into addToReq
                    clone.querySelector('.p-click-area').onclick = () => addToReq(p.id, fullName, p.product_code, p.uom_id, p.price);
                    
                    clone.querySelector('.p-name').textContent = fullName;
                    clone.querySelector('.code-target').textContent = `Code: ${p.product_code} | MRP: ₹${parseFloat(p.price).toFixed(2)}`;
                    clone.querySelector('.details-target').textContent = `UOM: ${p.uom_name} | Cat: ${p.category_name}`;
                    
                    grid.appendChild(clone);
                });
            }

            // --- CART & QUANTITY LOGIC ---
            function renderReqCart() {
                const container = document.getElementById('reqItems');
                const template = document.getElementById('tpl-cart-item');
                container.innerHTML = '';
                
                const keys = Object.keys(reqCart);
                document.getElementById('btnSubmit').disabled = keys.length === 0;
                
                let totalQty = 0;
                let totalAmount = 0.00;

                if(keys.length === 0) {
                    container.innerHTML = '<div style="text-align:center;color:#64748b;margin-top:20px;">Cart empty.</div>';
                    document.getElementById('displayTotalQty').innerText = '0';
                    document.getElementById('displayTotalAmount').innerText = '₹ 0.00';
                    return;
                }

                keys.forEach(id => {
                    const item = reqCart[id];
                    const clone = template.content.cloneNode(true);
                    
                    // Calculate totals
                    let itemTotal = item.qty * item.price;
                    totalQty += item.qty;
                    totalAmount += itemTotal;

                    clone.querySelector('.ci-name').textContent = item.name;
                    clone.querySelector('.ci-code').textContent = `Code: ${item.code}`;
                    clone.querySelector('.ci-price').textContent = `₹${itemTotal.toFixed(2)}`;
                    
                    const inputField = clone.querySelector('.qty-val');
                    inputField.value = item.qty;
                    inputField.onchange = (e) => manualQtyUpdate(e.target, id);
                    
                    clone.querySelector('.btn-minus').onclick = () => updateQty(id, -1);
                    clone.querySelector('.btn-plus').onclick = () => updateQty(id, 1);
                    clone.querySelector('.btn-remove').onclick = () => { delete reqCart[id]; renderReqCart(); };
                    
                    container.appendChild(clone);
                });

                // Update UI Totals
                document.getElementById('displayTotalQty').innerText = totalQty;
                document.getElementById('displayTotalAmount').innerText = `₹ ${totalAmount.toFixed(2)}`;
            }

            function addToReq(id, name, code, uom_id, price) {
                reqCart[id] = reqCart[id] ? { ...reqCart[id], qty: reqCart[id].qty + 1 } : { id, name, code, uom: uom_id, price: price, qty: 1 };
                renderReqCart();
            }

            function updateQty(id, change) {
                reqCart[id].qty += change;
                if (reqCart[id].qty <= 0) delete reqCart[id];
                renderReqCart();
            }

            function manualQtyUpdate(element, id) {
                let val = parseInt(element.value);
                // Strict validation: must be a number and greater than 0
                if (isNaN(val) || val <= 0) {
                    toastr.warning('Please enter a valid positive number.');
                    element.value = reqCart[id].qty; // Revert to previous safe state
                    return;
                }
                reqCart[id].qty = val;
                renderReqCart();
            }

            function clearCart() { reqCart = {}; renderReqCart(); }

            // --- MANUAL IMAGE NAVIGATION ---
            function navImage(e, btn, direction) {
                e.stopPropagation(); // Prevents click from bubbling to the cart
                const container = btn.parentElement;
                const img = container.querySelector('.slider-img');
                const images = JSON.parse(img.dataset.images);
                let idx = parseInt(img.dataset.index) + direction;
                if (idx < 0) idx = images.length - 1;
                if (idx >= images.length) idx = 0;
                img.src = images[idx];
                img.dataset.index = idx;
            }

            // --- MODAL LOGIC ---
            let currentModalImages = [];
            let currentModalIndex = 0;
            function openModal(images) {

                if (!images || images.length === 0) return;
                currentModalImages = images;
                currentModalIndex = 0;
                const modal = document.getElementById('imageModal');
                const imgEl = document.getElementById('modalImg');
                imgEl.src = currentModalImages[0];
                modal.style.display = 'flex';
            }

            function modalNav(direction) {
                if (!currentModalImages.length) return;
                currentModalIndex += direction;

                if (currentModalIndex < 0) {
                    currentModalIndex = currentModalImages.length - 1;
                }

                if (currentModalIndex >= currentModalImages.length) {
                    currentModalIndex = 0;
                }

                document.getElementById('modalImg').src =
                    currentModalImages[currentModalIndex];
            }

            function toggleModalZoom() {
                document.getElementById('modalImg').classList.toggle('zoomed');
            }

            function closeModal() {
                document.getElementById('imageModal').style.display = 'none';
            }

            document.getElementById('imageModal').addEventListener('click', function(event) {

                if (event.target.id === 'imageModal') {
                    closeModal();
                }
            });

            // --- SEARCH & SUBMIT ---
            window.divSearch = function(inputId, containerId) {
                const filter = document.getElementById(inputId).value.toLowerCase();
                const items = document.getElementById(containerId).getElementsByClassName("item-search");
                for (let i = 0; i < items.length; i++) {
                    items[i].style.display = (items[i].textContent || items[i].innerText).toLowerCase().indexOf(filter) > -1 ? "" : "none";
                }
            };

            async function submitRequisition() {
                // Determine 'where_req' dynamically from the dropdown
                const reqTypeDropdown = document.getElementById('req_type');
                let whereReq = 'Godown'; 
                if (reqTypeDropdown && reqTypeDropdown.value === 'store') whereReq = 'Store';

                // Calculate exact total amount
                let finalTotal = 0;
                Object.values(reqCart).forEach(i => finalTotal += (i.qty * i.price));

                const payload = {
                    where_req: whereReq,
                    req_store_id: document.getElementById('send_store_id') ? document.getElementById('send_store_id').value : 1, // Store ID requested from
                    total_amount: finalTotal.toFixed(2),
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
                    
                    if(res.ok && data.status === 'success') { 
                        toastr.success(data.message); 
                        clearCart(); 
                        document.getElementById('remarks').value = '';
                        // Redirect to list page after 1.5 seconds
                        setTimeout(() => {
                            window.location.href = "{{ route('requisition.list') }}";
                        }, 1500);
                    } else { 
                        toastr.error(data.message || 'Validation Failed.');
                        document.getElementById('btnSubmit').disabled = false; 
                    }
                } catch (e) { 
                    toastr.error('System error occurred while submitting.'); 
                    document.getElementById('btnSubmit').disabled = false;
                } 
                finally { 
                    document.getElementById('btnSubmit').disabled = false; 
                }
            }
            renderReqCart();
        </script>
    @endpush
@endsection