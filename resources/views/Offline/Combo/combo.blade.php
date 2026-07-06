@extends('Offline.layouts.app')
@section('title', 'Combo Builder - Shoe ERP')
@section('content')

<style>
    .builder-grid { 
        display: grid; 
        grid-template-columns: 1.4fr 0.1fr 1fr; 
        gap: 2rem; 
        align-items: start; 
        max-width: 1300px;
        margin: 0 auto;
    }

    .glass-card { 
        background: #ffffff; 
        border: 1px solid #e4e4e7; 
        border-radius: 12px; 
        padding: 1.5rem; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    #ingredient_container, .bundle-config-area {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 8px;
        margin-top: 10px;
        -webkit-overflow-scrolling: touch;
    }

    #ingredient_container::-webkit-scrollbar, .bundle-config-area::-webkit-scrollbar {
        width: 6px;
    }
    #ingredient_container::-webkit-scrollbar-thumb, .bundle-config-area::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .store-selection-card { 
        background: #f8fafc; 
        padding: 1rem; 
        border-radius: 8px; 
        border: 1px solid #e2e8f0; 
        margin-bottom: 1.5rem;
    }

    .ingredient-row {
        display: flex; 
        align-items: center; 
        justify-content: space-between;
        background: #f8fafc; 
        border: 1px solid #e2e8f0; 
        padding: 12px 16px;
        border-radius: 8px; 
        margin-bottom: 10px; 
        gap: 15px;
        width: 100%;
        box-sizing: border-box;
    }
    .item-info { flex: 2; }
    .item-name-text { font-weight: 700; font-size: 14px; color: #1e293b; }
    .item-stock-text { font-size: 11px; color: #64748b; margin-top: 2px; }
    
    .qty-control-group { display: flex; align-items: center; gap: 10px; flex: 1; justify-content: flex-end; }
    .qty-input { width: 80px; text-align: center; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px; font-weight: 600; }
    .uom-label-text { font-size: 12px; font-weight: 700; color: #64748b; min-width: 45px; }

    .pricing-summary-box { 
        background: #f8fafc; 
        padding: 1.2rem; 
        border-radius: 10px; 
        margin-top: 1.5rem; 
        border: 1px solid #e2e8f0; 
    }
    .total-price-input { font-size: 20px; font-weight: 700; color: #0f172a; }

    .section-label { font-size: 11px; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .remove-btn { color: #ef4444; cursor: pointer; border: none; background: none; font-size: 1.5rem; line-height: 1; font-weight: bold; }
    .builder-arrow { display: flex; justify-content: center; align-items: center; font-size: 30px; color: #94a3b8; }

    .btn-submit-combo { 
        width: 100%; 
        margin-top: 1.5rem; 
        padding: 16px; 
        background: #0f172a; 
        color: white; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        cursor: pointer; 
        transition: 0.2s;
    }
    .btn-submit-combo:hover { background: #1e293b; }

    #freeze-overlay { 
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(255,255,255,0.8); z-index: 9999; flex-direction: column; 
        align-items: center; justify-content: center; backdrop-filter: blur(4px); 
    }

    .form-control,
    select,
    input {
        max-width: 100%;
        box-sizing: border-box;
    }

    @media (max-width: 1024px) { 
        .builder-grid { grid-template-columns: 1fr; gap: 1rem; } 
        .builder-arrow { transform: rotate(90deg); margin: 0.5rem 0; height: 40px; }
        .ingredient-row { flex-direction: column; align-items: flex-start; position: relative; }
        .qty-control-group { width: 100%; justify-content: space-between; }
        .remove-btn { position: absolute; top: 10px; right: 10px; }
    }

    @media (max-width: 480px) {
        .glass-card { padding: 1rem; }
        .btn-submit-combo { padding: 14px; font-size: 14px; }
        .item-name-text { font-size: 13px; }
        .qty-input { height: 40px; }
    }
</style>

<div id="freeze-overlay">
    <div style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 10px;">🖨️ Processing Combo...</div>
    <p style="color: #64748b;">Please wait while we open the print window and redirect you.</p>
</div>

<header class="topbar" style="gap:16px; flex-wrap:wrap;">
    <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin:0;">Combo Product Create</h1>
</header>

<form id="comboForm" class="builder-grid">
    @csrf
    <div class="glass-card">
        <h3 style="margin-bottom: 1.5rem;">1. Select Store Products</h3>
        
        <div class="store-selection-card">
            <label class="section-label">Choose Store: <span style="color:red">*</span></label>
            <select id="store_id" class="form-control" style="font-weight: 600;">
                <option value="">Select the Store</option>
                @foreach($stores as $s)
                    <option value="{{ $s->id }}">{{ $s->store_name ?? $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="section-label">Choose Products: <span style="color:red">*</span></label>
            <select id="product_search" class="form-control" style="height: 45px; border: 2px solid #e2e8f0;">
                <option value="">Search products in this store...</option>
            </select>
        </div>

        <div id="ingredient_container">
            <div id="placeholder_text" style="text-align: center; padding: 40px; color: #94a3b8; border: 2px dashed #e2e8f0; border-radius: 10px;">
                Choose a store and add products to start.
            </div>
        </div>

        <button type="button" id="finalizeBtn" class="btn-submit-combo" style="display:none; background:#2563eb;">
            Finalize & Proceed to Pricing
        </button>
    </div>

    <div class="builder-arrow">➜</div>

    <div class="glass-card" style="border-top: 4px solid #0f172a;">
        <h3 style="margin-bottom: 1.5rem;">2. Final Combo Products</h3>
        
        <div class="bundle-config-area">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="section-label">Final Combo Product: <span style="color:red">*</span></label>
                <select name="combo_product_id" id="combo_product_id" class="form-control" style="font-weight: 600;" disabled>
                    <option value="">-- Choose Target --</option>
                    @foreach($all_products as $prod)
                        @php
                            $colour = $prod->colourRelation->colour_name ?? '';
                            $size = $prod->pro_size ?? '';
                            $attrs = collect([$colour, $size])->filter()->implode(' | ');
                        @endphp
                        <option value="{{ $prod->id }}">{{ $prod->name }} {{ $attrs ? '(' . $attrs . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label class="section-label">Combo Qty: <span style="color:red">*</span></label>
                    <input type="text" name="bundle_qty" class="form-control int-only" disabled>
                </div>
                <div>
                    <label class="section-label">Combo UOM: <span style="color:red">*</span></label>
                    <select name="bundle_uom" id="bundle_uom" class="form-control" disabled>
                        <option value="">Select product uom</option>
                        @foreach($units as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
                    </select>
                </div>
            </div>

            <div class="pricing-summary-box">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px;">
                    <div>
                        <label class="section-label">Unit Price: <span style="color:red">*</span></label>
                        <input type="text" name="unit_price" class="form-control int-only" disabled>
                    </div>
                    <div>
                        <label class="section-label">GST %</label>
                        <input type="text" name="gst_rate" class="form-control int-only" disabled>
                    </div>
                </div>
                <div>
                    <label class="section-label">Total Packet Price: <span style="color:red">*</span></label>
                    <input type="text" name="combo_price" class="form-control int-only total-price-input" disabled>
                </div>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn-submit-combo" disabled>
            Create Combo & Update Inventory
        </button>
    </div>
</form>

<template id="ingredient-row-template">
    <div class="ingredient-row">
        <div class="item-info">
            <div class="item-name-text"></div>
            <div class="item-attrs" style="display:flex; gap:4px; flex-wrap:wrap; margin-top:2px;"></div>
            <div class="item-stock-text"></div>
            <input type="hidden" class="hidden-id">
            <input type="hidden" class="hidden-uom">
        </div>
        <div class="qty-control-group">
            <input type="text" class="qty-input int-only" placeholder="Qty">
            <span class="uom-label-text"></span>
        </div>
        <button type="button" class="remove-btn">×</button>
    </div>
</template>

@push('scripts')
<script>
    const storeSelector = document.getElementById('store_id');
    const productSearch = document.getElementById('product_search');
    const container = document.getElementById('ingredient_container');
    const template = document.getElementById('ingredient-row-template');
    const freezeOverlay = document.getElementById('freeze-overlay');
    const finalizeBtn = document.getElementById('finalizeBtn');
    let isFinalized = false;

    // Positive Integers Only logic
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('int-only')) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value === '0') e.target.value = '';
        }
    });

    // Load store products
    async function loadProducts() {
        const storeId = storeSelector.value;
        if (!storeId) {
            productSearch.innerHTML = '<option value="">Search products in this store...</option>';
            return;
        }

        try {
            const res = await fetch(`get-store-products/${storeId}`);
            const products = await res.json();
            
            let html = '<option value="">Search products in this store...</option>';
            products.forEach(item => {
                const uomName = item.uom_relation ? item.uom_relation.name : 'Units';
                const colour = item.product?.colour_relation?.colour_name || '';
                const size = item.product?.pro_size || '';
                const attrs = [colour, size].filter(Boolean).join(' | ');
                html += `<option value="${item.product_id}" data-name="${item.product?.name}" data-uom="${uomName}" data-uom-id="${item.uom}" data-stock="${item.quantity}" data-colour="${colour}" data-size="${size}">
                            ${item.product?.name} ${attrs ? '(' + attrs + ')' : ''} (Available: ${item.quantity} ${uomName})
                         </option>`;
            });
            productSearch.innerHTML = html;
        } catch (e) { toastr.error("Failed to load inventory."); }
    }

    function toggleFinalizeBtn() {
        const rows = document.querySelectorAll('.ingredient-row');
        if (rows.length > 0 && !isFinalized) {
            finalizeBtn.style.display = 'block';
        } else {
            finalizeBtn.style.display = 'none';
        }
    }

    storeSelector.addEventListener('change', () => {
        container.innerHTML = '<div id="placeholder_text" style="text-align: center; padding: 40px; color: #94a3b8; border: 2px dashed #e2e8f0; border-radius: 10px;">Add ingredients.</div>';
        loadProducts();
        toggleFinalizeBtn();
    });

    // Add Ingredient
    productSearch.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        if (document.getElementById(`row_${opt.value}`)) {
            toastr.warning("Product already in the list."); return;
        }

        if (document.getElementById('placeholder_text')) document.getElementById('placeholder_text').remove();

        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.ingredient-row');
        row.id = `row_${opt.value}`;
        
        row.querySelector('.item-name-text').innerText = opt.dataset.name;
        row.querySelector('.item-stock-text').innerText = `Stock: ${opt.dataset.stock} ${opt.dataset.uom}`;
        row.querySelector('.uom-label-text').innerText = opt.dataset.uom;

        const attrsDiv = row.querySelector('.item-attrs');
        if (attrsDiv) {
            let badges = '';
            if (opt.dataset.colour) badges += `<span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${opt.dataset.colour}</span>`;
            if (opt.dataset.size) badges += `<span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${opt.dataset.size}</span>`;
            attrsDiv.innerHTML = badges;
        }
        
        row.querySelector('.hidden-id').name = `items[${opt.value}][product_id]`;
        row.querySelector('.hidden-id').value = opt.value;
        row.querySelector('.hidden-uom').name = `items[${opt.value}][uom_id]`;
        row.querySelector('.hidden-uom').value = opt.dataset.uomId;
        
        const qInput = row.querySelector('.qty-input');
        qInput.name = `items[${opt.value}][use_qty]`;
        qInput.dataset.maxQty = opt.dataset.stock;
        qInput.dataset.productName = opt.dataset.name;

        row.querySelector('.remove-btn').onclick = () => {
            if (isFinalized) return;
            row.remove();
            toggleFinalizeBtn();
        };

        container.appendChild(clone);
        this.value = "";
        toggleFinalizeBtn();
    });

    // Finalize button - lock section 1, unlock section 2
    finalizeBtn.addEventListener('click', function() {
        if (isFinalized) return;

        const ingredientRows = document.querySelectorAll('.ingredient-row');
        if (ingredientRows.length === 0) { toastr.error("Add at least one ingredient first."); return; }
        let hasQtyError = false;
        for (const row of ingredientRows) {
            const qInput = row.querySelector('.qty-input');
            const val = qInput.value;
            const name = qInput.dataset.productName || 'this product';
            const maxQty = parseInt(qInput.dataset.maxQty) || 0;
            if (!val || parseInt(val) <= 0) {
                toastr.error(`Please put quantity for "${name}".`);
                hasQtyError = true;
            } else if (parseInt(val) > maxQty) {
                toastr.error(`Insufficient stock for "${name}". Available: ${maxQty}, you entered: ${val}.`);
                hasQtyError = true;
            }
        }
        if (hasQtyError) return;

        isFinalized = true;

        // Disable section 1 (left panel) - use readonly/disabled carefully to still submit values
        storeSelector.disabled = true;
        productSearch.disabled = true;
        ingredientRows.forEach(row => {
            row.querySelector('.qty-input').readOnly = true;
            row.querySelector('.remove-btn').style.display = 'none';
        });
        finalizeBtn.style.display = 'none';

        // Enable section 2 (right panel)
        document.querySelectorAll('#combo_product_id, [name="bundle_qty"], #bundle_uom, [name="unit_price"], [name="gst_rate"], [name="combo_price"]').forEach(el => {
            el.disabled = false;
        });
        document.getElementById('submitBtn').disabled = false;

        toastr.success("Section 1 finalized. Now configure the combo pricing.");
    });

    // Form Submit & Detailed Validation
    document.getElementById('comboForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validation Checks
        if (!storeSelector.value) { toastr.error("choose store"); return; }
        const ingredientRows = document.querySelectorAll('.ingredient-row');
        if (ingredientRows.length === 0) { toastr.error("choose Select Ingredients"); return; }
        let ingQtyError = false;
        ingredientRows.forEach(row => {
            const val = row.querySelector('.qty-input').value;
            if (!val || parseInt(val) <= 0) ingQtyError = true;
        });
        if (ingQtyError) { toastr.error("Quantity not put negative or 0"); return; }
        if (!document.getElementById('combo_product_id').value) { toastr.error("choose combo product"); return; }
        const bundleQty = document.querySelector('[name="bundle_qty"]').value;
        if (!bundleQty || parseInt(bundleQty) <= 0) { toastr.error("put quantity"); return; }
        if (!document.getElementById('bundle_uom').value) { toastr.error("select uom"); return; }
        const unitPrice = document.querySelector('[name="unit_price"]').value;
        if (!unitPrice || parseInt(unitPrice) <= 0) { toastr.error("put unit price"); return; }
        const comboPrice = document.querySelector('[name="combo_price"]').value;
        if (!comboPrice || parseInt(comboPrice) <= 0) { toastr.error("put amount"); return; }

        // Execution
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        freezeOverlay.style.display = 'flex';

        const formData = new FormData(this);
        formData.set('store_id', storeSelector.value);

        try {
            const response = await fetch("{{ route('combo.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                // 1. Save data for print page
                localStorage.setItem('print_barcodes', JSON.stringify(result.print_data));
                
                // 2. Open the print page in a new window
                window.open(result.redirect_url, '_blank');

                toastr.success("Combo Created! Redirecting to list...");

                // 3. Wait 3 seconds and then redirect to the list page
                // This removes the freeze by moving the user to the next logical screen
                setTimeout(() => {
                    window.location.href = "{{ route('combo.list') }}";
                }, 3000);

            } else { throw result; }
        } catch (err) {
            freezeOverlay.style.display = 'none';
            btn.disabled = false;
            toastr.error(err.message || "An unexpected error occurred.");
        }
    });
</script>
@endpush
@endsection