@extends('Offline.layouts.app')
@section('title', 'New Purchase Entry - Shoe ERP')
@section('content')

<div class="main-content">
    <div class="workspace">
        <form id="purchaseForm" autocomplete="off" novalidate>
            <div class="card">
                <div class="card-header">
                    <span>Create New Purchase Entry</span>
                </div>
                
                <div class="challan-info">
                    <div class="form-group">
                        <label>Select Vendor <span style="color:red">*</span></label>
                        <select class="form-control" id="vendor_id" name="vendor_id" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $v) <option value="{{ $v->id }}">{{ $v->vendor_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Challan / Invoice No <span style="color:red">*</span></label>
                        <input type="text" class="form-control" id="challan_no" name="challan_no" placeholder="e.g. INV-98234" required>
                    </div>
                    <div class="form-group">
                        <label>Challan Date <span style="color:red">*</span></label>
                        <input type="date" class="form-control" id="challan_date" name="challan_date" required>
                    </div>
                </div>

                <div class="entry-grid-container">
                    <table class="entry-table" id="itemTable">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align:center;">ID</th>
                                <th style="width: 25%;">Product / Shoe Model <span style="color:red">*</span></th>
                                <th style="width: 8%;">Qty <span style="color:red">*</span></th>
                                <th style="width: 10%;">UOM <span style="color:red">*</span></th>
                                <th>Unit Price (₹) <span style="color:red">*</span></th>
                                <th>MRP (₹) <span style="color:red">*</span></th>
                                <th style="width: 8%;">GST %</th>
                                <th>Total (₹)</th>
                                <th style="width: 40px; text-align:center;">Del</th>
                            </tr>
                        </thead>
                        <tbody id="gridBody">
                            </tbody>
                    </table>
                    <button type="button" class="btn-add-row" onclick="addRow()">+ Add New Product Row</button>
                </div>

                <div class="entry-footer">
                    <div style="flex: 1; max-width: 600px;">
                        <div style="font-size: 13px; font-weight: 600; color:#0f172a; margin-bottom: 12px;">Upload Challan Images (Optional)</div>
                        <div class="upload-grid" id="dynamicImageContainer">
                            @php $slots = ['fst', 'sec', 'trd', 'foth', 'fiv']; @endphp
                            @foreach($slots as $index => $s)
                                <div class="upload-wrapper" id="wrapper_{{ $s }}" style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                                    <div class="upload-box" id="box_{{ $s }}">
                                        <input type="file" id="file_{{ $s }}" class="file-input" accept="image/jpeg, image/png, image/jpg">
                                        <input type="hidden" id="{{ $s }}_image_base64" name="{{ $s }}_image_base64">
                                        <input type="hidden" id="{{ $s }}_image_name" name="{{ $s }}_image_name">
                                        <img id="img_{{ $s }}" class="modal-img" onclick="openLightbox(this.src)">
                                        <div class="upload-icon">📄</div>
                                        <div class="upload-text">Upload<br><span style="color:#ef4444;">Max 70KB</span></div>
                                    </div>
                                    <button type="button" class="btn-remove-img" id="btn_remove_{{ $s }}" onclick="removeImage('{{ $s }}')">×</button>
                                    <div id="err_{{ $s }}" class="img-error-text"></div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">Command / Remarks (Optional)</label>
                            <textarea id="command" name="command" class="form-control" style="min-height: 80px;" placeholder="Write any notes or remarks here..."></textarea>
                        </div>
                    </div>
                    
                    <div class="totals-box">
                        <div class="total-row"><span>Total Items (Rows):</span> <span id="lblRows" class="num-col">0</span></div>
                        <div class="total-row"><span>Total Quantity:</span> <span id="lblQty" class="num-col">0.00</span></div>
                        <div class="total-row"><span>Sub Total:</span> <span id="lblSubTotal" class="num-col">₹ 0.00</span></div>
                        <div class="total-row"><span>Total GST Amount:</span> <span id="lblGst" class="num-col">₹ 0.00</span></div>
                        <div class="total-row grand"><span>Grand Total:</span> <span id="lblGrandTotal" class="num-col">₹ 0.00</span></div>
                        
                        <button type="submit" id="btnSubmit" class="btn-save">Save Purchase Entry</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="imageLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.95); z-index:10000; align-items:center; justify-content:center;">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 40px; background: none; border: none; color: white; font-size: 40px; cursor: pointer; font-weight: bold; z-index:10001;">&times;</button>
    <img id="lightboxImg" src="" style="transition: transform 0.3s ease; transform-origin: center center; max-width: 90vw; max-height: 90vh;">
    <div style="position:absolute; bottom: 40px; display:flex; gap:15px; background:rgba(255,255,255,0.15); padding:10px 20px; border-radius:30px; z-index:10001;">
        <button type="button" onclick="zoomImg(0.5)" class="btn btn-outline" style="color:white; border:1px solid white; background:rgba(0,0,0,0.6); padding:8px 16px; border-radius:6px; cursor:pointer;">➕ Zoom In</button>
        <button type="button" onclick="zoomImg(-0.5)" class="btn btn-outline" style="color:white; border:1px solid white; background:rgba(0,0,0,0.6); padding:8px 16px; border-radius:6px; cursor:pointer;">➖ Zoom Out</button>
    </div>
</div>

@push('scripts')
<script>
    // FIX: Block Back Button Form Caching (Bfcache)
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) { window.location.reload(); }
    });

    const productsList = @json($products);
    const unitsList = @json($units);

    // --- DYNAMIC GRID ENGINE ---
    let globalRowId = 0; 

    function addRow() {
        const tbody = document.getElementById('gridBody');
        const tr = document.createElement('tr');
        tr.id = `row_${globalRowId}`;
        tr.className = 'item-row';
        
        let prodHtml = `<select class="prod-select" name="products[${globalRowId}][product_id]" required onchange="checkDuplicateAndCalc(this, ${globalRowId})"><option value="">Select Product...</option>`;
        productsList.forEach(p => prodHtml += `<option value="${p.id}">${p.name}</option>`);
        prodHtml += `</select>`;

        let uomHtml = `<select class="uom-select" name="products[${globalRowId}][uom]" required><option value="">Unit...</option>`;
        unitsList.forEach(u => uomHtml += `<option value="${u.id}">${u.keyword}</option>`);
        uomHtml += `</select>`;

        tr.innerHTML = `
            <td class="row-index" style="text-align: center; color: #64748b; font-size: 12px; font-weight:600;"></td>
            <td>${prodHtml}</td>
            <td class="col-num"><input type="number" name="products[${globalRowId}][quantity]" class="qty-input" data-rid="${globalRowId}" step="any" min="0.1" required oninput="calcRow(${globalRowId})"></td>
            <td>${uomHtml}</td>
            <td class="col-num"><input type="number" name="products[${globalRowId}][unit_price]" class="price-input" data-rid="${globalRowId}" step="any" min="0" required oninput="calcRow(${globalRowId})"></td>
            <td class="col-num"><input type="number" name="products[${globalRowId}][mrp]" class="mrp-input" data-rid="${globalRowId}" step="any" min="0" required></td>
            <td class="col-num"><input type="number" name="products[${globalRowId}][gst]" class="gst-input" data-rid="${globalRowId}" step="any" min="0" oninput="calcRow(${globalRowId})"></td>
            <td class="col-num"><input type="text" id="total_${globalRowId}" readonly style="background:#f8fafc; font-weight:600; color:#0f172a; border:1px solid transparent;" value="0.00"></td>
            <td style="text-align: center; color: #ef4444; cursor: pointer; font-weight: bold; font-size: 18px;" onclick="removeRow('${tr.id}')">×</td>
        `;
        tbody.appendChild(tr);
        globalRowId++;
        reindexRows();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            row.querySelector('.row-index').innerText = index + 1;
        });
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

        if (count > 1) {
            toastr.error("Same product cannot be added twice in one entry.");
            selectObj.value = ""; 
        }
        calcRow(id);
    }

    function calcRow(id) {
        let qty = parseFloat(document.querySelector(`.qty-input[data-rid="${id}"]`)?.value) || 0;
        let price = parseFloat(document.querySelector(`.price-input[data-rid="${id}"]`)?.value) || 0;
        let gst = parseFloat(document.querySelector(`.gst-input[data-rid="${id}"]`)?.value) || 0;

        let base = qty * price;
        let gstAmt = base * (gst / 100);
        let final = base + gstAmt;

        let totalBox = document.getElementById(`total_${id}`);
        if(totalBox) totalBox.value = final.toFixed(2);
        
        calcTotals();
    }

    function calcTotals() {
        let totalQty = 0, subTotal = 0, totalGst = 0, grandTotal = 0;
        const rows = document.querySelectorAll('.item-row');

        rows.forEach(row => {
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

        document.getElementById('lblRows').innerText = rows.length;
        document.getElementById('lblQty').innerText = totalQty.toFixed(2);
        document.getElementById('lblSubTotal').innerText = `₹ ${subTotal.toFixed(2)}`;
        document.getElementById('lblGst').innerText = `₹ ${totalGst.toFixed(2)}`;
        document.getElementById('lblGrandTotal').innerText = `₹ ${grandTotal.toFixed(2)}`;
    }

    // --- REMOVABLE SEQUENTIAL IMAGE UPLOADER ---
    const imgSlots = ['fst', 'sec', 'trd', 'foth', 'fiv'];
    
    imgSlots.forEach((slot, index) => {
        document.getElementById(`file_${slot}`).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Clear errors
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
                
                // Show Remove Button
                document.getElementById(`btn_remove_${slot}`).style.display = 'block';
                
                // Reveal next box
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
        
        // Clear errors immediately
        document.getElementById(`box_${slot}`).style.borderColor = '#cbd5e1';
        document.getElementById(`err_${slot}`).style.display = 'none';
    };

    // --- ZOOM LIGHTBOX LOGIC ---
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

    // --- FORM SUBMIT (Perfect Error Mapping) ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text, .grid-error-text').forEach(el => el.remove());
        document.querySelectorAll('.img-error-text').forEach(el => el.style.display = 'none'); 
        document.querySelectorAll('.form-control, input, select').forEach(el => el.style.borderColor = '');
        document.querySelectorAll('.upload-box').forEach(el => el.style.borderColor = '#cbd5e1');
    };

    document.getElementById('purchaseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();
        
        const rows = document.querySelectorAll('.item-row');
        if(rows.length === 0) {
            toastr.error("Please add at least one product.");
            return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; 
        btn.innerHTML = 'Saving...';

        const payload = Object.fromEntries(new FormData(this).entries());
        
        payload.products = [];
        rows.forEach(row => {
            payload.products.push({
                product_id: row.querySelector('.prod-select').value,
                quantity: row.querySelector('.qty-input').value,
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
            try { data = await res.json(); } 
            catch (err) { throw new Error("A fatal server error occurred."); }

            if (!res.ok) throw data;
            
            toastr.success(data.message);
            // Form success: Reload so it wipes via the pageshow listener or just reload
            setTimeout(() => window.location.reload(), 1000); 
            
        } catch (error) {
            btn.disabled = false; 
            btn.innerHTML = 'Save Purchase Entry';
            toastr.error(error.message || 'Validation failed. Please check highlighted fields.');
            
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    
                    // Image Errors
                    if (fieldName.includes('_image_base64')) {
                        let slot = fieldName.split('_')[0];
                        let box = document.getElementById('box_' + slot);
                        let errDiv = document.getElementById('err_' + slot);
                        if(box && errDiv) {
                            box.style.borderColor = '#ef4444';
                            errDiv.innerText = msg;
                            errDiv.style.display = 'block';
                        }
                    } 
                    // Grid Errors
                    else if (fieldName.startsWith('products.')) {
                        let parts = fieldName.split('.');
                        let inputName = `products[${parts[1]}][${parts[2]}]`; 
                        let field = document.querySelector(`[name="${inputName}"]`);
                        if (field) {
                            field.style.borderColor = '#ef4444';
                            let errSpan = document.createElement('div');
                            errSpan.className = 'grid-error-text';
                            errSpan.innerText = "Required";
                            field.parentNode.style.position = 'relative';
                            field.parentNode.appendChild(errSpan);
                        }
                    }
                    // Regular Inputs
                    else {
                        let field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.style.borderColor = '#ef4444';
                            field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection