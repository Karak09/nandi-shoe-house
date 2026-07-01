@extends('Offline.layouts.app')
    @section('title', 'Store Master - Shoe ERP')
    @section('page_title', 'Retail Store Management')

    @section('content')
    <style>
        .store-layout { display: flex; gap: 24px; height: calc(100vh - 140px); }

        .store-layout .form-section { flex: 0 0 450px; display: flex; flex-direction: column; }
        .store-layout .form-section .v-card-body { flex: 1; overflow-y: auto; padding: 20px; }
        .store-layout .form-section .v-card-body::-webkit-scrollbar { width: 5px; }
        .store-layout .form-section .v-card-body::-webkit-scrollbar-track { background: transparent; }
        .store-layout .form-section .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

        .store-layout .table-section { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .store-layout .table-section .v-card-body { flex: 1; overflow: hidden; padding: 0; display: flex; flex-direction: column; }
        .store-layout .table-section .v-table-wrap { flex: 1; overflow-y: auto; overflow-x: auto; }
        .store-layout .table-section .v-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
        .store-layout .table-section .v-table-wrap::-webkit-scrollbar-track { background: transparent; }
        .store-layout .table-section .v-table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

        .store-layout .v-fg { margin-bottom: 18px; }
        .store-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
        .store-layout .v-fg label .required { color: #ef4444; }
        .store-layout .v-fg .v-input,
        .store-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; }
        .store-layout .v-fg .v-input:focus,
        .store-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        .store-layout .v-fg .v-input:hover,
        .store-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }

        .store-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .store-layout .v-divider { margin: 22px 0 18px 0; border: 0; border-top: 1px solid #e8ecf1; }
        .store-layout .v-divider-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; padding-top: 4px; }
        .store-layout .v-divider-label .dash { flex: 1; height: 1px; background: linear-gradient(90deg, #e8ecf1, transparent); }

        .store-layout .v-area-section { display: none; margin-top: 6px; padding: 16px; background: #f8fafc; border: 1.5px dashed #d1d5db; border-radius: 12px; }
        .store-layout .v-area-options { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 10px; }
        .store-layout .v-area-options label { font-size: 13px; font-weight: 500; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 8px; border: 1.5px solid #e2e8f0; transition: all 0.2s; background: #fff; }
        .store-layout .v-area-options label:hover { border-color: #2563eb; background: #eff6ff; }
        .store-layout .v-area-options input[type="radio"] { accent-color: #2563eb; width: 16px; height: 16px; }

        .store-layout .v-geo-section { display: none; margin-top: 12px; padding: 16px; background: #fff; border: 1px solid #e8ecf1; border-radius: 12px; }

        .store-layout .v-toggle-wrap { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e8ecf1; }
        .store-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
        .store-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
        .store-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
        .store-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .store-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
        .store-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
        .store-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }

        .store-layout .v-submit { width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
        .store-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
        .store-layout .v-submit:active { transform: translateY(0); }
        .store-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .store-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 650px; }
        .store-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
        .store-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
        .store-layout .v-table th { padding: 14px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .store-layout .v-table td { padding: 16px 20px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
        .store-layout .v-table tbody tr { transition: all 0.15s; }
        .store-layout .v-table tbody tr:hover td { background: #f8fafc; }
        .store-layout .v-td-title { font-weight: 600; color: #0f172a; margin-bottom: 3px; }
        .store-layout .v-td-sub { font-size: 12px; color: #94a3b8; }

        .store-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
        .store-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
        .store-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .store-layout .v-badge.active .dot { background: #10b981; }
        .store-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
        .store-layout .v-badge.inactive .dot { background: #94a3b8; }

        .store-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
        .store-layout .v-action:hover { transform: translateY(-1px); }
        .store-layout .v-action-view { color: #059669; }
        .store-layout .v-action-view:hover { background: #ecfdf5; }
        .store-layout .v-action-edit { color: #2563eb; }
        .store-layout .v-action-edit:hover { background: #eff6ff; }
        .store-layout .v-action-delete { color: #ef4444; }
        .store-layout .v-action-delete:hover { background: #fef2f2; }

        @media (max-width: 992px) {
            .store-layout { flex-direction: column !important; height: auto !important; gap: 16px !important; }
            .store-layout .form-section { flex: none !important; width: 100% !important; }
            .store-layout .form-section .v-card-body { max-height: 400px !important; }
            .store-layout .table-section { width: 100% !important; min-height: 300px !important; }
            .store-layout .v-grid-2 { grid-template-columns: 1fr !important; }
            .store-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
            #viewModal > div { width: 95% !important; max-width: 450px !important; }
            #viewModalContent > div { grid-template-columns: 1fr !important; }
            #viewModalContent > div > div { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 576px) {
            .store-layout .v-card-body { padding: 16px !important; }
            .store-layout .v-area-options { flex-direction: column; gap: 10px; }
            .store-layout .v-area-options label { width: 100%; }
            #viewModal > div { padding: 16px !important; }
            #viewModalContent > div { gap: 8px !important; }
        }
    </style>
    <div class="store-layout">
        
        <section class="v-card form-section">
            <div class="v-card-header">
                <span><span class="v-icon form-icon">🏪</span> <span id="formTitle">Register New Store</span></span>
                <button type="button" id="btnClear" class="v-reset">Reset</button>
            </div>
            <div class="v-card-body">
                <form id="storeForm">
                    <input type="hidden" id="encrypted_id" name="encrypted_id">
                    
                    <div class="v-fg">
                        <label>Choose Store User <span class="required">*</span></label>
                        <select id="user_id" name="user_id" class="v-select" required>
                            <option value="">Select User</option>
                        </select>
                    </div>

                    <div class="v-fg">
                        <label>Store Name <span class="required">*</span></label>
                        <input type="text" id="store_name" name="store_name" class="v-input" required>
                    </div>
                    
                    <div class="v-grid-2">
                        <div class="v-fg">
                            <label>Contact No <span class="required">*</span></label>
                            <input type="text" id="contact_no" name="contact_no" class="v-input" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>
                        <div class="v-fg">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="v-input" required>
                        </div>
                    </div>

                    <hr class="v-divider">
                    <div class="v-divider-label"><span>📍 Store Geography</span><span class="dash"></span></div>

                    <div class="v-grid-2">
                        <div class="v-fg">
                            <label>State <span class="required">*</span></label>
                            <select id="state_id" name="state_id" class="v-select loc-trigger" data-target="district_id" data-url="/api/get-districts/" required>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="v-fg">
                            <label>District <span class="required">*</span></label>
                            <select id="district_id" name="district_id" class="v-select" disabled required>
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>

                    <div id="area_type_section" class="v-area-section">
                        <label style="font-size:12px;font-weight:600;color:#475569;">Select Area Type <span class="required">*</span></label>
                        <div class="v-area-options">
                            <label><input type="radio" name="area_type" value="rural" id="type_rural"> <span>🏘️ Rural (Gram Panchayat)</span></label>
                            <label><input type="radio" name="area_type" value="urban" id="type_urban"> <span>🏙️ Urban (Municipality)</span></label>
                        </div>
                    </div>

                    <div id="rural_section" class="v-geo-section">
                        <div class="v-grid-2" style="margin-bottom:14px;">
                            <div class="v-fg">
                                <label>Block <span class="required">*</span></label>
                                <select id="block_id" name="block_id" class="v-select loc-trigger" data-target="gp_id" data-url="/api/get-gram-panchayats/">
                                    <option value="">Select Block</option>
                                </select>
                            </div>
                            <div class="v-fg">
                                <label>Gram Panchayat <span class="required">*</span></label>
                                <select id="gp_id" name="gp_id" class="v-select loc-trigger" data-target="vill_id" data-url="/api/get-villages/">
                                    <option value="">Select Panchayat</option>
                                </select>
                            </div>
                        </div>
                        <div class="v-grid-2">
                            <div class="v-fg">
                                <label>Village <span style="color:#94a3b8;font-weight:400;">(Optional)</span></label>
                                <select id="vill_id" name="vill_id" class="v-select loc-trigger" data-target="post_id" data-url="/api/get-post-offices-by-village/">
                                    <option value="">Select Village</option>
                                </select>
                            </div>
                            <div class="v-fg">
                                <label>Post Office <span style="color:#94a3b8;font-weight:400;">(Optional)</span></label>
                                <select id="post_id" name="post_id" class="v-select" disabled>
                                    <option value="">Select Post Office</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="urban_section" class="v-geo-section">
                        <div class="v-grid-2">
                            <div class="v-fg">
                                <label>Municipality / Corp <span class="required">*</span></label>
                                <select id="muni_id" name="muni_id" class="v-select loc-trigger" data-target="ward_id" data-url="/api/get-wards/">
                                    <option value="">Select Municipality</option>
                                </select>
                            </div>
                            <div class="v-fg">
                                <label>Ward <span class="required">*</span></label>
                                <select id="ward_id" name="ward_id" class="v-select" disabled>
                                    <option value="">Select Ward</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="v-divider">
                    <div class="v-divider-label"><span>📍 Address Details</span><span class="dash"></span></div>

                    <div class="v-grid-2">
                        <div class="v-fg">
                            <label>Flat/Shop No <span class="required">*</span></label>
                            <input type="text" id="flat_no" name="flat_no" class="v-input" required>
                        </div>
                        <div class="v-fg">
                            <label>PIN Code <span class="required">*</span></label>
                            <input type="text" id="pin" name="pin" class="v-input" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>
                        <div class="v-fg" style="grid-column:1/-1;">
                            <label>Complete Address <span class="required">*</span></label>
                            <textarea id="address" name="address" class="v-input" rows="3" required></textarea>
                        </div>
                    </div>

                    <div class="v-fg">
                        <label>Status</label>
                        <div class="v-toggle-wrap">
                            <label class="v-toggle">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <span class="v-slider"></span>
                            </label>
                            <span class="v-toggle-label">Active Store</span>
                        </div>
                    </div>

                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e8ecf1;">
                        <button type="submit" id="btnSubmit" class="v-submit">Save Store Details</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="v-card table-section">
            <div class="v-card-header">
                <span><span class="v-icon table-icon">📋</span> Store Directory</span>
            </div>
            <div class="v-card-body">
                <div class="v-table-wrap">
                    <table id="dataTable" class="v-table datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Store Info</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $s)
                            <tr>
                                <td><span class="v-td-title">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="v-td-title">{{ $s->store_name }}</div>
                                    <div class="v-td-sub">{{ $s->flat_no }}, {{ $s->pin }}</div>
                                </td>
                                <td>
                                    <div class="v-td-title">{{ $s->contact_no }}</div>
                                    <div class="v-td-sub">{{ $s->email }}</div>
                                </td>
                                <td>
                                    @if($s->is_active)
                                        <span class="v-badge active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <button class="v-action v-action-view" onclick='viewRecord(@json($s))' title="View Details">👁️ View</button>
                                    <button class="v-action v-action-edit" onclick='editRecord(@json($s))'>✏️ Edit</button>
                                    <button class="v-action v-action-delete" onclick="deleteRecord('{{ $s->encrypted_id }}')">🗑️ Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
        <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:450px; max-height: 90vh; display: flex; flex-direction: column;">
            
            <div style="flex-shrink: 0;">
                <h2 style="font-size: 18px; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; color: #0f172a;">Store Details</h2>
            </div>
            
            <div id="viewModalContent" style="font-size: 14px; line-height: 1.8; color: #334155; overflow-y: auto; padding-right: 8px; flex-grow: 1;">
                </div>
            
            <div style="flex-shrink: 0; margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-outline" style="padding: 8px 24px;">Close</button>
            </div>
            
        </div>
    </div>

    @push('scripts')
    <script>

        // --- CLEAR ERRORS HELPER ---
        window.clearFormErrors = function() {
            document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
            document.querySelectorAll('.v-input, .v-select, select, textarea, input').forEach(el => el.style.borderColor = '');
            const areaSection = document.getElementById('area_type_section');
            if (areaSection) {
                areaSection.style.border = 'none';
                areaSection.style.borderColor = '';
            }
        };

        // --- FORM SUBMIT ---
        // 1. Force novalidate on the form via JS just in case it's missing in HTML
        document.getElementById('storeForm').setAttribute('novalidate', true);

        // --- FETCH STORE USERS API ---
        window.fetchStoreUsers = async function() {
            const select = document.getElementById('user_id'); // Updated ID
            
            if (select.options.length > 1) return; 

            try {
                const res = await fetch('/api/get-store-users', {
                    method: 'GET',
                    headers: { 
                        'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                        'Accept': 'application/json'
                    }
                });
                
                const users = await res.json();
                
                select.innerHTML = '<option value="">Select User</option>';
                users.forEach(user => {
                    // Using user.full_name from our mapped Controller response
                    select.insertAdjacentHTML('beforeend', `<option value="${user.id}">${user.full_name}</option>`);
                });
            } catch (error) {
                console.error('Failed to fetch store users:', error);
            }
        };

        // Trigger the fetch when the user clicks/focuses on the dropdown
        document.getElementById('user_id').addEventListener('focus', fetchStoreUsers);

        document.getElementById('storeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (window.clearFormErrors) window.clearFormErrors();

            const btn = document.getElementById('btnSubmit');
            btn.disabled = true; 
            btn.innerHTML = 'Processing...';

            const encId = document.getElementById('encrypted_id').value;
            const method = encId ? 'PUT' : 'POST';
            const url = encId ? `/api/stores/${encId}` : `/api/stores`;

            // 2. Temporarily enable disabled fields so FormData captures their values
            const disabledFields = this.querySelectorAll(':disabled');
            disabledFields.forEach(field => field.disabled = false);

            const formData = new FormData(this);
            const payload = Object.fromEntries(formData.entries());

            // Re-disable fields
            disabledFields.forEach(field => field.disabled = true);

            payload.is_active = document.getElementById('is_active').checked ? 1 : 0;

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
                
                // 3. Parse JSON errors before checking response.ok
                if (!res.ok) {
                    const errorData = await res.json();
                    throw errorData; 
                }

                const data = await res.json();
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000); 
                
            } catch (error) {
                btn.disabled = false; 
                btn.innerHTML = encId ? 'Update Details' : 'Save Details';

                console.log("LARAVEL VALIDATION ERRORS:", error);

                toastr.error(error.message || 'Please fix the errors highlighted below.');
                
                // 4. Inject Inline Errors (BULLETPROOF VERSION)
                if (error.errors) {
                    for (let fieldName in error.errors) {
                        let msg = error.errors[fieldName][0];
                        let field = document.querySelector(`[name="${fieldName}"]`);
                        
                        if (fieldName === 'area_type') {
                            let container = document.getElementById('area_type_section');
                            if (container) {
                                container.style.border = '1.5px solid #ef4444';
                                if (!container.querySelector('.custom-error-text')) {
                                    container.insertAdjacentHTML('beforeend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 8px; font-weight: 600;">${msg}</div>`);
                                }
                            }
                        } else if (field) {
                            field.style.borderColor = '#ef4444';
                            let parent = field.parentElement;
                            let existing = parent ? parent.querySelector('.custom-error-text') : field.nextElementSibling;
                            if (existing && existing.classList.contains('custom-error-text')) { existing.remove(); }
                            field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                        }
                    }
                }
            }
        });

        const setSelectValue = async (elementId, value) => {
            const el = document.getElementById(elementId);
            if(el && value) {
                el.value = value;
                el.dispatchEvent(new Event('change', { bubbles: true }));
                await new Promise(r => setTimeout(r, 600)); 
            }
        };

        // --- POPULATE FOR EDIT ---
        window.editRecord = async function(record) {
            if (window.clearFormErrors) window.clearFormErrors();
            document.getElementById('formTitle').innerText = 'Edit Store';
            document.getElementById('encrypted_id').value = record.encrypted_id;
            
            // Populate user_type_id
            await window.fetchStoreUsers();
            document.getElementById('user_id').value = record.user_id; 
            document.getElementById('user_id').disabled = true;
            document.getElementById('store_name').value = record.store_name;
            document.getElementById('contact_no').value = record.contact_no;
            document.getElementById('email').value = record.email;
            document.getElementById('flat_no').value = record.flat_no;
            document.getElementById('address').value = record.address;
            document.getElementById('pin').value = record.pin;
            document.getElementById('is_active').checked = record.is_active == 1;

            document.getElementById('state_id').value = record.state_id;
            await window.fetchLocationData('/api/get-districts/', record.state_id, 'district_id');
            document.getElementById('district_id').value = record.district_id;
            
            document.getElementById('area_type_section').style.display = 'block';

            if (record.area_type === 'rural' || record.block_id || record.gp_id) {
                document.getElementById('type_rural').checked = true;
                document.getElementById('rural_section').style.display = 'block';
                document.getElementById('urban_section').style.display = 'none';

                await window.fetchLocationData('/api/get-blocks/', record.district_id, 'block_id');
                document.getElementById('block_id').value = record.block_id;

                if (record.block_id) {
                    await window.fetchLocationData('/api/get-gram-panchayats/', record.block_id, 'gp_id');
                    document.getElementById('gp_id').value = record.gp_id;
                }

                if (record.gp_id) {
                    await window.fetchLocationData('/api/get-villages/', record.gp_id, 'vill_id');
                    document.getElementById('vill_id').value = record.vill_id;
                }

                if (record.vill_id) {
                    await window.fetchLocationData('/api/get-post-offices-by-village/', record.vill_id, 'post_id');
                    document.getElementById('post_id').value = record.post_id;
                }

            } else if (record.area_type === 'urban' || record.muni_id || record.ward_id) {
                document.getElementById('type_urban').checked = true;
                document.getElementById('urban_section').style.display = 'block';
                document.getElementById('rural_section').style.display = 'none';

                await window.fetchLocationData('/api/get-municipalities/', record.district_id, 'muni_id');
                document.getElementById('muni_id').value = record.muni_id;

                if (record.muni_id) {
                    await window.fetchLocationData('/api/get-wards/', record.muni_id, 'ward_id');
                    document.getElementById('ward_id').value = record.ward_id;
                }
            }

            document.getElementById('btnSubmit').innerText = 'Update Details';
        };

        // --- CLEAR FORM ---
        document.getElementById('btnClear').addEventListener('click', () => {
            document.getElementById('storeForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitle').innerText = 'Register New Store';
            document.getElementById('btnSubmit').innerText = 'Save Details';
            document.getElementById('btnSubmit').disabled = false;
            document.getElementById('user_id').disabled = false;
            document.getElementById('district_id').innerHTML = '<option value="">Select District</option>';
            document.getElementById('district_id').disabled = true;
            document.getElementById('area_type_section').style.display = 'none';
            document.getElementById('rural_section').style.display = 'none';
            document.getElementById('urban_section').style.display = 'none';
        });

        // --- DELETE ---
        window.deleteRecord = async function(encId) {
            if(!confirm('Are you sure you want to soft delete this record?')) return;
            try {
                const res = await fetch(`/api/stores/${encId}`, {
                    method: 'DELETE',
                    headers: { 
                        'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    }
                });
                const data = await res.json();
                if(!res.ok) throw data;
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } catch (error) { toastr.error('Deletion failed.'); }
        };

        // --- VIEW ---
        window.viewRecord = function(record) {
            const val = (item) => item ? item : '-';

            const fName = record.store_user?.details?.f_name || '';
            const lName = record.store_user?.details?.l_name || '';
            const fullName = `${fName} ${lName}`.trim() || '-';

            let areaType = '-';
            if (record.area_type && record.area_type !== null) {
                areaType = record.area_type;
            } else if (record.block_id != null || record.gp_id != null) {
                areaType = 'rural';
            } else if (record.muni_id != null || record.ward_id != null) {
                areaType = 'urban';
            }

            let geoHtml = '';
            if (areaType === 'rural') {
                geoHtml = `
                    <p style="margin:0;"><strong>Block:</strong> <br><span style="color:#0f172a;">${val(record.block?.name)}</span></p>
                    <p style="margin:0;"><strong>Gram Panchayat:</strong> <br><span style="color:#0f172a;">${val(record.gram_panchayat?.name)}</span></p>
                    <p style="margin:0;"><strong>Village:</strong> <br><span style="color:#0f172a;">${val(record.village?.name)}</span></p>
                    <p style="margin:0;"><strong>Post Office:</strong> <br><span style="color:#0f172a;">${val(record.post_office?.name)}</span></p>
                `;
            } else if (areaType === 'urban') {
                geoHtml = `
                    <p style="margin:0;"><strong>Municipality / Corp:</strong> <br><span style="color:#0f172a;">${val(record.municipality?.name)}</span></p>
                    <p style="margin:0;"><strong>Ward:</strong> <br><span style="color:#0f172a;">${val(record.ward?.name)}</span></p>
                `;
            } else {
                geoHtml = `<p style="margin:0; grid-column: 1 / -1; color:#ef4444; font-style:italic;">No geographic data saved.</p>`;
            }

            // Injected Store User Name into the view Modal
            const content = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <p style="margin:0;"><strong>Store User:</strong> <br><span style="color:#0f172a; font-weight:600;">${fullName}</span></p>
                    <p style="margin:0;"><strong>Store Name:</strong> <br><span style="color:#0f172a; font-weight:600;">${val(record.store_name)}</span></p>
                    <p style="margin:0;"><strong>Contact No:</strong> <br><span style="color:#0f172a;">${val(record.contact_no)}</span></p>
                    <p style="margin:0;"><strong>Email:</strong> <br><span style="color:#0f172a;">${val(record.email)}</span></p>
                    <p style="margin:0; grid-column: 1 / -1;"><strong>Status:</strong> <br>${record.is_active ? '<span style="color:#10b981; font-weight:bold;">Active</span>' : '<span style="color:#ef4444; font-weight:bold;">Inactive</span>'}</p>
                </div>
                
                <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Geography Details</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <p style="margin:0;"><strong>State:</strong> <br><span style="color:#0f172a;">${val(record.state?.name)}</span></p>
                    <p style="margin:0;"><strong>District:</strong> <br><span style="color:#0f172a;">${val(record.district?.name)}</span></p>
                    <p style="margin:0; grid-column: 1 / -1;"><strong>Area Type:</strong> <br><span style="color:#0f172a; text-transform: capitalize;">${areaType}</span></p>
                </div>

                <div style="background: #f8fafc; padding: 12px; border-radius: 6px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    ${geoHtml}
                </div>

                <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Address Details</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <p style="margin:0;"><strong>Flat/Shop No:</strong> <br><span style="color:#0f172a;">${val(record.flat_no)}</span></p>
                    <p style="margin:0;"><strong>PIN Code:</strong> <br><span style="color:#0f172a;">${val(record.pin)}</span></p>
                    <p style="margin:0; grid-column: 1 / -1;"><strong>Complete Address:</strong> <br><span style="color:#0f172a;">${val(record.address)}</span></p>
                </div>
            `;
            
            document.getElementById('viewModalContent').innerHTML = content;
            document.getElementById('viewModal').style.display = 'flex';
        };
    </script>
    @endpush
    @endsection