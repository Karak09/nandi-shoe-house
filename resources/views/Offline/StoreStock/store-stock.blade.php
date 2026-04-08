@extends('Offline.layouts.app')
@section('title', 'Godown to Store Transfer - Shoe ERP')

@push('styles')
<style>
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #f1f5f9;}
    
    .topbar { background: #ffffff; padding: 16px 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; z-index: 5; }
    .transfer-route { display: flex; align-items: center; gap: 16px; background: #f1f5f9; padding: 6px 16px; border-radius: 24px; border: 1px solid #cbd5e1; }
    .route-node { font-size: 13px; font-weight: 700; color: #0f172a; }
    .route-arrow { color: #4f46e5; font-weight: bold; }

    /* Dual-Pane Layout */
    .transfer-workspace { display: grid; grid-template-columns: 400px 1fr; height: calc(100vh - 75px); overflow: hidden; }

    /* LEFT PANE: Source Stock List */
    .source-pane { background: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
    .pane-header { padding: 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .pane-title { font-size: 14px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
    .search-input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; transition: 0.2s; }
    .search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }

    .stock-list { flex: 1; overflow-y: auto; padding: 16px; display:flex; flex-direction:column; gap:12px; }
    .stock-item { padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: 0.2s; background: #ffffff; display: flex; justify-content: space-between; align-items: center; }
    .stock-item:hover { border-color: #4f46e5; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stock-item.active { border-color: #4f46e5; box-shadow: 0 0 0 1px #4f46e5; background: #f5f3ff; }
    
    .s-name { font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px; }
    .s-meta { font-size: 11px; color: #64748b; font-family: monospace; font-weight:600;}
    .s-qty { text-align: right; }
    .s-qty-val { font-size: 18px; font-weight: 700; color: #0f172a; }
    .s-qty-uom { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; }

    /* RIGHT PANE: Transfer Manifest */
    .manifest-pane { background: #f8fafc; display: flex; flex-direction: column; overflow: hidden; }
    .manifest-scroll { flex: 1; overflow-y: auto; padding: 32px; }
    
    .config-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; border-left: 4px solid #4f46e5; display:none; }
    .empty-state { text-align:center; padding:100px 20px; color:#64748b; font-size:15px; font-weight:500;}
    
    .c-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; }
    .c-title { font-size: 18px; font-weight: 700; color: #0f172a; }
    
    .c-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .c-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
    .sec-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; color: #0f172a; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 500; color: #0f172a; outline: none; transition: 0.2s; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
    
    .input-group { display: flex; align-items: center; position: relative; }
    .input-group-text { position: absolute; left: 12px; color: #64748b; font-weight: 600; font-size: 13px; }
    .input-group .form-control { padding-left: 28px; font-family: monospace; font-weight: 600; }

    .qty-box { display: flex; align-items: center; gap: 16px; background: #e0e7ff; padding: 16px 20px; border-radius: 8px; border: 1px solid #c7d2fe; margin-bottom: 24px; }
    .qty-box input { width: 120px; font-size: 20px; font-weight: 700; text-align: center; color: #4f46e5; border-color: #4f46e5; }
    .qty-avail { font-size: 13px; color: #4338ca; font-weight: 600; }

    .manifest-footer { background: #ffffff; border-top: 1px solid #cbd5e1; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.02); }
    .totals-block { display: flex; gap: 32px; }
    .t-item { display: flex; flex-direction: column; gap: 4px; }
    .t-lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
    .t-val { font-size: 20px; font-weight: 800; color: #0f172a; font-family: monospace; }
    .btn-submit { padding: 14px 28px; background: #4f46e5; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; }
</style>
@endpush

@section('content')
<div class="main-content">
    <header class="topbar">
        <div class="transfer-route">
            <span class="route-node">🏢 Main Godown</span>
            <span class="route-arrow">➔</span>
            <select class="form-control" id="store_id" style="width: 250px; border-color: #4f46e5; font-weight: 600; padding:6px 12px;">
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="font-size: 12px; font-weight: 600; color: #64748b;">Transfer Date: <span style="font-family: monospace; color: #0f172a;">{{ date('d-M-Y') }}</span></div>
    </header>

    <div class="transfer-workspace">
        <div class="source-pane">
            <div class="pane-header">
                <div class="pane-title">Available Godown Stock</div>
                <input type="text" id="stockSearch" class="search-input" placeholder="Search by Product Name...">
            </div>
            <div class="stock-list" id="stockList">
                @foreach($godownStocks as $stock)
                <div class="stock-item" onclick="selectProduct({{ json_encode($stock) }}, this)">
                    <div>
                        <div class="s-name">{{ $stock->product->name }}</div>
                        <div class="s-meta">Batch: ({{ $stock->latest_batch }})</div>
                    </div>
                    <div class="s-qty">
                        <div class="s-qty-val">{{ number_format($stock->quantity, 0) }}</div>
                        <div class="s-qty-uom">{{ $stock->uomRelation->keyword ?? '' }}</div>
                    </div>
                </div>
                @endforeach
                
                @if($godownStocks->isEmpty())
                    <div style="text-align:center; padding:40px 20px; color:#64748b; font-size:13px; font-weight:500;">No stock available in Godown.</div>
                @endif
            </div>
        </div>

        <div class="manifest-pane">
            <div class="manifest-scroll">
                
                <div id="emptyState" class="empty-state">
                    👈 Select a product from the Godown stock list to configure the transfer.
                </div>

                <form id="transferForm" style="display:none;" novalidate>
                    <input type="hidden" id="product_id" name="product_id">
                    <input type="hidden" id="purchase_details_id" name="purchase_details_id">
                    <input type="hidden" id="batch_no" name="batch_no">
                    <input type="hidden" id="max_qty">

                    <div class="config-card" id="configCard">
                        <div class="c-header">
                            <div>
                                <div class="c-title" id="lblProductName">-</div>
                                <div style="font-family: monospace; font-size: 12px; color: #64748b; margin-top: 4px;">
                                    Batch: <span id="lblBatch"></span> | UOM: <span id="lblUom"></span>
                                </div>
                            </div>
                        </div>

                        <div class="qty-box">
                            <div>
                                <label class="form-label" style="color: #3730a3; margin-bottom: 4px;">Transfer Quantity <span style="color:red">*</span></label>
                                <input type="number" id="quantity" name="quantity" class="form-control" step="any" required oninput="calculateTotal()">
                            </div>
                            <div class="qty-avail">/ <span id="lblMaxQty">0</span> Available in Godown</div>
                        </div>

                        <div class="c-grid">
                            <div class="c-section">
                                <div class="sec-title">📦 Packaging & Segregation</div>
                                <div class="form-group">
                                    <label class="form-label">Transfer State</label>
                                    <select class="form-control" id="is_packet" name="is_packet">
                                        <option value="0">Send as Loose Items</option>
                                        <option value="1">Send as Packets (Boxed)</option>
                                    </select>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div class="form-group">
                                        <label class="form-label">Total Packs</label>
                                        <input type="number" id="no_of_pack" name="no_of_pack" class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Qty Details</label>
                                        <input type="text" id="each_pack_quantity" name="each_pack_quantity" class="form-control" placeholder="e.g. 5 Pairs/Pack">
                                    </div>
                                </div>
                            </div>

                            <div class="c-section">
                                <div class="sec-title">💰 Store Pricing Allocation</div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div class="form-group">
                                        <label class="form-label">Store MRP <span style="color:red">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" id="mrp" name="mrp" step="0.01" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Unit Price <span style="color:red">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" id="unit_price" name="unit_price" step="0.01" class="form-control" style="color: #4f46e5; border-color: #c7d2fe;" required oninput="calculateTotal()">
                                        </div>
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div class="form-group">
                                        <label class="form-label">CGST (%)</label>
                                        <input type="number" id="cgst" name="cgst" step="0.01" class="form-control" value="0" oninput="calculateTotal()">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">SGST (%)</label>
                                        <input type="number" id="sgst" name="sgst" step="0.01" class="form-control" value="0" oninput="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="manifest-footer">
                <div class="totals-block">
                    <div class="t-item">
                        <span class="t-lbl">Total Transfer Qty</span>
                        <span class="t-val" id="footQty" style="color: #4f46e5;">0.00</span>
                    </div>
                    <div class="t-item">
                        <span class="t-lbl">Total GST Amt</span>
                        <span class="t-val" id="footGst">₹ 0.00</span>
                    </div>
                    <div class="t-item">
                        <span class="t-lbl">Est. Transfer Value</span>
                        <span class="t-val" id="footTotal">₹ 0.00</span>
                    </div>
                </div>
                <button type="submit" form="transferForm" id="btnSubmit" class="btn-submit" disabled>Dispatch to Store ➔</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Search Filter for Godown Stock
    document.getElementById('stockSearch').addEventListener('input', function(e) {
        const text = e.target.value.toLowerCase();
        document.querySelectorAll('.stock-item').forEach(item => {
            const name = item.querySelector('.s-name').innerText.toLowerCase();
            item.style.display = name.includes(text) ? 'flex' : 'none';
        });
    });

    // Load Product Data into Right Pane
    window.selectProduct = function(stock, element) {
        document.querySelectorAll('.stock-item').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('transferForm').style.display = 'block';
        document.getElementById('configCard').style.display = 'block';
        document.getElementById('btnSubmit').disabled = false;

        document.getElementById('product_id').value = stock.product_id;
        document.getElementById('purchase_details_id').value = stock.purchase_details_id;
        document.getElementById('batch_no').value = stock.latest_batch;
        document.getElementById('max_qty').value = stock.quantity;
        
        document.getElementById('lblProductName').innerText = stock.product ? stock.product.name : 'Unknown';
        document.getElementById('lblBatch').innerText = stock.latest_batch;
        document.getElementById('lblUom').innerText = stock.uom_relation ? stock.uom_relation.keyword : '-';
        document.getElementById('lblMaxQty').innerText = parseFloat(stock.quantity).toFixed(2);
        
        // Reset Inputs
        document.getElementById('quantity').value = '';
        document.getElementById('quantity').max = stock.quantity;
        document.getElementById('mrp').value = '';
        document.getElementById('unit_price').value = '';
        
        calculateTotal();
    };

    // Auto Calculate Totals
    window.calculateTotal = function() {
        let qty = parseFloat(document.getElementById('quantity').value) || 0;
        let maxQty = parseFloat(document.getElementById('max_qty').value) || 0;
        let price = parseFloat(document.getElementById('unit_price').value) || 0;
        let cgst = parseFloat(document.getElementById('cgst').value) || 0;
        let sgst = parseFloat(document.getElementById('sgst').value) || 0;

        // Prevent exceeding available stock visually
        if(qty > maxQty) {
            document.getElementById('quantity').style.borderColor = 'red';
            document.getElementById('quantity').style.color = 'red';
        } else {
            document.getElementById('quantity').style.borderColor = '#4f46e5';
            document.getElementById('quantity').style.color = '#4f46e5';
        }

        let baseTotal = qty * price;
        let gstTotal = baseTotal * ((cgst + sgst) / 100);
        let finalTotal = baseTotal + gstTotal;

        document.getElementById('footQty').innerText = qty.toFixed(2);
        document.getElementById('footGst').innerText = '₹ ' + gstTotal.toFixed(2);
        document.getElementById('footTotal').innerText = '₹ ' + finalTotal.toFixed(2);
    };

    // Form Submission
    document.getElementById('transferForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let qty = parseFloat(document.getElementById('quantity').value);
        let maxQty = parseFloat(document.getElementById('max_qty').value);
        
        if(qty > maxQty) {
            toastr.error(`You cannot transfer more than the available ${maxQty} stock!`);
            return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; 
        btn.innerHTML = 'Dispatching...';

        const payload = Object.fromEntries(new FormData(this).entries());
        payload.store_id = document.getElementById('store_id').value;

        try {
            const res = await fetch('/api/store-transfers', {
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
            setTimeout(() => window.location.reload(), 1000); 
            
        } catch (error) {
            btn.disabled = false; 
            btn.innerHTML = 'Dispatch to Store ➔';
            toastr.error(error.message || 'Transfer validation failed.');
            
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let field = document.getElementById(fieldName);
                    if (field) field.style.borderColor = '#ef4444';
                }
            }
        }
    });
</script>
@endpush
@endsection