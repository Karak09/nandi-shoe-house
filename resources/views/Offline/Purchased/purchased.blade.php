@extends('Offline.layouts.app')
@section('title', 'New Purchase Entry - Shoe ERP')
@section('content')
<style>
    .p-layout .v-card { background: #fff; border-radius: 14px; border: 1px solid #e8ecf1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .p-layout .v-card-header { padding: 18px 24px; font-weight: 700; font-size: 15px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e8ecf1; color: #0f172a; letter-spacing: -0.2px; }
    .p-layout .v-card-header .v-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; margin-right: 10px; font-size: 14px; }
    .p-layout .v-card-header .v-icon.form-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
    .p-layout .v-card-body { padding: 24px; }

    .p-layout .v-fg { margin-bottom: 18px; }
    .p-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .p-layout .v-fg label .required { color: #ef4444; }
    .p-layout .v-fg .v-input,
    .p-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .p-layout .v-fg .v-input:focus,
    .p-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .p-layout .v-fg .v-input:hover,
    .p-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }
    .p-layout .v-fg textarea.v-input { min-height: 80px; resize: vertical; }

    .p-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .p-layout .v-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

    .p-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 780px; table-layout: fixed; }
    .p-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .p-layout .v-table th { padding: 12px 14px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
    .p-layout .v-table td { padding: 8px 10px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; }
    .p-layout .v-table tbody tr:hover td { background: #f8fafc; }
    .p-layout .v-table-wrap { overflow-x: auto; margin-bottom: 12px; border: 1px solid #e8ecf1; border-radius: 10px; }
    .p-layout .v-table .v-cell-num { text-align: center; color: #64748b; font-size: 12px; font-weight: 600; width: 36px; }
    .p-layout .v-table .v-cell-del { text-align: center; color: #ef4444; cursor: pointer; font-weight: bold; font-size: 20px; width: 36px; }
    .p-layout .v-table .v-cell-del:hover { background: #fef2f2; }
    .p-layout .v-table .prod-select,
    .p-layout .v-table .uom-select,
    .p-layout .v-table .qty-input,
    .p-layout .v-table .price-input,
    .p-layout .v-table .mrp-input,
    .p-layout .v-table .gst-input { width: 100%; padding: 7px 8px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 12px; color: #0f172a; outline: none; background: #fafbfc; box-sizing: border-box; transition: all 0.2s; }
    .p-layout .v-table .prod-select:focus,
    .p-layout .v-table .uom-select:focus,
    .p-layout .v-table .qty-input:focus,
    .p-layout .v-table .price-input:focus,
    .p-layout .v-table .mrp-input:focus,
    .p-layout .v-table .gst-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .v-add-row { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fff; border: 1.5px dashed #2563eb; border-radius: 10px; font-size: 12px; font-weight: 600; color: #2563eb; cursor: pointer; transition: all 0.2s; margin-bottom: 16px; }
    .v-add-row:hover { background: #eff6ff; border-color: #1d4ed8; }

    .p-layout .v-submit { padding: 10px 20px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
    .p-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .p-layout .v-submit:active { transform: translateY(0); }
    .p-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .p-layout .upload-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 8px; }
    .p-layout .upload-wrapper { display: flex; flex-direction: column; position: relative; }
    .p-layout .upload-box { position: relative; border: 2px dashed #d1d5db; border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; transition: 0.2s; background: #f9fafb; background-size: cover; background-position: center; height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; }
    .p-layout .upload-box.has-image { border-style: solid; border-color: #94a3b8; }
    .p-layout .upload-icon { font-size: 16px; color: #9ca3af; margin-bottom: 2px; }
    .p-layout .upload-text { font-size: 9px; font-weight: 600; color: #374151; background: rgba(255,255,255,0.8); padding: 2px 6px; border-radius: 4px; line-height: 1.3; }
    .p-layout .file-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2; }
    .p-layout .upload-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; display: none; position: absolute; top: 0; left: 0; z-index: 1; }
    .p-layout .upload-box.has-image img { display: block; }
    .p-layout .upload-box.has-image .upload-icon,
    .p-layout .upload-box.has-image .upload-text { display: none; }
    .p-layout .btn-remove-img { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%; background: #ef4444; color: #fff; border: 2px solid #fff; font-size: 14px; line-height: 18px; text-align: center; cursor: pointer; display: none; z-index: 5; font-weight: bold; padding: 0; }
    .p-layout .btn-remove-img:hover { background: #dc2626; }
    .p-layout .img-error-text { display: none; color: #ef4444; font-size: 10px; font-weight: 600; margin-top: 4px; text-align: center; }

    .p-layout .v-totals { display: flex; flex-wrap: wrap; gap: 16px; padding: 16px 20px; background: #f8fafc; border: 1px solid #e8ecf1; border-radius: 12px; margin-bottom: 20px; }
    .p-layout .v-total-item { flex: 1; min-width: 120px; }
    .p-layout .v-total-item .lbl { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .p-layout .v-total-item .val { font-size: 16px; font-weight: 700; color: #0f172a; }
    .p-layout .v-total-item .val.grand { color: #2563eb; font-size: 20px; }

    .p-layout .v-divider-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; padding-top: 4px; }
    .p-layout .v-divider-label .dash { flex: 1; height: 1px; background: linear-gradient(90deg, #e8ecf1, transparent); }
    .p-layout .v-section { background: #fff; border-radius: 10px; border: 1px solid #e8ecf1; padding: 20px; margin-bottom: 20px; }

    .v-error-text { color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600; }

    @media (max-width: 992px) {
        .p-layout .v-grid-2 { grid-template-columns: 1fr !important; }
        .p-layout .v-grid-3 { grid-template-columns: 1fr !important; }
        .p-layout .upload-grid { grid-template-columns: repeat(3, 1fr) !important; }
        .p-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
        .p-layout .v-card-header .v-submit { width: 100% !important; }
        .p-layout .v-totals { gap: 12px; }
        .p-layout .v-total-item { min-width: 100px; }
        .p-layout .v-fg .v-input,
        .p-layout .v-fg .v-select { padding: 12px 14px !important; font-size: 14px !important; }
        .p-layout .v-add-row { width: 100%; justify-content: center; }
    }
    @media (max-width: 576px) {
        .p-layout .v-card-body { padding: 16px !important; }
        .p-layout .upload-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .p-layout .v-table th,
        .p-layout .v-table td { padding: 6px 6px !important; font-size: 11px !important; }
        .p-layout .v-table { min-width: 780px; }
        .p-layout .v-table .prod-select,
        .p-layout .v-table .uom-select,
        .p-layout .v-table .qty-input,
        .p-layout .v-table .price-input,
        .p-layout .v-table .mrp-input,
        .p-layout .v-table .gst-input { padding: 5px 6px !important; font-size: 11px !important; }
        .p-layout .v-section { padding: 14px !important; }
        .p-layout .v-total-item { min-width: 80px; }
        .p-layout .v-total-item .val { font-size: 14px; }
        .p-layout .v-total-item .val.grand { font-size: 16px; }
        .p-layout .upload-box { height: 70px !important; }
    }
</style>
<div class="p-layout">
    <form id="purchaseForm" autocomplete="off" novalidate>
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon form-icon">📦</span> Create New Purchase Entry</span>
                <button type="submit" id="btnSubmit" class="v-submit" style="width:auto;padding:8px 18px;font-size:12px;">Save Purchase Entry</button>
            </div>
            <div class="v-card-body">

                <div class="v-section">
                    <div class="v-divider-label"><span>📄 Challan Information</span><span class="dash"></span></div>
                    <div class="v-grid-3">
                        <div class="v-fg">
                            <label>Select Vendor <span class="required">*</span></label>
                            <select class="v-select" id="vendor_id" name="vendor_id" required>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $v) <option value="{{ $v->id }}">{{ $v->vendor_name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="v-fg">
                            <label>Challan / Invoice No <span class="required">*</span></label>
                            <input type="text" class="v-input" id="challan_no" name="challan_no" placeholder="e.g. INV-98234" required>
                        </div>
                        <div class="v-fg">
                            <label>Challan Date <span class="required">*</span></label>
                            <input type="date" class="v-input" id="challan_date" name="challan_date" required>
                        </div>
                    </div>
                </div>

                <div class="v-section">
                    <div class="v-divider-label"><span>🛒 Product Items</span><span class="dash"></span></div>
                    <div class="v-table-wrap">
                        <table class="v-table" id="itemTable">
                            <thead>
                                <tr>
                                    <th style="width:32px;text-align:center;">#</th>
                                    <th style="width:180px;">Product <span class="required">*</span></th>
                                    <th style="width:72px;">Qty <span class="required">*</span></th>
                                    <th style="width:82px;">UOM <span class="required">*</span></th>
                                    <th style="width:95px;">Price (₹) <span class="required">*</span></th>
                                    <th style="width:88px;">MRP (₹) <span class="required">*</span></th>
                                    <th style="width:65px;">GST %</th>
                                    <th style="width:28px;text-align:center;">X</th>
                                </tr>
                            </thead>
                            <tbody id="gridBody"></tbody>
                        </table>
                    </div>
                    <button type="button" class="v-add-row" onclick="addRow()">+ Add New Product Row</button>

                    <div class="v-totals">
                        <div class="v-total-item">
                            <div class="lbl">Items</div>
                            <div class="val" id="lblRows">0</div>
                        </div>
                        <div class="v-total-item">
                            <div class="lbl">Total Qty</div>
                            <div class="val" id="lblQty">0</div>
                        </div>
                        <div class="v-total-item">
                            <div class="lbl">Sub Total</div>
                            <div class="val" id="lblSubTotal">₹ 0.00</div>
                        </div>
                        <div class="v-total-item">
                            <div class="lbl">Total GST</div>
                            <div class="val" id="lblGst">₹ 0.00</div>
                        </div>
                        <div class="v-total-item">
                            <div class="lbl">Grand Total</div>
                            <div class="val grand" id="lblGrandTotal">₹ 0.00</div>
                        </div>
                    </div>
                </div>

                <div class="v-section">
                    <div class="v-divider-label"><span>📎 Attachments & Remarks</span><span class="dash"></span></div>
                    <div class="v-grid-2">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:8px;">Upload Challan Images (Optional)</div>
                            <div class="upload-grid" id="dynamicImageContainer">
                                @php $slots = ['fst', 'sec', 'trd', 'foth', 'fiv']; @endphp
                                @foreach($slots as $index => $s)
                                    <div class="upload-wrapper" id="wrapper_{{ $s }}" style="display:{{ $index === 0 ? 'flex' : 'none' }};">
                                        <div class="upload-box" id="box_{{ $s }}">
                                            <input type="file" id="file_{{ $s }}" class="file-input" accept="image/jpeg, image/png, image/jpg">
                                            <input type="hidden" id="{{ $s }}_image_base64" name="{{ $s }}_image_base64">
                                            <input type="hidden" id="{{ $s }}_image_name" name="{{ $s }}_image_name">
                                            <img id="img_{{ $s }}" onclick="openLightbox(this.src)">
                                            <div class="upload-icon">📄</div>
                                            <div class="upload-text">Photo {{ $index + 1 }}<br><span style="color:#ef4444;">Max 70KB</span></div>
                                        </div>
                                        <button type="button" class="btn-remove-img" id="btn_remove_{{ $s }}" onclick="removeImage('{{ $s }}')">&times;</button>
                                        <div id="err_{{ $s }}" class="img-error-text"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="v-fg">
                                <label>Command / Remarks</label>
                                <textarea id="command" name="command" class="v-input" placeholder="Write any notes or remarks here..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </form>
</div>

<div id="imageLightbox" onclick="if(event.target===this)closeLightbox()" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.95);z-index:10000;align-items:center;justify-content:center;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:40px;background:none;border:none;color:white;font-size:40px;cursor:pointer;font-weight:bold;z-index:10001;">&times;</button>
    <img id="lightboxImg" src="" style="transition:transform 0.3s ease;transform-origin:center center;max-width:90vw;max-height:90vh;cursor:grab;">
    <div style="position:absolute;bottom:40px;display:flex;gap:15px;background:rgba(255,255,255,0.15);padding:10px 20px;border-radius:30px;z-index:10001;">
        <button type="button" onclick="zoomImg(0.5)" style="padding:8px 16px;background:rgba(0,0,0,0.6);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:6px;cursor:pointer;font-weight:600;">➕ Zoom In</button>
        <button type="button" onclick="zoomImg(-0.5)" style="padding:8px 16px;background:rgba(0,0,0,0.6);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:6px;cursor:pointer;font-weight:600;">➖ Zoom Out</button>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) { window.location.reload(); }
    });

    const productsList = @json($products);
    const unitsList = @json($units);

    let globalRowId = 0;

    function addRow() {
        const tbody = document.getElementById('gridBody');
        const tr = document.createElement('tr');
        tr.id = `row_${globalRowId}`;
        tr.className = 'item-row';

        let prodHtml = `<select class="prod-select" name="products[${globalRowId}][product_id]" required onchange="checkDuplicateAndCalc(this, ${globalRowId})"><option value="">-- Select Product --</option>`;
        productsList.forEach(p => prodHtml += `<option value="${p.id}">${p.name}</option>`);
        prodHtml += `</select>`;

        let uomHtml = `<select class="uom-select" name="products[${globalRowId}][uom]" required><option value="">-- Unit --</option>`;
        unitsList.forEach(u => uomHtml += `<option value="${u.id}">${u.keyword}</option>`);
        uomHtml += `</select>`;

        tr.innerHTML = `
            <td class="v-cell-num row-index"></td>
            <td>${prodHtml}</td>
            <td><input type="number" name="products[${globalRowId}][quantity]" class="qty-input" data-rid="${globalRowId}" step="1" min="1" required oninput="validateQty(this);calcRow(${globalRowId})"></td>
            <td>${uomHtml}</td>
            <td><input type="number" name="products[${globalRowId}][unit_price]" class="price-input" data-rid="${globalRowId}" step="any" min="0" required oninput="calcRow(${globalRowId})"></td>
            <td><input type="number" name="products[${globalRowId}][mrp]" class="mrp-input" data-rid="${globalRowId}" step="any" min="0" required></td>
            <td><input type="number" name="products[${globalRowId}][gst]" class="gst-input" data-rid="${globalRowId}" step="any" min="0" oninput="calcRow(${globalRowId})"></td>
            <td class="v-cell-del" onclick="removeRow('${tr.id}')">&times;</td>
        `;
        tbody.appendChild(tr);
        globalRowId++;
        reindexRows();
    }

    function validateQty(input) {
        let val = parseInt(input.value);
        if (input.value && (isNaN(val) || val < 1 || input.value.includes('.'))) {
            input.value = val > 0 ? val : '';
        }
    }

    function reindexRows() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, index) => { row.querySelector('.row-index').innerText = index + 1; });
        calcTotals();
    }

    function removeRow(rowId) {
        document.getElementById(rowId).remove();
        reindexRows();
    }

    function checkDuplicateAndCalc(selectObj, id) {
        const selectedVal = selectObj.value;
        if (!selectedVal) return;
        const allSelects = document.querySelectorAll('.prod-select');
        let count = 0;
        allSelects.forEach(sel => { if (sel.value === selectedVal) count++; });
        if (count > 1) { toastr.error("Same product cannot be added twice in one entry."); selectObj.value = ""; }
        calcRow(id);
    }

    function calcRow(id) {
        let qty = parseFloat(document.querySelector(`.qty-input[data-rid="${id}"]`)?.value) || 0;
        let price = parseFloat(document.querySelector(`.price-input[data-rid="${id}"]`)?.value) || 0;
        let gst = parseFloat(document.querySelector(`.gst-input[data-rid="${id}"]`)?.value) || 0;
        let base = qty * price;
        let gstAmt = base * (gst / 100);
        calcTotals();
    }

    function calcTotals() {
        let totalQty = 0, subTotal = 0, totalGst = 0, grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            let price = parseFloat(row.querySelector('.price-input').value) || 0;
            let gst = parseFloat(row.querySelector('.gst-input').value) || 0;
            let base = qty * price;
            let gstAmt = base * (gst / 100);
            totalQty += qty;
            subTotal += base;
            totalGst += gstAmt;
            grandTotal += (base + gstAmt);
        });
        document.getElementById('lblRows').innerText = document.querySelectorAll('.item-row').length;
        document.getElementById('lblQty').innerText = Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2);
        document.getElementById('lblSubTotal').innerText = `₹ ${subTotal.toFixed(2)}`;
        document.getElementById('lblGst').innerText = `₹ ${totalGst.toFixed(2)}`;
        document.getElementById('lblGrandTotal').innerText = `₹ ${grandTotal.toFixed(2)}`;
    }

    // --- IMAGE UPLOADER ---
    const imgSlots = ['fst', 'sec', 'trd', 'foth', 'fiv'];
    imgSlots.forEach((slot, index) => {
        document.getElementById(`file_${slot}`).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            document.getElementById(`box_${slot}`).style.borderColor = '#cbd5e1';
            document.getElementById(`err_${slot}`).style.display = 'none';
            document.getElementById(`${slot}_image_name`).value = file.name;
            const reader = new FileReader();
            reader.onload = (ev) => {
                document.getElementById(`${slot}_image_base64`).value = ev.target.result;
                let box = document.getElementById(`box_${slot}`);
                let img = document.getElementById(`img_${slot}`);
                img.src = ev.target.result;
                box.classList.add('has-image');
                document.getElementById(`btn_remove_${slot}`).style.display = 'block';
                if (index + 1 < imgSlots.length) {
                    document.getElementById(`wrapper_${imgSlots[index + 1]}`).style.display = 'flex';
                }
            };
            reader.readAsDataURL(file);
        });
    });

    window.removeImage = function(slot) {
        document.getElementById(`box_${slot}`).classList.remove('has-image');
        document.getElementById(`img_${slot}`).src = '';
        document.getElementById(`file_${slot}`).value = '';
        document.getElementById(`${slot}_image_base64`).value = '';
        document.getElementById(`${slot}_image_name`).value = '';
        document.getElementById(`btn_remove_${slot}`).style.display = 'none';
        document.getElementById(`box_${slot}`).style.borderColor = '#cbd5e1';
        document.getElementById(`err_${slot}`).style.display = 'none';
    };

    // --- ZOOM LIGHTBOX ---
    let currentZoom = 1;
    window.openLightbox = function(src) {
        if(!src) return;
        currentZoom = 1;
        const img = document.getElementById('lightboxImg');
        img.style.transform = `scale(1)`;
        img.src = src;
        document.getElementById('imageLightbox').style.display = 'flex';
    };
    window.closeLightbox = function() {
        document.getElementById('imageLightbox').style.display = 'none';
    };
    window.zoomImg = function(step) {
        currentZoom += step;
        if(currentZoom < 0.5) currentZoom = 0.5;
        if(currentZoom > 5) currentZoom = 5;
        document.getElementById('lightboxImg').style.transform = `scale(${currentZoom})`;
    };

    // --- FORM SUBMIT ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.v-error-text, .grid-error-text').forEach(el => el.remove());
        document.querySelectorAll('.img-error-text').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.v-input, .v-select, .qty-input, .price-input, .mrp-input, .gst-input, .prod-select, .uom-select').forEach(el => el.style.borderColor = '');
        document.querySelectorAll('.upload-box').forEach(el => el.style.borderColor = '#cbd5e1');
    };

    document.getElementById('purchaseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const rows = document.querySelectorAll('.item-row');
        if(rows.length === 0) { toastr.error("Please add at least one product."); return; }

        let hasQtyError = false;
        rows.forEach(row => {
            let qty = row.querySelector('.qty-input');
            let val = parseInt(qty.value);
            if (!qty.value || isNaN(val) || val < 1 || qty.value.includes('.')) {
                qty.style.borderColor = '#ef4444';
                if (!qty.nextElementSibling?.classList.contains('v-error-text')) {
                    qty.insertAdjacentHTML('afterend', '<div class="v-error-text">Must be a positive whole number (min 1).</div>');
                }
                hasQtyError = true;
            }
        });
        if (hasQtyError) { toastr.error('Please fix the quantity errors highlighted.'); return; }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        const payload = Object.fromEntries(new FormData(this).entries());
        payload.products = [];
        rows.forEach(row => {
            payload.products.push({
                product_id: row.querySelector('.prod-select').value,
                quantity: parseInt(row.querySelector('.qty-input').value),
                uom: row.querySelector('.uom-select').value,
                unit_price: row.querySelector('.price-input').value,
                mrp: row.querySelector('.mrp-input').value,
                gst: row.querySelector('.gst-input').value || 0
            });
        });

        try {
            const res = await fetch('/api/purchases', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });
            let data;
            try { data = await res.json(); } catch (err) { throw new Error("A fatal server error occurred."); }
            if (!res.ok) throw data;
            toastr.success(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = 'Save Purchase Entry';
            toastr.error(error.message || 'Validation failed.');
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    if (fieldName.includes('_image_base64')) {
                        let slot = fieldName.split('_')[0];
                        let box = document.getElementById('box_' + slot);
                        let errDiv = document.getElementById('err_' + slot);
                        if(box && errDiv) { box.style.borderColor = '#ef4444'; errDiv.innerText = msg; errDiv.style.display = 'block'; }
                    } else if (fieldName.startsWith('products.')) {
                        let parts = fieldName.split('.');
                        let inputName = `products[${parts[1]}][${parts[2]}]`;
                        let field = document.querySelector(`[name="${inputName}"]`);
                        if (field) {
                            field.style.borderColor = '#ef4444';
                            if (!field.nextElementSibling?.classList.contains('v-error-text')) {
                                field.insertAdjacentHTML('afterend', `<div class="v-error-text">${msg}</div>`);
                            }
                        }
                    } else {
                        let field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.style.borderColor = '#ef4444';
                            if (!field.nextElementSibling?.classList.contains('v-error-text')) {
                                field.insertAdjacentHTML('afterend', `<div class="v-error-text">${msg}</div>`);
                            }
                        }
                    }
                }
            }
        }
    });

    addRow();
</script>
@endpush
@endsection