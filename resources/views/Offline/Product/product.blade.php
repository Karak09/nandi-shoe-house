@extends('Offline.layouts.app')
@section('title', 'Professional Product Master - Shoe ERP')
@section('page_title', 'Product Catalog')
@section('content')

<div class="content-area">
    <div id="listView" class="view-section active">
        <div class="header-actions">
            <div class="page-title">
                <h1>Products</h1>
                <p>Manage your shoe catalog, SKUs, and categories.</p>
            </div>
            <button class="btn btn-primary" onclick="toggleView('addView', true)">+ Add New Product</button>
        </div>

        <div class="card-full">
            <table id="prodTable" class="datatable">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th>ID</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Size & UOM</th>
                        <th>Codes (HSN/SKU)</th>
                        <th>Status</th>
                        <th data-sortable="false" style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="product-cell">
                                <div class="img-thumb" style="background-image: url('{{ $p->images && $p->images->fst_image_doc ? asset('storage/'.$p->images->fst_image_doc) : 'https://via.placeholder.com/40' }}');"></div>
                                <div>
                                    <div class="prod-name">{{ $p->name }}</div>
                                    <div class="prod-sku">Code: {{ $p->product_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p->category ? ($p->category->parent ? $p->category->parent->name . ' > ' : '') . $p->category->name : 'N/A' }}</td>
                        <td>Size: {{ $p->pro_size ?: '-' }} <br><span style="color:#6b7280; font-size:12px;">UOM: {{ $p->uomRelation ? $p->uomRelation->name : 'Unit' }}</span></td>
                        <td>
                            <div>SKU: <span style="font-weight:600;">{{ $p->sku ?: '-' }}</span></div>
                            <div style="color:#6b7280; font-size:12px;">HSN: {{ $p->hsn_code ?: '-' }}</div>
                        </td>
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
                            <button class="action-link delete-link" onclick="deleteRecord('{{ $p->encrypted_id }}')">Delete</button>
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
                <h1 id="formTitleText">Add New Product</h1>
                <p>Fill in the details to list a new shoe in the system.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="toggleView('listView')">Cancel</button>
                <button type="button" id="btnSubmitTop" class="btn btn-primary" onclick="document.getElementById('prodForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Product</button>
            </div>
        </div>

        <form id="prodForm" novalidate>
            <input type="hidden" id="encrypted_id" name="encrypted_id">
            
            <div class="form-layout">
                <div>
                    <div class="form-card">
                        <h2 class="card-title">General Information</h2>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Product Name (English) <span style="color:red">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Nike Air Max 270" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bengali Name</label>
                                <input type="text" id="ben_name" name="ben_name" class="form-control" placeholder="e.g. নাইক এয়ার ম্যাক্স">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Product Description</label>
                            <textarea id="product_des" name="product_des" class="form-control" placeholder="Write a detailed description..." style="min-height: 100px;"></textarea>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="card-title">
                            <span>Product Media (Auto-Revealing Slots)</span>
                        </div>
                        
                        <div class="upload-grid" id="dynamicImageContainer">
                            @php $slots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig']; @endphp
                            @foreach($slots as $index => $s)
                                <div class="upload-wrapper" id="wrapper_{{ $s }}" style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                                    <div class="upload-box" id="box_{{ $s }}">
                                        <input type="file" id="file_{{ $s }}" class="file-input" accept="image/jpeg, image/png, image/jpg">
                                        <input type="hidden" id="{{ $s }}_image_base64" name="{{ $s }}_image_base64">
                                        <input type="hidden" id="{{ $s }}_image_name" name="{{ $s }}_image_name">
                                        <div class="upload-icon">📷</div>
                                        <div class="upload-text">Photo {{ $index + 1 }}<br><span style="color:#ef4444;">Max 70KB</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <div class="form-card">
                        <h2 class="card-title">Organization & Attributes</h2>
                        <div class="form-group">
                            <label class="form-label">Category <span style="color:red">*</span></label>
                            <select id="cat_id" name="cat_id" class="form-control" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->parent ? $cat->parent->name . ' > ' : '' }}{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Size (pro_size) <span style="color:red">*</span></label>
                                <input type="number" id="pro_size" name="pro_size" class="form-control" placeholder="e.g. 8, 9" min="0" step="any" oninput="this.value = Math.abs(this.value)" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit (UOM) <span style="color:red">*</span></label>
                                <select id="uom" name="uom" class="form-control" required>
                                    <option value="">Select Unit...</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->keyword }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="check-group" style="display:flex; gap:10px;">
                            <input type="checkbox" id="is_packet" name="is_packet" style="width:16px; height:16px; cursor:pointer;">
                            <label for="is_packet" class="form-label" style="margin:0;">Is Packet Item?</label>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2 class="card-title">Inventory Identifiers</h2>
                        <div class="form-group">
                            <label class="form-label">Product Code <span style="color:red">*</span></label>
                            <input type="text" id="product_code" name="product_code" class="form-control" placeholder="Unique system code" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" id="sku" name="sku" class="form-control" placeholder="e.g. NK270-BLK-9">
                        </div>
                        <div class="form-group">
                            <label class="form-label">HSN Code (Taxation)</label>
                            <input type="text" id="hsn_code" name="hsn_code" class="form-control" placeholder="e.g. 64041190">
                        </div>
                        
                        <hr style="border:0; border-top:1px solid #e5e7eb; margin: 20px 0;">
                        
                        <div class="check-group" style="display:flex; gap:10px;">
                            <input type="checkbox" id="is_active" name="is_active" checked style="width:16px; height:16px; cursor:pointer;">
                            <label for="is_active" class="form-label" style="margin:0; font-weight:600; color:#10b981;">Product is Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:650px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="flex-shrink: 0;">
            <h2 style="font-size: 18px; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; color: #0f172a;">Product Details</h2>
        </div>
        <div id="viewModalContent" style="font-size: 14px; line-height: 1.8; color: #334155; overflow-y: auto; padding-right: 8px; flex-grow: 1;"></div>
        <div style="flex-shrink: 0; margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-outline" style="padding: 8px 24px;">Close</button>
        </div>
    </div>
</div>

<div id="imageLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.95); z-index:10000; align-items:center; justify-content:center;">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 40px; background: none; border: none; color: white; font-size: 40px; cursor: pointer; font-weight: bold; z-index:10001;">&times;</button>
    
    <img id="lightboxImg" src="" style="transition: transform 0.3s ease; transform-origin: center center; max-width: 90vw; max-height: 90vh; cursor: grab;">
    
    <div style="position:absolute; bottom: 40px; display:flex; gap:15px; background:rgba(255,255,255,0.15); padding:10px 20px; border-radius:30px; z-index:10001;">
        <button onclick="zoomImg(0.5)" class="btn btn-outline" style="color:white; border-color:white; background:rgba(0,0,0,0.6);">➕ Zoom In</button>
        <button onclick="zoomImg(-0.5)" class="btn btn-outline" style="color:white; border-color:white; background:rgba(0,0,0,0.6);">➖ Zoom Out</button>
    </div>
</div>

@push('scripts')
<script>
    // --- FULL IMAGE ZOOM LOGIC ---
    let currentZoom = 1;
    window.openLightbox = function(src) {
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

    // --- SIMPLE SEQUENTIAL IMAGE UPLOADER ---
    const imgSlots = ['fst', 'sec', 'trd', 'foth', 'fiv', 'six', 'sev', 'eig'];

    imgSlots.forEach((slot, index) => {
        document.getElementById(`file_${slot}`).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            document.getElementById(`${slot}_image_name`).value = file.name;
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById(`${slot}_image_base64`).value = e.target.result;
                document.getElementById(`box_${slot}`).style.backgroundImage = `url(${e.target.result})`;
                
                // Show the next box automatically
                if (index + 1 < imgSlots.length) {
                    const nextSlot = imgSlots[index + 1];
                    document.getElementById(`wrapper_${nextSlot}`).style.display = 'flex';
                } else {
                    // ✅ LAST IMAGE (8th)
                    toastr.warning('You can only upload 8 photos', 'Limit Reached', {
                        timeOut: 3000,
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right"
                    });
                }
            };
            reader.readAsDataURL(file);
        });
    });

    function resetImageBoxes() {
        imgSlots.forEach((slot, index) => {
            // Hide all boxes except the first one
            document.getElementById(`wrapper_${slot}`).style.display = (index === 0) ? 'flex' : 'none';
            document.getElementById(`box_${slot}`).style.backgroundImage = 'none';
            document.getElementById(`file_${slot}`).value = '';
            document.getElementById(`${slot}_image_base64`).value = '';
            document.getElementById(`${slot}_image_name`).value = '';
        });
    }

    function toggleView(viewId, isReset = false) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        if(isReset) {
            document.getElementById('prodForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add New Product';
            document.getElementById('btnSubmitTop').innerText = 'Save Product';
            window.clearFormErrors();
            resetImageBoxes();
        }
    }

    // --- HELPER: CLEAR ERRORS ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.form-control, input').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('prodForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true; 
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/products/${encId}` : `/api/products`;

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;
        payload.is_packet = document.getElementById('is_packet').checked ? 1 : 0;

        try {
            const res = await fetch(url, {
                method: method,
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
            btn.innerHTML = encId ? 'Update Product' : 'Save Product';
            
            // Clean global error message display
            toastr.error(error.message || 'Please fix the validation errors.');
            
        if (error.errors) {
            for (let fieldName in error.errors) {
                let msg = error.errors[fieldName][0];

                // ✅ HANDLE IMAGE ERRORS
                if (fieldName.includes('_image_base64')) {
                    let slot = fieldName.replace('_image_base64', ''); // fst, sec, etc
                    let wrapper = document.getElementById(`wrapper_${slot}`);

                    if (wrapper) {
                        // remove old error if exists
                        wrapper.querySelectorAll('.custom-error-text').forEach(el => el.remove());

                        wrapper.insertAdjacentHTML(
                            'beforeend',
                            `<div class="custom-error-text" style="
                                color:#ef4444;
                                font-size:11px;
                                margin-top:4px;
                                font-weight:600;
                                text-align:center;
                            ">${msg}</div>`
                        );

                        // optional: highlight box
                        let box = document.getElementById(`box_${slot}`);
                        if (box) box.style.borderColor = '#ef4444';
                    }

                    continue;
                }

                // ✅ NORMAL FIELD ERRORS (unchanged)
                let field = document.querySelector(`[name="${fieldName}"]`);
                if (field && field.type !== 'hidden') {
                    field.style.borderColor = '#ef4444';
                    field.insertAdjacentHTML(
                        'afterend',
                        `<div class="custom-error-text" style="
                            color:#ef4444;
                            font-size:11px;
                            margin-top:4px;
                            font-weight:600;
                        ">${msg}</div>`
                    );
                }
            }
        }
        }
    });

    // --- EDIT RECORD ---
    window.editRecord = function(record) {
        toggleView('addView');
        window.clearFormErrors();
        document.getElementById('formTitleText').innerText = 'Edit Product';
        document.getElementById('btnSubmitTop').innerText = 'Update Product';
        
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        document.getElementById('ben_name').value = record.ben_name || '';
        document.getElementById('product_des').value = record.product_des || '';
        document.getElementById('product_code').value = record.product_code;
        document.getElementById('sku').value = record.sku || '';
        document.getElementById('hsn_code').value = record.hsn_code || '';
        document.getElementById('pro_size').value = record.pro_size || '';
        document.getElementById('cat_id').value = record.cat_id || '';
        document.getElementById('uom').value = record.uom || '';
        
        document.getElementById('is_active').checked = record.is_active == 1;
        document.getElementById('is_packet').checked = record.is_packet == 1;

        resetImageBoxes();
        let lastFilledIndex = -1;
        
        if(record.images) {
            imgSlots.forEach((slot, index) => {
                let doc = record.images[`${slot}_image_doc`];
                if(doc) {
                    document.getElementById(`wrapper_${slot}`).style.display = 'flex';
                    document.getElementById(`box_${slot}`).style.backgroundImage = `url('/storage/${doc}')`;
                    lastFilledIndex = index;
                }
            });
        }
        
        // Show the next empty slot if there is room
        if (lastFilledIndex + 1 < imgSlots.length) {
            document.getElementById(`wrapper_${imgSlots[lastFilledIndex + 1]}`).style.display = 'flex';
        }
    };

    // --- DELETE RECORD ---
    window.deleteRecord = async function(encId) {
        if(!confirm('Are you sure you want to soft delete this product?')) return;
        try {
            const res = await fetch(`/api/products/${encId}`, {
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
        const val = (item) => item ? item : '-';
        
        let imagesHtml = '';
        if(record.images) {
            imgSlots.forEach(slot => {
                let doc = record.images[`${slot}_image_doc`];
                if(doc) imagesHtml += `<img src="/storage/${doc}" class="modal-img" title="Click to Zoom" onclick="openLightbox(this.src)">`;
            });
        }
        if(!imagesHtml) imagesHtml = '<p style="color:#64748b; font-style:italic; grid-column: 1/-1;">No images uploaded.</p>';

        const content = `
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Product Media (Click to Zoom)</div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
                ${imagesHtml}
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>Product Name:</strong> <br><span style="color:#0f172a; font-weight:600;">${val(record.name)}</span></p>
                <p style="margin:0;"><strong>Category:</strong> <br><span style="color:#0f172a;">${record.category ? (record.category.parent ? record.category.parent.name + ' > ' : '') + record.category.name : '-'}</span></p>
                <p style="margin:0;"><strong>Product Code:</strong> <br><span style="color:#0f172a;">${val(record.product_code)}</span></p>
                <p style="margin:0;"><strong>SKU:</strong> <br><span style="color:#0f172a;">${val(record.sku)}</span></p>
                <p style="margin:0;"><strong>HSN Code:</strong> <br><span style="color:#0f172a;">${val(record.hsn_code)}</span></p>
                <p style="margin:0;"><strong>Size:</strong> <br><span style="color:#0f172a;">${val(record.pro_size)}</span> <br><span style="color:#6b7280; font-size:12px;">UOM: ${record.uom_relation ? record.uom_relation.name : 'Unit'}</span></p>
            </div>
        `;
        
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection