@extends('Offline.layouts.app')
@section('title', 'Professional Price Master - Shoe ERP')
@section('page_title', 'Price Master')
@section('content')
<style>
    .p-layout { height: calc(100vh - 140px); display: flex; flex-direction: column; }
    .p-layout .view-section { display: none; animation: fadeIn 0.25s ease; }
    .p-layout .view-section.active { display: flex; flex-direction: column; flex: 1; min-height: 0; }

    .p-layout .v-card { background: #fff; border-radius: 14px; border: 1px solid #e8ecf1; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; flex: 1; min-height: 0; }
    .p-layout .v-card-header { padding: 18px 24px; font-weight: 700; font-size: 15px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e8ecf1; color: #0f172a; letter-spacing: -0.2px; flex-shrink: 0; }
    .p-layout .v-card-header .v-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; margin-right: 10px; font-size: 14px; }
    .p-layout .v-card-header .v-icon.form-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
    .p-layout .v-card-header .v-icon.table-icon { background: linear-gradient(135deg, #059669, #047857); color: #fff; }

    .p-layout .v-card-body { padding: 24px; flex: 1; overflow-y: auto; }
    .p-layout .v-card-body::-webkit-scrollbar { width: 5px; }
    .p-layout .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .p-layout .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .p-layout .v-fg { margin-bottom: 18px; }
    .p-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .p-layout .v-fg label .required { color: #ef4444; }
    .p-layout .v-fg .v-input,
    .p-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .p-layout .v-fg .v-input:focus,
    .p-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .p-layout .v-fg .v-input:hover,
    .p-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }
    .p-layout .v-fg .v-input:read-only { background: #f1f5f9; cursor: not-allowed; }
    .p-layout .v-fg .v-input.prefix { padding-left: 28px; }
    .p-layout .v-input-wrap { position: relative; }
    .p-layout .v-input-wrap .v-prefix { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #64748b; font-weight: 600; pointer-events: none; z-index: 1; }
    .p-layout .v-input-wrap .v-suffix { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #64748b; font-weight: 600; pointer-events: none; z-index: 1; }
    .p-layout .v-fg .v-input-wrap .v-input.prefix { padding-left: 28px; }
    .p-layout .v-fg .v-input-wrap .v-input.suffix { padding-right: 32px; }

    .p-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .p-layout .v-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

    .p-layout .v-toggle-wrap { display: flex; align-items: center; gap: 14px; padding: 14px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #e8ecf1; }
    .p-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .p-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .p-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .p-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .p-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .p-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
    .p-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }

    .p-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 750px; }
    .p-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .p-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .p-layout .v-table th { padding: 14px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .p-layout .v-table td { padding: 14px 18px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .p-layout .v-table tbody tr { transition: all 0.15s; }
    .p-layout .v-table tbody tr:hover td { background: #f8fafc; }

    .p-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .p-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .p-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .p-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

    .p-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .p-layout .v-action:hover { transform: translateY(-1px); }
    .p-layout .v-action-view { color: #059669; }
    .p-layout .v-action-view:hover { background: #ecfdf5; }
    .p-layout .v-action-edit { color: #2563eb; }
    .p-layout .v-action-edit:hover { background: #eff6ff; }
    .p-layout .v-action-delete { color: #ef4444; }
    .p-layout .v-action-delete:hover { background: #fef2f2; }

    .p-layout .v-table-wrap { flex: 1; overflow: auto; }

    .p-layout .v-reset { padding: 6px 14px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
    .p-layout .v-reset:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }
    .p-layout .v-submit { padding: 8px 18px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; width: auto; }
    .p-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .p-layout .v-submit:active { transform: translateY(0); }
    .p-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .p-layout .v-divider-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; padding-top: 4px; }
    .p-layout .v-divider-label .dash { flex: 1; height: 1px; background: linear-gradient(90deg, #e8ecf1, transparent); }

    .p-layout .price-text { font-weight: 700; font-size: 14px; }
    .p-layout .price-mrp { color: #0f172a; }
    .p-layout .price-store { color: #4f46e5; }
    .p-layout .price-online { color: #d97706; }
    .p-layout .discount-text { font-size: 11px; color: #10b981; font-weight: 600; }

    @media (max-width: 992px) {
        .p-layout .v-grid-2 { grid-template-columns: 1fr !important; }
        .p-layout .v-grid-3 { grid-template-columns: 1fr !important; }
        .p-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
        #viewModal > div { width: 95% !important; max-width: 500px !important; }
        #viewModalContent > div { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 576px) {
        .p-layout .v-card-body { padding: 16px !important; }
        .p-layout .v-table th,
        .p-layout .v-table td { padding: 10px 14px !important; font-size: 12px !important; }
        .p-layout .v-table { min-width: 600px; }
        #viewModal > div { padding: 16px !important; }
    }
</style>
<div class="p-layout">

    <div id="listView" class="view-section active">
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon table-icon">💰</span> Price Master</span>
                <button class="v-submit" onclick="toggleView('addView', true)">+ Add New Price</button>
            </div>
            <div class="v-card-body" style="padding:0;">
                <div class="v-table-wrap">
                    <table id="priceTable" class="v-table datatable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>MRP</th>
                                <th>Store Price</th>
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
                                    <div style="font-weight:600;color:#0f172a;">{{ $p->product ? $p->product->name : 'Unknown' }}</div>
                                    <div style="font-size:11px;color:#64748b;">SKU: {{ $p->product ? $p->product->sku : '-' }}</div>
                                </td>
                                <td><span style="font-weight:600;">{{ $p->pro_size ?: '-' }}</span></td>
                                <td>
                                    <div class="price-text price-mrp">₹ {{ number_format($p->pro_mrp_price, 2) }}</div>
                                    @if($p->pro_mrp_discount > 0)
                                        <div class="discount-text">-{{ number_format($p->pro_mrp_discount_percentage, 1) }}%</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="price-text price-store">₹ {{ number_format($p->pro_sale_price, 2) }}</div>
                                    @if($p->pro_sale_discount > 0)
                                        <div class="discount-text">-₹{{ number_format($p->pro_sale_discount, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="price-text price-online">₹ {{ number_format($p->pro_online, 2) }}</div>
                                </td>
                                <td style="font-weight:600;">{{ number_format($p->gst_rate, 1) }}%</td>
                                <td>
                                    @if($p->is_active)
                                        <span class="v-badge active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <button class="v-action v-action-view" onclick='viewRecord(@json($p))' title="View Details">👁️ View</button>
                                    <button class="v-action v-action-edit" onclick='editRecord(@json($p))'>✏️ Edit</button>
                                    <button class="v-action v-action-delete" onclick="deleteRecord('{{ $p->encrypted_id }}')">🗑️ Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div id="addView" class="view-section">
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon form-icon">📋</span> <span id="formTitleText">Add Price Configuration</span></span>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="v-reset" onclick="toggleView('listView')">Cancel</button>
                    <button type="button" id="btnSubmitTop" class="v-submit" onclick="document.getElementById('priceForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Pricing</button>
                </div>
            </div>
            <div class="v-card-body">
                <form id="priceForm" novalidate>
                    <input type="hidden" id="encrypted_id" name="encrypted_id">
                    <div class="v-grid-2">
                        <div>
                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🏷️ Product Information</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Select Product <span class="required">*</span></label>
                                    <select id="product_id" name="product_id" class="v-select" onchange="autoFillProductDetails(this)" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $prod)
                                            @php $hasPrice = in_array($prod->id, $existingPriceProductIds); @endphp
                                            <option value="{{ $prod->id }}" data-size="{{ $prod->pro_size }}" data-uom="{{ $prod->uomRelation ? $prod->uomRelation->name : '' }}" data-has-price="{{ $hasPrice ? 1 : 0 }}" {{ $hasPrice ? 'disabled' : '' }}>{{ $prod->name }} (SKU: {{ $prod->sku }}){{ $hasPrice ? ' [Price already set]' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="v-grid-3">
                                    <div class="v-fg">
                                        <label>Size</label>
                                        <input type="number" step="any" id="pro_size" name="pro_size" class="v-input" placeholder="Auto-filled" readonly>
                                    </div>
                                    <div class="v-fg">
                                        <label>Unit Qty <span class="required">*</span></label>
                                        <input type="number" step="1" min="1" id="pro_unit" name="pro_unit" class="v-input" placeholder="e.g. 1" oninput="if(this.value<1||this.value.includes('.')){this.value=parseInt(this.value)||''}" required>
                                    </div>
                                    <div class="v-fg">
                                        <label>Per Unit Price</label>
                                        <div class="v-input-wrap">
                                            <span class="v-prefix">₹</span>
                                            <input type="number" step="0.01" id="pro_per_unit_price" name="pro_per_unit_price" class="v-input prefix" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🏪 Offline Store Pricing</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Store Sale Price <span class="required">*</span></label>
                                    <div class="v-input-wrap">
                                        <span class="v-prefix">₹</span>
                                        <input type="number" step="0.01" id="pro_sale_price" name="pro_sale_price" class="v-input prefix base-calc" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="v-grid-2">
                                    <div class="v-fg">
                                        <label>Sale Discount Amt</label>
                                        <div class="v-input-wrap">
                                            <span class="v-prefix">₹</span>
                                            <input type="number" step="0.01" id="pro_sale_discount" name="pro_sale_discount" class="v-input prefix calc-amt" data-target="pro_sale_discount_percentage" data-base="pro_sale_price" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="v-fg">
                                        <label>Sale Discount %</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="pro_sale_discount_percentage" name="pro_sale_discount_percentage" class="v-input suffix calc-pct" data-target="pro_sale_discount" data-base="pro_sale_price" placeholder="0.00">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🌐 Online Platform Pricing</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Online Sale Price</label>
                                    <div class="v-input-wrap">
                                        <span class="v-prefix">₹</span>
                                        <input type="number" step="0.01" id="pro_online" name="pro_online" class="v-input prefix base-calc" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="v-grid-2">
                                    <div class="v-fg">
                                        <label>Online Discount Amt</label>
                                        <div class="v-input-wrap">
                                            <span class="v-prefix">₹</span>
                                            <input type="number" step="0.01" id="pro_online_discount" name="pro_online_discount" class="v-input prefix calc-amt" data-target="pro_online_discount_percentage" data-base="pro_online" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="v-fg">
                                        <label>Online Discount %</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="pro_online_discount_percentage" name="pro_online_discount_percentage" class="v-input suffix calc-pct" data-target="pro_online_discount" data-base="pro_online" placeholder="0.00">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🏷️ Maximum Retail Price (MRP)</span><span class="dash"></span></div>
                                <div class="v-fg">
                                    <label>Base MRP Price <span class="required">*</span></label>
                                    <div class="v-input-wrap">
                                        <span class="v-prefix">₹</span>
                                        <input type="number" step="0.01" id="pro_mrp_price" name="pro_mrp_price" class="v-input prefix base-calc" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="v-grid-2">
                                    <div class="v-fg">
                                        <label>MRP Discount Amt</label>
                                        <div class="v-input-wrap">
                                            <span class="v-prefix">₹</span>
                                            <input type="number" step="0.01" id="pro_mrp_discount" name="pro_mrp_discount" class="v-input prefix calc-amt" data-target="pro_mrp_discount_percentage" data-base="pro_mrp_price" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="v-fg">
                                        <label>MRP Discount %</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="pro_mrp_discount_percentage" name="pro_mrp_discount_percentage" class="v-input suffix calc-pct" data-target="pro_mrp_discount" data-base="pro_mrp_price" placeholder="0.00">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="background:#fff;border-radius:10px;border:1px solid #e8ecf1;padding:20px;margin-bottom:20px;">
                                <div class="v-divider-label"><span>🧾 Taxation Rates</span><span class="dash"></span></div>
                                <div class="v-grid-3">
                                    <div class="v-fg">
                                        <label>CGST Rate</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="cgst_rate" name="cgst_rate" class="v-input suffix gst-calc" placeholder="0.00">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                    <div class="v-fg">
                                        <label>SGST Rate</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="sgst_rate" name="sgst_rate" class="v-input suffix gst-calc" placeholder="0.00">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                    <div class="v-fg">
                                        <label>Total GST</label>
                                        <div class="v-input-wrap">
                                            <input type="number" step="0.01" id="gst_rate" class="v-input suffix" placeholder="0.00" disabled style="background:#f1f5f9;cursor:not-allowed;">
                                            <span class="v-suffix">%</span>
                                        </div>
                                    </div>
                                </div>
                                <hr style="border:0;border-top:1px solid #e8ecf1;margin:18px 0;">
                                <div class="v-toggle-wrap">
                                    <label class="v-toggle">
                                        <input type="checkbox" id="is_active" name="is_active" checked>
                                        <span class="v-slider"></span>
                                    </label>
                                    <span class="v-toggle-label" style="color:#059669;">Price Configuration is Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px);">
    <div onclick="event.stopPropagation()" style="background:#fff;padding:24px 32px;border-radius:16px;width:550px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:modalIn 0.25s ease;">
        <div style="flex-shrink:0;">
            <h2 style="font-size:18px;font-weight:700;border-bottom:1px solid #e8ecf1;padding-bottom:14px;margin-bottom:16px;color:#0f172a;display:flex;align-items:center;gap:10px;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:8px;font-size:14px;">💰</span>
                Price Configuration Details
            </h2>
        </div>
        <div id="viewModalContent" style="font-size:14px;line-height:1.8;color:#334155;overflow-y:auto;padding-right:8px;flex-grow:1;"></div>
        <div style="flex-shrink:0;margin-top:24px;display:flex;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="v-reset" style="padding:8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- DISCOUNT AUTO-CALCULATORS ---
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
    document.querySelectorAll('.base-calc').forEach(el => {
        el.addEventListener('input', function() {
            let baseId = this.id;
            let pctField = document.querySelector(`.calc-pct[data-base="${baseId}"]`);
            if(pctField && pctField.value) {
                pctField.dispatchEvent(new Event('input'));
            }
        });
    });

    // --- GST AUTO-CALCULATOR ---
    document.querySelectorAll('.gst-calc').forEach(el => {
        el.addEventListener('input', function() {
            let cgst = parseFloat(document.getElementById('cgst_rate').value) || 0;
            let sgst = parseFloat(document.getElementById('sgst_rate').value) || 0;
            document.getElementById('gst_rate').value = (cgst + sgst).toFixed(2);
        });
    });

    // --- AUTO-FILL PRODUCT SIZE & PRICE-SET WARNING ---
    function autoFillProductDetails(selectObj) {
        // Remove any existing price-set warning
        const existingWarn = document.getElementById('priceSetWarning');
        if(existingWarn) existingWarn.remove();

        if(selectObj.value) {
            const opt = selectObj.options[selectObj.selectedIndex];
            document.getElementById('pro_size').value = opt.getAttribute('data-size') || '';

            if(opt.getAttribute('data-has-price') === '1') {
                const warn = document.createElement('div');
                warn.id = 'priceSetWarning';
                warn.style.cssText = 'color:#ef4444;font-size:12px;font-weight:600;margin-top:6px;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;';
                warn.textContent = '⚠️ This product price already set. You cannot add another price for this product.';
                selectObj.parentNode.appendChild(warn);
            }
        }
    }

    // --- UI TOGGLE ---
    function toggleView(viewId, isReset = false) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        if(isReset) {
            document.getElementById('priceForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add Price Configuration';
            document.getElementById('btnSubmitTop').innerText = 'Save Pricing';
            document.getElementById('product_id').disabled = false;
            window.clearFormErrors();
        }
    }

    // --- CLEAR ERRORS ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, .v-select, .v-input.suffix, .v-input.prefix').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('priceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        // Client-side Unit Qty validation
        const uq = document.getElementById('pro_unit');
        const uqNum = parseFloat(uq.value);
        if(uq.value && (uqNum < 1 || !Number.isInteger(uqNum))) {
            uq.style.borderColor = '#ef4444';
            uq.insertAdjacentHTML('afterend', '<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">Unit Qty must be a positive whole number (min 1).</div>');
            toastr.error('Please fix the highlighted errors.');
            return;
        }

        const encId = document.getElementById('encrypted_id').value;

        // Client-side check: product with existing price (ONLY for new records)
        if (!encId) {
            const pid = document.getElementById('product_id');
            const opt = pid.options[pid.selectedIndex];
            if(pid.value && opt && opt.getAttribute('data-has-price') === '1') {
                toastr.error('This product already has a price configured. Please choose another product or edit the existing price.');
                pid.style.borderColor = '#ef4444';
                return;
            }
        }

        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        const url = encId ? `/api/prices/${encId}` : `/api/prices`;

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());
        // Disabled select values are not included in FormData — add manually
        if (!payload.product_id && document.getElementById('product_id').disabled) {
            payload.product_id = document.getElementById('product_id').value;
        }
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
                        let sibling = field.nextElementSibling;
                        if (sibling && sibling.classList.contains('custom-error-text')) sibling.remove();
                        field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">${msg}</div>`);
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

        // Enable all options first so selected value can be set
        const pidSel = document.getElementById('product_id');
        Array.from(pidSel.options).forEach(o => o.disabled = false);
        pidSel.value = record.product_id;
        pidSel.disabled = true;

        for (const [key, value] of Object.entries(record)) {
            let el = document.getElementById(key);
            if (el && el.type !== 'checkbox' && el !== pidSel) {
                el.value = (key === 'pro_unit' && value) ? parseInt(value) : value;
            }
        }
        document.getElementById('is_active').checked = record.is_active == 1;

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
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Product</strong><br>
                    <span style="color:#0f172a;font-weight:600;font-size:16px;">${productName}</span>
                </p>
                <p style="margin:0;text-align:right;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">SKU</strong><br>
                    <span style="color:#64748b;">${sku}</span>
                </p>
            </div>

            <div style="background:#f8fafc;padding:18px;border-radius:10px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;border:1px solid #e2e8f0;">
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Base MRP</div>
                    <div style="font-size:18px;font-weight:700;color:#0f172a;">₹${val(record.pro_mrp_price)}</div>
                    ${record.pro_mrp_discount > 0 ? `<div style="font-size:12px;color:#10b981;font-weight:600;">Disc: -₹${val(record.pro_mrp_discount)} (${val(record.pro_mrp_discount_percentage)}%)</div>` : ''}
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Store Sale</div>
                    <div style="font-size:18px;font-weight:700;color:#4f46e5;">₹${val(record.pro_sale_price)}</div>
                    ${record.pro_sale_discount > 0 ? `<div style="font-size:12px;color:#10b981;font-weight:600;">Disc: -₹${val(record.pro_sale_discount)} (${val(record.pro_sale_discount_percentage)}%)</div>` : ''}
                </div>
                <div>
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Online Sale</div>
                    <div style="font-size:18px;font-weight:700;color:#d97706;">₹${val(record.pro_online)}</div>
                    ${record.pro_online_discount > 0 ? `<div style="font-size:12px;color:#10b981;font-weight:600;">Disc: -₹${val(record.pro_online_discount)} (${val(record.pro_online_discount_percentage)}%)</div>` : ''}
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:20px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Size</strong><br>
                    <span style="color:#0f172a;font-weight:600;">${val(record.pro_size)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Unit Qty</strong><br>
                    <span style="color:#0f172a;">${val(record.pro_unit)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Per Unit Price</strong><br>
                    <span style="color:#0f172a;">₹${val(record.pro_per_unit_price)}</span>
                </p>
            </div>

            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">🧾 Taxation</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">CGST</strong><br>
                    <span style="color:#0f172a;">${val(record.cgst_rate)}%</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">SGST</strong><br>
                    <span style="color:#0f172a;">${val(record.sgst_rate)}%</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Total GST</strong><br>
                    <span style="color:#0f172a;font-weight:600;">${val(record.gst_rate)}%</span>
                </p>
            </div>
        `;

        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection