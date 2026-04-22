@extends('Offline.layouts.app')
@section('title', 'Godown Bulk Transfer - Shoe ERP')

@push('styles')
<style>
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #f8fafc;}
    .topbar { background: #ffffff; padding: 16px 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; z-index: 5; }
    .transfer-route { display: flex; align-items: center; gap: 16px; background: #f1f5f9; padding: 8px 20px; border-radius: 8px; border: 1px solid #cbd5e1; }
    
    .transfer-workspace { display: grid; grid-template-columns: 420px 1fr; height: calc(100vh - 75px); overflow: hidden; }

    /* LEFT PANE */
    .source-pane { background: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
    .pane-header { padding: 20px; border-bottom: 1px solid #e2e8f0; background: #ffffff; }
    .pane-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;}
    .search-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: 0.2s; background: #f8fafc; }
    .search-input:focus { border-color: #4f46e5; background: #ffffff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }

    .stock-list { flex: 1; overflow-y: auto; padding: 16px; display:flex; flex-direction:column; gap:12px; }
    .stock-item { padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: 0.2s; background: #ffffff; display: flex; justify-content: space-between; align-items: center; }
    .stock-item:hover { border-color: #94a3b8; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stock-item.added { border-color: #10b981; background: #f0fdf4; pointer-events: none; opacity: 0.7; }
    
    .s-name { font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px; }
    .s-meta { font-size: 11px; color: #64748b; font-family: monospace; font-weight:600;}
    .btn-add-catalog { padding: 6px 12px; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .stock-item:hover .btn-add-catalog { background: #4f46e5; color: white; border-color: #4f46e5; }
    .badge-added { padding: 6px 12px; background: #10b981; color: white; border-radius: 6px; font-size: 12px; font-weight: 700; display: none; }
    .stock-item.added .btn-add-catalog { display: none; }
    .stock-item.added .badge-added { display: inline-block; }

    /* RIGHT PANE */
    .manifest-pane { background: #f8fafc; display: flex; flex-direction: column; overflow: hidden; }
    .manifest-scroll { flex: 1; overflow-y: auto; padding: 24px 32px; display: flex; flex-direction: column; gap: 16px; }
    .manifest-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .empty-state { text-align:center; padding:100px 20px; color:#64748b; }
    
    .m-item { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; transition: 0.2s; }
    .m-item.expanded { border-color: #4f46e5; box-shadow: 0 8px 16px rgba(79, 70, 229, 0.1); }
    .m-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; background: #ffffff; }
    .m-header:hover { background: #f8fafc; }
    .m-body { display: none; padding: 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
    .expanded .m-body { display: block; }
    
    .c-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
    .c-section { display: flex; flex-direction: column; gap: 16px; }
    
    .form-group { display: flex; flex-direction: column; gap: 6px; position:relative; margin-bottom:14px;}
    .form-label { font-size: 12px; font-weight: 600; color: #334155; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 500; color: #0f172a; outline: none; background: #ffffff; transition: 0.2s; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
    .input-group { display: flex; align-items: center; position: relative; }
    .input-group span { position: absolute; left: 12px; color: #64748b; font-weight: 700; font-size: 13px; }
    .input-group .form-control { padding-left: 28px; font-family: monospace; font-weight: 600; }

    .primary-box { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #ffffff; padding: 16px; border-radius: 8px; border: 1px solid #c7d2fe; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.05); margin-bottom: 24px; }
    
    /* ABSOLUTE ERROR MAPPING */
    .error-text { position: absolute; bottom: -18px; left: 0; color: #ef4444; font-size: 11px; font-weight: 700; white-space: nowrap; }

    .manifest-footer { background: #ffffff; border-top: 1px solid #cbd5e1; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 12px rgba(0,0,0,0.03); }
    .btn-submit { padding: 16px 32px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .btn-submit:hover { background: #3730a3; }
    .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

    #printOverlay { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15, 23, 42, 0.95); z-index:99999; flex-direction:column; justify-content:center; align-items:center; color:white; text-align:center; }
</style>
@endpush

@section('content')
<div class="main-content">
    <header class="topbar">
        <div class="transfer-route">
            <span class="route-node">Godown Stock</span>
            <span class="route-arrow">➔</span>
            <select class="form-control" id="store_id" style="width: 250px; border-color: #4f46e5; font-weight: 700; padding:8px 12px;">
                <option value="">-- Select Destination Store --</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="font-size: 13px; font-weight: 600; color: #64748b;">Date: <span style="font-family: monospace; color: #0f172a;">{{ date('d-M-Y') }}</span></div>
    </header>

    <div class="transfer-workspace">
        <div class="source-pane">
            <div class="pane-header">
                <div class="pane-title">
                    Available Godown Inventory
                    <span style="background:#e2e8f0; padding:2px 8px; border-radius:12px; font-size:11px;">{{ count($godownStocks) }} Items</span>
                </div>
                <input type="text" id="stockSearch" class="search-input" placeholder="Search product name or batch...">
            </div>
            
            <div class="stock-list" id="stockList">
                @forelse($godownStocks as $stock)
                <div class="stock-item" id="stock-card-{{ $stock->id }}" data-stock="{{ json_encode($stock) }}">
                    <div>
                        <div class="s-name">{{ $stock->product->name }} <span style="color:#64748b; font-size:11px;">({{ $stock->uomRelation->keyword ?? '' }})</span></div>
                        <div class="s-meta" style="color:#0ea5e9;">Batches: {{ $stock->all_batches }}</div>
                    </div>
                    <div>
                        <div style="font-size:18px; font-weight:700; text-align:right;">{{ number_format($stock->quantity, 0) }}</div>
                        <button type="button" class="btn-add-catalog" onclick="addToManifest({{ $stock->id }})" style="margin-top:6px;">+ Add</button>
                        <span class="badge-added" style="margin-top:6px;">✓ Added</span>
                    </div>
                </div>
                @empty
                    <div style="text-align:center; padding:40px 20px; color:#64748b; font-size:13px; font-weight:500;">No stock available.</div>
                @endforelse
            </div>
        </div>

        <div class="manifest-pane">
            <div class="manifest-scroll">
                <div class="manifest-header">
                    <div style="font-size: 18px; font-weight: 700; color: #0f172a;">Transfer Manifest</div>
                    <div class="m-count" id="manifestCount">0 Items</div>
                </div>

                <div id="emptyState" class="empty-state">
                    <div style="font-size: 48px; opacity: 0.5;">🛒</div>
                    <h3>Manifest is empty</h3>
                    <p>Select products from the left panel.</p>
                </div>

                <div id="manifestContainer" style="display: flex; flex-direction: column; gap: 16px;"></div>
            </div>
            
            <div class="manifest-footer">
                <div style="display:flex; gap: 40px;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <span style="font-size:11px; font-weight:700; color:#64748b;">Total Qty</span>
                        <span id="grandQty" style="font-size:22px; font-weight:800; color:#4f46e5;">0.00</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <span style="font-size:11px; font-weight:700; color:#64748b;">Total Value</span>
                        <span id="grandTotal" style="font-size:22px; font-weight:800; color:#0f172a;">₹ 0.00</span>
                    </div>
                </div>
                <button type="button" id="btnSubmit" class="btn-submit" onclick="submitManifest()" disabled>Dispatch & Generate Barcodes ➔</button>
            </div>
        </div>
    </div>
</div>

<div id="printOverlay">
    <div style="font-size:80px; margin-bottom:20px;">🖨️</div>
    <h2 style="margin-bottom:10px; font-size:32px;">Barcodes Generated!</h2>
    <p style="margin-bottom:30px; color:#cbd5e1; font-size:18px;">A new tab has opened with your scannable barcodes.<br>Print them and close the tab when finished.</p>
    <button onclick="window.location.replace('/offline/dashboard/default')" style="padding:16px 40px; background:#10b981; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:18px;">Finish & Return to Dashboard</button>
</div>

<script> const globalUnits = @json($units); </script>

@push('scripts')
<script>
    let manifestItems = {}; 
    let rawStockData = {};  

    document.querySelectorAll('.stock-item').forEach(el => {
        let stock = JSON.parse(el.getAttribute('data-stock'));
        rawStockData[stock.id] = stock;
    });

    document.getElementById('stockSearch').addEventListener('input', function(e) {
        const text = e.target.value.toLowerCase();
        document.querySelectorAll('.stock-item').forEach(item => {
            const stock = JSON.parse(item.getAttribute('data-stock'));
            const nameMatch = stock.product.name.toLowerCase().includes(text);
            const batchMatch = stock.all_batches.toLowerCase().includes(text);
            item.style.display = (nameMatch || batchMatch) ? 'flex' : 'none';
        });
    });

    window.addToManifest = function(stockId) {
        const stock = rawStockData[stockId];
        if(manifestItems[stockId]) return;

        manifestItems[stockId] = {
            stock_id: stockId,
            product_id: stock.product_id,
            purchase_details_id: stock.purchase_details_id,
            batch_no: stock.all_batches, // Passing ALL batches
            max_qty: parseFloat(stock.quantity),
            product_name: stock.product.name,
            
            quantity: '',
            uom: stock.uomRelation ? stock.uomRelation.id : '',
            is_packet: '0',
            no_of_pack: '0',
            each_pack_quantity: '',
            mrp: '',
            unit_price: '',
            cgst: '0',
            sgst: '0',
            
            itemTotal: 0
        };

        document.getElementById(`stock-card-${stockId}`).classList.add('added');
        renderManifest();
    };

    window.removeFromManifest = function(stockId, event) {
        event.stopPropagation();
        delete manifestItems[stockId];
        document.getElementById(`stock-card-${stockId}`).classList.remove('added');
        renderManifest();
    };

    window.toggleAccordion = function(stockId) {
        document.getElementById(`manifest-item-${stockId}`).classList.toggle('expanded');
    };

    window.updateItem = function(stockId, field, value) {
        manifestItems[stockId][field] = value;
        
        let item = manifestItems[stockId];
        let qty = parseFloat(item.quantity) || 0;
        let price = parseFloat(item.unit_price) || 0;
        let cgst = parseFloat(item.cgst) || 0;
        let sgst = parseFloat(item.sgst) || 0;

        let basePrice = qty * price;
        item.itemTotal = basePrice * (1 + ((cgst + sgst) / 100));

        document.getElementById(`summary-qty-${stockId}`).innerText = qty ? qty.toFixed(2) : '0';
        document.getElementById(`summary-total-${stockId}`).innerText = '₹ ' + item.itemTotal.toFixed(2);
        
        updateGrandTotals();
    };

    function renderManifest() {
        const container = document.getElementById('manifestContainer');
        const items = Object.values(manifestItems);
        document.getElementById('manifestCount').innerText = `${items.length} Items`;

        if(items.length === 0) {
            document.getElementById('emptyState').style.display = 'block';
            container.innerHTML = '';
            document.getElementById('btnSubmit').disabled = true;
            updateGrandTotals();
            return;
        }

        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('btnSubmit').disabled = false;

        let html = '';
        items.forEach(item => {
            let uomOptions = '<option value="">Select UOM...</option>';
            globalUnits.forEach(u => {
                uomOptions += `<option value="${u.id}" ${item.uom == u.id ? 'selected' : ''}>${u.keyword}</option>`;
            });

            html += `
            <div class="m-item expanded" id="manifest-item-${item.stock_id}">
                <div class="m-header" onclick="toggleAccordion(${item.stock_id})">
                    <div>
                        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">${item.product_name}</div>
                        <div style="font-size: 12px; color: #0ea5e9; font-family: monospace;">Batches: ${item.batch_no} | Godown Max: ${item.max_qty}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 32px;">
                        <div style="display: flex; flex-direction: column; align-items: flex-end;">
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">QTY</span>
                            <span id="summary-qty-${item.stock_id}" style="font-size: 16px; font-weight: 700; color: #0f172a;">${item.quantity || '0'}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end;">
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">VALUE</span>
                            <span id="summary-total-${item.stock_id}" style="font-size: 16px; font-weight: 700; color: #0f172a;">₹ ${item.itemTotal.toFixed(2)}</span>
                        </div>
                        <div style="border-left: 1px solid #e2e8f0; padding-left: 20px; margin-left: 8px;">
                            <button type="button" onclick="removeFromManifest(${item.stock_id}, event)" style="background:none; border:none; font-size:18px; color:#ef4444; cursor:pointer;">🗑</button>
                        </div>
                    </div>
                </div>

                <div class="m-body">
                    <div class="primary-box">
                        <div class="form-group">
                            <label class="form-label">Transfer Qty <span style="color:red">*</span></label>
                            <input type="number" id="input_quantity_${item.product_id}" class="form-control" step="any" value="${item.quantity}" oninput="updateItem(${item.stock_id}, 'quantity', this.value)" placeholder="Enter Qty">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit of Measure <span style="color:red">*</span></label>
                            <select id="input_uom_${item.product_id}" class="form-control" onchange="updateItem(${item.stock_id}, 'uom', this.value)">${uomOptions}</select>
                        </div>
                    </div>

                    <div class="c-grid">
                        <div class="c-section">
                            <div class="sec-title">📦 Segregation</div>
                            <div class="form-group">
                                <select class="form-control" onchange="updateItem(${item.stock_id}, 'is_packet', this.value)">
                                    <option value="0" ${item.is_packet == '0' ? 'selected' : ''}>Loose Items</option>
                                    <option value="1" ${item.is_packet == '1' ? 'selected' : ''}>Packaged Item (Boxed)</option>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="form-group">
                                    <label class="form-label">No of Packs</label>
                                    <input type="number" class="form-control" value="${item.no_of_pack}" oninput="updateItem(${item.stock_id}, 'no_of_pack', this.value)">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Qty/Pack</label>
                                    <input type="text" class="form-control" value="${item.each_pack_quantity}" oninput="updateItem(${item.stock_id}, 'each_pack_quantity', this.value)" placeholder="e.g. 5">
                                </div>
                            </div>
                        </div>

                        <div class="c-section">
                            <div class="sec-title">💰 Store Pricing</div>
                            <div class="form-group">
                                <label class="form-label">Store MRP <span style="color:red">*</span></label>
                                <div class="input-group"><span>₹</span><input type="number" id="input_mrp_${item.product_id}" step="0.01" class="form-control" value="${item.mrp}" oninput="updateItem(${item.stock_id}, 'mrp', this.value)"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit Price <span style="color:red">*</span></label>
                                <div class="input-group"><span>₹</span><input type="number" id="input_unit_price_${item.product_id}" step="0.01" class="form-control" style="color:#4f46e5;" value="${item.unit_price}" oninput="updateItem(${item.stock_id}, 'unit_price', this.value)"></div>
                            </div>
                        </div>

                        <div class="c-section">
                            <div class="sec-title">⚖️ Taxes</div>
                            <div class="form-group">
                                <label class="form-label">CGST (%)</label>
                                <input type="number" step="0.01" class="form-control" value="${item.cgst}" oninput="updateItem(${item.stock_id}, 'cgst', this.value)">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SGST (%)</label>
                                <input type="number" step="0.01" class="form-control" value="${item.sgst}" oninput="updateItem(${item.stock_id}, 'sgst', this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        });

        container.innerHTML = html;
        updateGrandTotals();
    }

    function updateGrandTotals() {
        let gQty = 0, gTotal = 0;
        Object.values(manifestItems).forEach(item => {
            gQty += parseFloat(item.quantity) || 0;
            gTotal += item.itemTotal || 0;
        });
        document.getElementById('grandQty').innerText = gQty.toFixed(2);
        document.getElementById('grandTotal').innerText = '₹ ' + gTotal.toFixed(2);
    }

    function clearFormErrors() {
        document.querySelectorAll('.error-text').forEach(e => e.remove());
        document.querySelectorAll('.form-control').forEach(e => { e.style.borderColor = '#cbd5e1'; });
    }

    window.submitManifest = async function() {
        clearFormErrors();
        const storeId = document.getElementById('store_id').value;
        if(!storeId) { toastr.error("Please select a Destination Store."); return; }

        const items = Object.values(manifestItems);
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; 
        btn.innerHTML = 'Dispatching Manifest...';

        const payload = { store_id: storeId, products: items };

        try {
            const res = await fetch('/api/store-transfers/bulk', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok) throw data;

            toastr.success(data.message);
            
            // SHOW FREEZE SCREEN AND OPEN PRINT TAB
            localStorage.setItem('print_barcodes', JSON.stringify(data.barcodes));
            document.getElementById('printOverlay').style.display = 'flex';
            window.open('/print-barcodes', '_blank');
            
        } catch (error) {
            btn.disabled = false; btn.innerHTML = 'Dispatch & Generate Barcodes ➔';
            toastr.error(error.message || 'Transfer validation failed.');

            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    
                    // Route exact error to the specific product ID input box!
                    if(fieldName.startsWith('products.')) {
                        let parts = fieldName.split('.'); // products.0.quantity
                        let index = parseInt(parts[1]);
                        let prodId = items[index].product_id; 
                        let fieldType = parts[2]; 
                        
                        let input = document.getElementById(`input_${fieldType}_${prodId}`);
                        if(input) {
                            input.style.borderColor = '#ef4444';
                            input.insertAdjacentHTML('afterend', `<div class="error-text">${msg}</div>`);
                            
                            // Expand the accordion if it was closed to show the error
                            document.getElementById(`manifest-item-${items[index].stock_id}`).classList.add('expanded');
                        }
                    }
                }
            }
        }
    };
</script>
@endpush
@endsection