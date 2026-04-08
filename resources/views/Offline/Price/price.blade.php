@extends('Offline.layouts.app')
@section('title', 'Professional Price Master - Shoe ERP')
@section('page_title', 'Price Master')
@section('content')

<div class="content-area">
    <div id="listView" class="view-section active">
        <div class="header-actions">
            <div class="page-title">
                <h1>Product Pricing</h1>
                <p>Manage MRP, discounts, and GST rates for all product variations.</p>
            </div>
            <button class="btn btn-primary" onclick="toggleView('addView', true)">+ Add New Price</button>
        </div>

        <div class="card-full">
            <table class="datatable">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th>Product & Variant</th>
                        <th>MRP</th>
                        <th>Store Sale Price</th>
                        <th>Online Price</th>
                        <th>GST %</th>
                        <th>Status</th>
                        <th data-sortable="false" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prices as $p)
                    <tr>
                        <td>
                            <div class="prod-name">{{ $p->product ? $p->product->name : 'Unknown Product' }}</div>
                            <div class="prod-size">Size: {{ $p->pro_size }} | SKU: {{ $p->product ? $p->product->sku : '-' }}</div>
                        </td>
                        <td>
                            <div class="price-text">₹ {{ number_format($p->pro_mrp_price, 2) }}</div>
                            @if($p->pro_mrp_discount > 0)
                                <div class="discount-text">-{{ number_format($p->pro_mrp_discount_percentage, 1) }}% (₹{{ number_format($p->pro_mrp_discount, 2) }})</div>
                            @endif
                        </td>
                        <td>
                            <div class="price-text" style="color: var(--primary);">₹ {{ number_format($p->pro_sale_price, 2) }}</div>
                            @if($p->pro_sale_discount > 0)
                                <div class="discount-text">Disc: ₹{{ number_format($p->pro_sale_discount, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="price-text" style="color: var(--warning);">₹ {{ number_format($p->pro_online, 2) }}</div>
                        </td>
                        <td style="font-weight:600;">{{ number_format($p->gst_rate, 1) }}%</td>
                        <td>
                            @if($p->is_active)
                                <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Active</span>
                            @else
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <button class="action-link" style="color: #059669;" onclick='viewRecord(@json($p))' title="View Details">👁️</button>
                            <button class="action-link edit-link" onclick='editRecord(@json($p))'>Edit</button>
                            <button class="action-link delete-link" style="color:#ef4444;" onclick="deleteRecord('{{ $p->encrypted_id }}')">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="addView" class="view-section">
        <div class="header-actions">
            <div class="page-title">
                <h1 id="formTitleText">Add Price Configuration</h1>
                <p>Set pricing, discounts, and taxes for a specific product size.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="toggleView('listView')">Cancel</button>
                <button type="button" id="btnSubmitTop" class="btn btn-primary" onclick="document.getElementById('priceForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Pricing</button>
            </div>
        </div>

        <form id="priceForm" novalidate>
            <input type="hidden" id="encrypted_id" name="encrypted_id">
            
            <div class="form-layout">
                <div>
                    <div class="form-card">
                        <h2 class="card-title">Product Information</h2>
                        <div class="form-group">
                            <label class="form-label">Select Product <span style="color:red">*</span></label>
                            <select id="product_id" name="product_id" class="form-control" onchange="autoFillProductDetails(this)" required>
                                <option value="">Search and select product...</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" data-size="{{ $prod->pro_size }}" data-uom="{{ $prod->uomRelation ? $prod->uomRelation->name : '' }}">{{ $prod->name }} (SKU: {{ $prod->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-label">Size (pro_size)</label>
                                <input type="number" step="any" id="pro_size" name="pro_size" class="form-control" placeholder="e.g. 9">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit Qty</label>
                                <input type="number" step="0.01" id="pro_unit" name="pro_unit" class="form-control" placeholder="1.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Per Unit Price</label>
                                <div class="input-group">
                                    <span class="input-prefix">₹</span>
                                    <input type="number" step="0.01" id="pro_per_unit_price" name="pro_per_unit_price" class="form-control has-prefix" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2 class="card-title">Offline Store Pricing</h2>
                        <div class="form-group">
                            <label class="form-label">Store Sale Price <span style="color:red">*</span></label>
                            <div class="input-group">
                                <span class="input-prefix">₹</span>
                                <input type="number" step="0.01" id="pro_sale_price" name="pro_sale_price" class="form-control has-prefix base-calc" placeholder="0.00" style="border-color: var(--primary); background: #eef2ff;" required>
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Sale Discount Amt</label>
                                <div class="input-group">
                                    <span class="input-prefix">₹</span>
                                    <input type="number" step="0.01" id="pro_sale_discount" name="pro_sale_discount" class="form-control has-prefix calc-amt" data-target="pro_sale_discount_percentage" data-base="pro_sale_price" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sale Discount %</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="pro_sale_discount_percentage" name="pro_sale_discount_percentage" class="form-control has-suffix calc-pct" data-target="pro_sale_discount" data-base="pro_sale_price" placeholder="0.00">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-card">
                        <h2 class="card-title">Online Platform Pricing</h2>
                        <div class="form-group">
                            <label class="form-label">Online Sale Price</label>
                            <div class="input-group">
                                <span class="input-prefix">₹</span>
                                <input type="number" step="0.01" id="pro_online" name="pro_online" class="form-control has-prefix base-calc" placeholder="0.00">
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Online Discount Amt</label>
                                <div class="input-group">
                                    <span class="input-prefix">₹</span>
                                    <input type="number" step="0.01" id="pro_online_discount" name="pro_online_discount" class="form-control has-prefix calc-amt" data-target="pro_online_discount_percentage" data-base="pro_online" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Online Discount %</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="pro_online_discount_percentage" name="pro_online_discount_percentage" class="form-control has-suffix calc-pct" data-target="pro_online_discount" data-base="pro_online" placeholder="0.00">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="form-card">
                        <h2 class="card-title">Maximum Retail Price (MRP)</h2>
                        <div class="form-group">
                            <label class="form-label">Base MRP Price <span style="color:red">*</span></label>
                            <div class="input-group">
                                <span class="input-prefix">₹</span>
                                <input type="number" step="0.01" id="pro_mrp_price" name="pro_mrp_price" class="form-control has-prefix base-calc" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">MRP Discount Amt</label>
                                <div class="input-group">
                                    <span class="input-prefix">₹</span>
                                    <input type="number" step="0.01" id="pro_mrp_discount" name="pro_mrp_discount" class="form-control has-prefix calc-amt" data-target="pro_mrp_discount_percentage" data-base="pro_mrp_price" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">MRP Discount %</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="pro_mrp_discount_percentage" name="pro_mrp_discount_percentage" class="form-control has-suffix calc-pct" data-target="pro_mrp_discount" data-base="pro_mrp_price" placeholder="0.00">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2 class="card-title">Taxation Rates</h2>
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="form-label">CGST Rate</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="cgst_rate" name="cgst_rate" class="form-control has-suffix gst-calc" placeholder="0.00">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">SGST Rate</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="sgst_rate" name="sgst_rate" class="form-control has-suffix gst-calc" placeholder="0.00">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total GST</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" id="gst_rate" class="form-control has-suffix" placeholder="0.00" disabled style="background: #f3f4f6; cursor:not-allowed;">
                                    <span class="input-suffix">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr style="border:0; border-top:1px solid #e5e7eb; margin: 20px 0;">
                        
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" id="is_active" name="is_active" checked style="width:16px; height:16px; cursor:pointer;">
                            <label for="is_active" class="form-label" style="margin:0; font-weight:600; color:#10b981;">Price Configuration is Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:550px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="flex-shrink: 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin:0;">Price Configuration Details</h2>
        </div>
        
        <div id="viewModalContent" style="font-size: 14px; line-height: 1.8; color: #334155; overflow-y: auto; padding-right: 8px; flex-grow: 1;"></div>
        
        <div style="flex-shrink: 0; margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-outline" style="padding: 8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- SMART AUTO-CALCULATORS ---
    
    // Auto-fill Product Size when Product Dropdown changes
    function autoFillProductDetails(selectObj) {
        if(selectObj.value) {
            const selectedOpt = selectObj.options[selectObj.selectedIndex];
            document.getElementById('pro_size').value = selectedOpt.getAttribute('data-size') || '';
        }
    }

    // Auto-calculate Total GST
    document.querySelectorAll('.gst-calc').forEach(el => {
        el.addEventListener('input', function() {
            let cgst = parseFloat(document.getElementById('cgst_rate').value) || 0;
            let sgst = parseFloat(document.getElementById('sgst_rate').value) || 0;
            document.getElementById('gst_rate').value = (cgst + sgst).toFixed(2);
        });
    });

    // Auto-calculate Discounts (Amount <-> Percentage)
    document.querySelectorAll('.calc-amt').forEach(el => {
        el.addEventListener('input', function() {
            let amt = parseFloat(this.value) || 0;
            let baseId = this.getAttribute('data-base');
            let basePrice = parseFloat(document.getElementById(baseId).value) || 0;
            let pctId = this.getAttribute('data-target');
            
            if(basePrice > 0) {
                document.getElementById(pctId).value = ((amt / basePrice) * 100).toFixed(2);
            }
        });
    });

    document.querySelectorAll('.calc-pct').forEach(el => {
        el.addEventListener('input', function() {
            let pct = parseFloat(this.value) || 0;
            let baseId = this.getAttribute('data-base');
            let basePrice = parseFloat(document.getElementById(baseId).value) || 0;
            let amtId = this.getAttribute('data-target');
            
            if(basePrice > 0) {
                document.getElementById(amtId).value = ((pct / 100) * basePrice).toFixed(2);
            }
        });
    });

    // Recalculate discounts if Base Price changes
    document.querySelectorAll('.base-calc').forEach(el => {
        el.addEventListener('input', function() {
            // Trigger the input event on the percentage field to recalculate the amount
            let baseId = this.id;
            let pctField = document.querySelector(`.calc-pct[data-base="${baseId}"]`);
            if(pctField && pctField.value) {
                pctField.dispatchEvent(new Event('input'));
            }
        });
    });


    // --- UI TOGGLE ---
    function toggleView(viewId, isReset = false) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        if(isReset) {
            document.getElementById('priceForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add Price Configuration';
            document.getElementById('btnSubmitTop').innerText = 'Save Pricing';
            window.clearFormErrors();
        }
    }

    // --- HELPER: CLEAR ERRORS ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.form-control').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('priceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true; 
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const url = encId ? `/api/prices/${encId}` : `/api/prices`;

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;

        try {
            const res = await fetch(url, {
                method: encId ? 'PUT' : 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload)
            });
            
            if (!res.ok) throw await res.json();

            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000); 
            
        } catch (error) {
            btn.disabled = false; 
            btn.innerHTML = encId ? 'Update Pricing' : 'Save Pricing';
            toastr.error(error.message || 'Please fix the errors highlighted below.');
            
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.style.borderColor = '#ef4444';
                        // Handle Input Group suffix/prefix cleanly
                        let parent = field.closest('.input-group') || field;
                        let sibling = parent.nextElementSibling;
                        if (sibling && sibling.classList.contains('custom-error-text')) sibling.remove();
                        parent.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                    }
                }
            }
        }
    });

    // --- EDIT RECORD ---
    window.editRecord = function(record) {
        toggleView('addView');
        window.clearFormErrors();
        document.getElementById('formTitleText').innerText = 'Edit Price Configuration';
        document.getElementById('btnSubmitTop').innerText = 'Update Pricing';
        
        for (const [key, value] of Object.entries(record)) {
            let el = document.getElementById(key);
            if (el && el.type !== 'checkbox') {
                el.value = value;
            }
        }
        document.getElementById('is_active').checked = record.is_active == 1;
        
        // Trigger GST calculation visually
        let cgst = document.getElementById('cgst_rate');
        if(cgst) cgst.dispatchEvent(new Event('input'));
    };

    // --- DELETE RECORD ---
    window.deleteRecord = async function(encId) {
        if(!confirm('Are you sure you want to soft delete this pricing configuration?')) return;
        try {
            const res = await fetch(`/api/prices/${encId}`, {
                method: 'DELETE',
                headers: { 
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            });
            if(!res.ok) throw await res.json();
            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000);
        } catch (error) { toastr.error('Deletion failed.'); }
    };

    // --- VIEW MODAL ---
    window.viewRecord = function(record) {
        const val = (item) => item ? item : '0.00';
        const productName = record.product ? record.product.name : 'Unknown Product';
        const sku = record.product ? record.product.sku : '-';
        
        const content = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <p style="margin:0;"><strong>Product:</strong> <br><span style="color:#0f172a; font-weight:600; font-size:16px;">${productName}</span></p>
                <p style="margin:0; text-align:right;"><strong>SKU:</strong> <br><span style="color:#64748b;">${sku}</span></p>
            </div>
            
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Base MRP</div>
                    <div style="font-size:18px; font-weight:700; color:#0f172a;">₹${val(record.pro_mrp_price)}</div>
                    <div style="font-size:12px; color:#10b981; font-weight:600;">Disc: -₹${val(record.pro_mrp_discount)} (${val(record.pro_mrp_discount_percentage)}%)</div>
                </div>
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Store Sale</div>
                    <div style="font-size:18px; font-weight:700; color:#4f46e5;">₹${val(record.pro_sale_price)}</div>
                    <div style="font-size:12px; color:#10b981; font-weight:600;">Disc: -₹${val(record.pro_sale_discount)} (${val(record.pro_sale_discount_percentage)}%)</div>
                </div>
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Online Sale</div>
                    <div style="font-size:18px; font-weight:700; color:#f59e0b;">₹${val(record.pro_online)}</div>
                    <div style="font-size:12px; color:#10b981; font-weight:600;">Disc: -₹${val(record.pro_online_discount)} (${val(record.pro_online_discount_percentage)}%)</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <p style="margin:0;"><strong>Size:</strong> <br><span style="color:#0f172a;">${val(record.pro_size)}</span></p>
                <p style="margin:0;"><strong>Unit Qty:</strong> <br><span style="color:#0f172a;">${val(record.pro_unit)}</span></p>
                <p style="margin:0;"><strong>Per Unit Price:</strong> <br><span style="color:#0f172a;">₹${val(record.pro_per_unit_price)}</span></p>
            </div>

            <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Taxation</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>CGST:</strong> <br><span style="color:#0f172a;">${val(record.cgst_rate)}%</span></p>
                <p style="margin:0;"><strong>SGST:</strong> <br><span style="color:#0f172a;">${val(record.sgst_rate)}%</span></p>
                <p style="margin:0;"><strong>Total GST:</strong> <br><span style="color:#0f172a; font-weight:600;">${val(record.gst_rate)}%</span></p>
            </div>
        `;
        
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection