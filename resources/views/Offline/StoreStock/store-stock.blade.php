@extends('Offline.layouts.app')
@section('title', 'Godown Bulk Transfer - Shoe ERP')
@section('content')

<header class="topbar" style="gap:16px; flex-wrap:wrap;">
    <div class="transfer-route">
        <span class="route-node">Godown Stock</span>
        <span class="route-arrow">➔</span>
        <select class="form-control" id="store_id" style="width:250px; border-color:#4f46e5; font-weight:700; padding:8px 12px;">
            <option value="">-- Select Destination Store --</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->store_name }}</option>
            @endforeach
        </select>
    </div>
    <div style="font-size:13px; font-weight:600; color:#64748b; white-space:nowrap;">Date: <span style="font-family:monospace; color:#0f172a;">{{ date('d-M-Y') }}</span></div>
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
                        <div class="s-name">{{ $stock->product->name }} <span style="color:#64748b; font-size:11px;">({{ $stock->uom_keyword }})</span></div>
                        <div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:2px;">
                            @if($stock->colour_name)
                                <span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">{{ $stock->colour_name }}</span>
                            @endif
                            @if($stock->pro_size)
                                <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">{{ $stock->pro_size }}</span>
                            @endif
                        </div>
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


<div id="printOverlay">
    <div style="font-size:80px; margin-bottom:20px;">🖨️</div>
    <h2 style="margin-bottom:10px; font-size:32px;">Barcodes Generated!</h2>
    <p style="margin-bottom:30px; color:#cbd5e1; font-size:18px;">A new tab has opened with your scannable barcodes.<br>Print them and close the tab when finished.</p>
    <button onclick="window.location.href='/store-purchase-history'" style="padding:16px 40px; background:#10b981; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:18px;">Finish & Go to Store Purchase History</button>
</div>

<div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center; padding:16px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px; border-radius:12px; max-width:900px; width:100%; max-height:90vh; display:flex; flex-direction:column;">
        <h2 style="font-size:20px; font-weight:700; border-bottom:2px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
            <span>Confirm Transfer Details</span>
            <button onclick="closeConfirmModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">&times;</button>
        </h2>
        <div id="confirmContent" style="overflow-y:auto; flex-grow:1;"></div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:16px; padding-top:12px; border-top:1px solid #e2e8f0;">
            <button onclick="closeConfirmModal()" style="padding:10px 24px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:6px; font-weight:600; cursor:pointer; font-size:14px;">Edit</button>
            <button id="btnConfirmSubmit" onclick="confirmSubmit()" style="padding:10px 24px; background:#4f46e5; color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer; font-size:14px;">Confirm & Dispatch</button>
        </div>
    </div>
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

    window.validateQty = function(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
        if (input.value !== '' && parseInt(input.value) === 0) {
            input.value = '';
        }
    };

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
            product_size: stock.pro_size || '',
            product_colour: stock.colour_name || '',
            
            quantity: '',
            uom: stock.uomRelation ? stock.uomRelation.id : '',
            uom_keyword: stock.uom_keyword || '',
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

            html += `
            <div class="m-item expanded" id="manifest-item-${item.stock_id}">
                <div class="m-header" onclick="toggleAccordion(${item.stock_id})">
                    <div>
                        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">${item.product_name}</div>
                        <div style="display:flex; gap:4px; flex-wrap:wrap; margin-bottom:2px;">
                            ${item.product_colour ? `<span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${item.product_colour}</span>` : ''}
                            ${item.product_size ? `<span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${item.product_size}</span>` : ''}
                        </div>
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
                            <input type="text" id="input_quantity_${item.product_id}" class="form-control" inputmode="numeric" value="${item.quantity}" oninput="validateQty(this); updateItem(${item.stock_id}, 'quantity', this.value)" placeholder="Enter Qty">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit of Measure</label>
                            <div style="padding:8px 12px; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; font-weight:600; color:#0f172a; font-size:13px;">${item.uom_keyword || 'N/A'}</div>
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

    window.submitManifest = function() {
        clearFormErrors();
        const storeId = document.getElementById('store_id').value;
        if(!storeId) { toastr.error("Please select a Destination Store."); return; }

        const items = Object.values(manifestItems);
        const hasEmpty = items.some(item => !item.quantity || !item.unit_price || !item.mrp);
        if(hasEmpty) { toastr.error("Please fill all required fields (Qty, MRP, Unit Price) for every item."); return; }

        let storeName = '';
        const storeSelect = document.getElementById('store_id');
        if(storeSelect.selectedIndex > 0) {
            storeName = storeSelect.options[storeSelect.selectedIndex].text;
        }

        let rows = '';
        let grandTotal = 0;
        items.forEach((item, i) => {
            let qty = parseFloat(item.quantity) || 0;
            let total = item.itemTotal || 0;
            grandTotal += total;
            rows += `<tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:10px 8px; font-weight:600;">${i+1}</td>
                <td style="padding:10px 8px; font-weight:600;">${item.product_name}
                    <div style="font-size:11px; color:#64748b; margin-top:2px;">
                        ${item.product_colour ? `<span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${item.product_colour}</span>` : ''}
                        ${item.product_size ? `<span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${item.product_size}</span>` : ''}
                    </div>
                </td>
                <td style="padding:10px 8px;">${qty}</td>
                <td style="padding:10px 8px;">${item.uom_keyword || '-'}</td>
                <td style="padding:10px 8px;">₹${parseFloat(item.mrp).toFixed(2)}</td>
                <td style="padding:10px 8px;">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="padding:10px 8px;">${item.cgst}% + ${item.sgst}%</td>
                <td style="padding:10px 8px; font-weight:700;">₹${total.toFixed(2)}</td>
            </tr>`;
        });

        const html = `
            <div style="background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:16px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Destination Store</div>
                    <div style="font-size:16px; font-weight:600; color:#4f46e5;">${storeName}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Total Items</div>
                    <div style="font-size:16px; font-weight:600; color:#0f172a;">${items.length}</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">#</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">Product</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">Qty</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">UOM</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">MRP</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">Unit Price</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">GST</th>
                        <th style="padding:10px 8px; border-bottom:2px solid #cbd5e1;">Total</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;">
                        <td colspan="7" style="padding:12px 8px; font-weight:700; text-align:right; border-top:2px solid #cbd5e1;">Grand Total</td>
                        <td style="padding:12px 8px; font-weight:800; font-size:16px; color:#4f46e5; border-top:2px solid #cbd5e1;">₹${grandTotal.toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
            </div>
        `;
        document.getElementById('confirmContent').innerHTML = html;
        document.getElementById('confirmModal').style.display = 'flex';
    };

    window.closeConfirmModal = function() {
        document.getElementById('confirmModal').style.display = 'none';
    };

    window.confirmSubmit = async function() {
        document.getElementById('confirmModal').style.display = 'none';
        const btn = document.getElementById('btnConfirmSubmit');
        btn.disabled = true;
        btn.innerHTML = 'Dispatching...';

        const storeId = document.getElementById('store_id').value;
        const items = Object.values(manifestItems);
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
            
            localStorage.setItem('print_barcodes', JSON.stringify(data.barcodes));
            document.getElementById('printOverlay').style.display = 'flex';
            let printWin = window.open('{{ route('store_stock.print_barcodes') }}', '_blank');
            if (!printWin || printWin.closed) {
                window.location.href = '/store-purchase-history';
                return;
            }
            let pollTimer = setInterval(function() {
                if (printWin.closed) {
                    clearInterval(pollTimer);
                    window.location.href = '/store-purchase-history';
                }
            }, 500);
            
        } catch (error) {
            btn.disabled = false; btn.innerHTML = 'Confirm & Dispatch';
            toastr.error(error.message || 'Transfer validation failed.');

            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    
                    if(fieldName.startsWith('products.')) {
                        let parts = fieldName.split('.');
                        let index = parseInt(parts[1]);
                        let prodId = items[index].product_id; 
                        let fieldType = parts[2]; 
                        
                        let input = document.getElementById(`input_${fieldType}_${prodId}`);
                        if(input) {
                            input.style.borderColor = '#ef4444';
                            input.insertAdjacentHTML('afterend', `<div class="error-text">${msg}</div>`);
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