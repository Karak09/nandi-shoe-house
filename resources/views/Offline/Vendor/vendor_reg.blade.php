@extends('Offline.layouts.app')
@section('title', 'Vendor Master - Shoe ERP')
@section('page_title', 'Vendor Management')

@section('content')
<div style="display: flex; gap: 24px; height: calc(100vh - 140px);">
    
    <section class="card form-section" style="flex: 0 0 450px; display: flex; flex-direction: column;">
        <div class="card-header">
            <span id="formTitle">Add New Vendor</span>
            <button type="button" id="btnClear" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Reset</button>
        </div>
        <div class="card-body">
            <form id="vendorForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Vendor Company Name <span style="color:red">*</span></label>
                    <input type="text" id="vendor_name" name="vendor_name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Owner / Contact Person <span style="color:red">*</span></label>
                    <input type="text" id="owner_name" name="owner_name" class="form-control" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Contact No <span style="color:red">*</span></label>
                        <input type="text" id="contact_no" name="contact_no" class="form-control" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span style="color:red">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="@gmail.com" required>
                    </div>
                </div>

                <div style="margin: 24px 0 16px 0; border-top: 1px solid var(--border); padding-top: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Vendor Geography</label>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">State <span style="color:red">*</span></label>
                        <select id="state_id" name="state_id" class="form-control loc-trigger" data-target="district_id" data-url="/api/get-districts/" required>
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">District <span style="color:red">*</span></label>
                        <select id="district_id" name="district_id" class="form-control" disabled required>
                            <option value="">Select District</option>
                        </select>
                    </div>
                </div>

                <div id="area_type_section" class="form-group" style="display: none; margin-top: 16px; padding: 12px; background: var(--bg-base); border-radius: 6px;">
                    <label class="form-label">Select Area Type <span style="color:red">*</span></label>
                    <div style="display: flex; gap: 20px;">
                        <label style="font-size: 13px; font-weight: 500;"><input type="radio" name="area_type" value="rural" id="type_rural"> Rural (Gram Panchayat)</label>
                        <label style="font-size: 13px; font-weight: 500;"><input type="radio" name="area_type" value="urban" id="type_urban"> Urban (Municipality)</label>
                    </div>
                </div>

                <div id="rural_section" style="display: none; margin-top: 16px;">
                    <div class="grid-2" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Block <span style="color:red">*</span></label>
                            <select id="block_id" name="block_id" class="form-control loc-trigger" data-target="gp_id" data-url="/api/get-gram-panchayats/">
                                <option value="">Select Block</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gram Panchayat <span style="color:red">*</span></label>
                            <select id="gp_id" name="gp_id" class="form-control loc-trigger" data-target="vill_id" data-url="/api/get-villages/">
                                <option value="">Select Panchayat</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Village (Optional)</label>
                            <select id="vill_id" name="vill_id" class="form-control loc-trigger" data-target="post_id" data-url="/api/get-post-offices-by-village/">
                                <option value="">Select Village</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Post Office (Optional)</label>
                            <select id="post_id" name="post_id" class="form-control" disabled>
                                <option value="">Select Post Office</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="urban_section" style="display: none; margin-top: 16px;">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Municipality / Corp <span style="color:red">*</span></label>
                            <select id="muni_id" name="muni_id" class="form-control loc-trigger" data-target="ward_id" data-url="/api/get-wards/">
                                <option value="">Select Municipality</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ward <span style="color:red">*</span></label>
                            <select id="ward_id" name="ward_id" class="form-control" disabled>
                                <option value="">Select Ward</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin: 24px 0 16px 0; border-top: 1px solid var(--border); padding-top: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Address Details</label>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Flat / Building No. <span style="color:red">*</span></label>
                        <input type="text" id="flat_no" name="flat_no" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location / Area <span style="color:red">*</span></label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PIN Code <span style="color:red">*</span></label>
                        <input type="text" id="pin" name="pin" class="form-control" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Complete Address <span style="color:red">*</span></label>
                    <textarea id="address" name="address" class="form-control" required></textarea>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrapper">
                        <label class="toggle-switch">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="slider"></span>
                        </label>
                        <span class="toggle-label" style="font-size: 13px; font-weight: 600;">Active Vendor</span>
                    </div>
                </div>

                <div class="action-footer" style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" id="btnSubmit" class="btn btn-primary" style="padding: 10px 24px;">Save Vendor Details</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card table-section">
        <div class="card-header">
            <span>Vendor Directory</span>
            <input type="text" id="vendorSearchInput" class="search-input" onkeyup="tableSearch('vendorSearchInput', 'vendorTable')" placeholder="Search anything...">
        </div>
        <div style="flex: 1; overflow-y: auto;">
            <table id="vendorTable" style="width: 100%; border-collapse: collapse; text-align: left;" class="datatable">
                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                    <tr>
                        <th style="padding: 12px 20px; font-size: 11px;">ID</th>
                        <th style="padding: 12px 20px; font-size: 11px;">Vendor Info</th>
                        <th style="padding: 12px 20px; font-size: 11px;">Contact Details</th>
                        <th style="padding: 12px 20px; font-size: 11px;">Status</th>
                        <th style="padding: 12px 20px; font-size: 11px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $v)
                    <tr style="border-bottom: 1px solid var(--border-light);">
                        <td style="padding: 16px 20px;">
                            <div class="td-title">{{ $loop->iteration }}</div>
                        </td>
                        <td style="padding: 16px 20px;">
                            <div class="td-title">{{ $v->vendor_name }}</div>
                            <div class="td-subtitle">Owner: {{ $v->owner_name }}</div>
                        </td>
                        <td style="padding: 16px 20px;">
                            <div class="td-title">{{ $v->contact_no }}</div>
                            <div class="td-subtitle">{{ $v->email }}</div>
                        </td>
                        <td style="padding: 16px 20px;">
                            @if($v->is_active)
                                <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Active</span>
                            @else
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Inactive</span>
                            @endif
                        </td>
                        <td style="padding: 16px 20px; text-align: right;">
                            <button class="action-link" style="color: #059669;" onclick='viewVendor(@json($v))' title="View Details">👁️</button>
                            <button class="action-link edit-link" onclick='editVendor(@json($v))'>Edit</button>
                            <button class="action-link delete-link" onclick="deleteVendor('{{ $v->encrypted_id }}')">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="vendorViewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:450px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="flex-shrink: 0;">
            <h2 style="font-size: 18px; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; color: #0f172a;">Vendor Details</h2>
        </div>
        
        <div id="viewModalContent" style="font-size: 14px; line-height: 1.8; color: #334155; overflow-y: auto; padding-right: 8px; flex-grow: 1;"></div>
        
        <div style="flex-shrink: 0; margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('vendorViewModal').style.display='none'" class="btn btn-outline" style="padding: 8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- 1. DYNAMIC LOCATION ENGINE (From Common Layout) ---
    // Handle area_type toggle
    // document.body.addEventListener('change', function(e) {
    //     if(e.target && e.target.name === 'area_type') {
    //         const distId = document.getElementById('district_id') ? document.getElementById('district_id').value : null;
            
    //         if (e.target.value === 'rural') {
    //             document.getElementById('rural_section').style.display = 'block';
    //             document.getElementById('urban_section').style.display = 'none';
    //             if(distId) window.fetchLocationData('/api/get-blocks/', distId, 'block_id');
                
    //             document.getElementById('muni_id').innerHTML = '<option value="">Select Municipality</option>';
    //             document.getElementById('ward_id').innerHTML = '<option value="">Select Ward</option>';
    //         } else if(e.target.value === 'urban') {
    //             document.getElementById('urban_section').style.display = 'block';
    //             document.getElementById('rural_section').style.display = 'none';
    //             if(distId) window.fetchLocationData('/api/get-municipalities/', distId, 'muni_id');
                
    //             document.getElementById('block_id').innerHTML = '<option value="">Select Block</option>';
    //             document.getElementById('gp_id').innerHTML = '<option value="">Select Panchayat</option>';
    //             document.getElementById('vill_id').innerHTML = '<option value="">Select Village</option>';
    //             document.getElementById('post_id').innerHTML = '<option value="">Select Post Office</option>';
    //         }
    //     }
    // });

    // // Reset fields if State changes
    // document.getElementById('state_id').addEventListener('change', function(e) {
    //     document.getElementById('area_type_section').style.display = 'none';
    //     document.getElementById('rural_section').style.display = 'none';
    //     document.getElementById('urban_section').style.display = 'none';
    //     document.querySelectorAll('input[name="area_type"]').forEach(r => r.checked = false);
    // });


    // --- 2. FORM SUBMIT WITH INLINE ERRORS ---
    document.getElementById('vendorForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Check JWT Token
        const token = localStorage.getItem('erp_jwt_token');
        if (!token) {
            toastr.error('Session expired. Please log in again.');
            window.location.href = '/login';
            return;
        }

        // Clear old errors
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.form-control, select, input').forEach(el => el.style.borderColor = '');

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; 
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/vendors/${encId}` : `/api/vendors`;

        // Temporarily enable disabled fields
        const disabledFields = this.querySelectorAll(':disabled');
        disabledFields.forEach(field => field.disabled = false);

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());

        // Re-disable
        disabledFields.forEach(field => field.disabled = true);

        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Content-Type': 'application/json', 
                    'Authorization': 'Bearer ' + token, 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify(payload)
            });
            
            if (!res.ok) {
                const errorData = await res.json();
                throw errorData; 
            }

            const data = await res.json();
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000); 
            
        } catch (error) {
            btn.disabled = false; 
            btn.innerHTML = encId ? 'Update Vendor Details' : 'Save Vendor Details';

            toastr.error(error.message || 'Please fix the errors highlighted below.');
            
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    
                    if (field) {
                        if (field.type === 'radio') {
                            let container = field.closest('.form-group');
                            container.style.border = '1px solid #ef4444';
                            container.style.padding = '8px';
                            container.style.borderRadius = '6px';
                            if (!container.querySelector('.custom-error-text')) {
                                container.insertAdjacentHTML('beforeend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 8px; font-weight: 600;">${msg}</div>`);
                            }
                        } else {
                            field.style.borderColor = '#ef4444';
                            let sibling = field.nextElementSibling;
                            if (sibling && sibling.classList.contains('custom-error-text')) { sibling.remove(); }
                            field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                        }
                    }
                }
            }
        }
    });

    // --- 3. POPULATE FOR EDIT (Awaiting properly) ---
    window.editVendor = async function(record) {
        document.getElementById('formTitle').innerText = 'Edit Vendor';
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('vendor_name').value = record.vendor_name;
        document.getElementById('owner_name').value = record.owner_name;
        document.getElementById('contact_no').value = record.contact_no;
        document.getElementById('email').value = record.email;
        document.getElementById('flat_no').value = record.flat_no;
        document.getElementById('location').value = record.location;
        document.getElementById('address').value = record.address;
        document.getElementById('pin').value = record.pin;
        document.getElementById('is_active').checked = record.is_active == 1;

        // Set State and await District
        document.getElementById('state_id').value = record.state_id;
        await window.fetchLocationData('/api/get-districts/', record.state_id, 'district_id');
        document.getElementById('district_id').value = record.district_id;
        
        document.getElementById('area_type_section').style.display = 'block';

        // Rural
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

        // Urban
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

        document.getElementById('btnSubmit').innerText = 'Update Vendor Details';
    };

    // --- 4. CLEAR FORM ---
    document.getElementById('btnClear').addEventListener('click', () => {
        document.getElementById('vendorForm').reset();
        document.getElementById('encrypted_id').value = '';
        document.getElementById('formTitle').innerText = 'Add New Vendor';
        document.getElementById('btnSubmit').innerText = 'Save Vendor Details';
        document.getElementById('btnSubmit').disabled = false;
        
        document.getElementById('district_id').innerHTML = '<option value="">Select District</option>';
        document.getElementById('district_id').disabled = true;
        document.getElementById('area_type_section').style.display = 'none';
        document.getElementById('rural_section').style.display = 'none';
        document.getElementById('urban_section').style.display = 'none';
    });

    // --- 5. DELETE ---
    window.deleteVendor = async function(encId) {
        if(!confirm('Are you sure you want to soft delete this vendor?')) return;
        try {
            const res = await fetch(`/api/vendors/${encId}`, {
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

    // --- 6. GRID VIEW MODAL ---
    window.viewVendor = function(record) {
        const val = (item) => item ? item : '-';
        
        // 1. Auto-detect Area Type for older database records
        let areaTypeText = '-';
        if (record.area_type) {
            areaTypeText = record.area_type;
        } else if (record.block_id || record.gp_id) {
            areaTypeText = 'rural';
        } else if (record.muni_id || record.ward_id) {
            areaTypeText = 'urban';
        }

        // 2. Dynamically build the Geography HTML based on Area Type
        let geoHtml = '';
        if (areaTypeText === 'rural') {
            geoHtml = `
                <p style="margin:0;"><strong>Block:</strong> <br><span style="color:#0f172a;">${val(record.block?.name)}</span></p>
                <p style="margin:0;"><strong>Gram Panchayat:</strong> <br><span style="color:#0f172a;">${val(record.gram_panchayat?.name)}</span></p>
                <p style="margin:0;"><strong>Village:</strong> <br><span style="color:#0f172a;">${val(record.village?.name)}</span></p>
                <p style="margin:0;"><strong>Post Office:</strong> <br><span style="color:#0f172a;">${val(record.post_office?.name)}</span></p>
            `;
        } else if (areaTypeText === 'urban') {
            geoHtml = `
                <p style="margin:0;"><strong>Municipality / Corp:</strong> <br><span style="color:#0f172a;">${val(record.municipality?.name)}</span></p>
                <p style="margin:0;"><strong>Ward:</strong> <br><span style="color:#0f172a;">${val(record.ward?.name)}</span></p>
            `;
        } else {
            geoHtml = `<p style="margin:0; grid-column: 1 / -1; color: #64748b; font-style: italic;">No local geography data provided.</p>`;
        }

        // 3. Render the Modal
        const content = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>Vendor Name:</strong> <br><span style="color:#0f172a; font-weight:600;">${val(record.vendor_name)}</span></p>
                <p style="margin:0;"><strong>Owner Name:</strong> <br><span style="color:#0f172a;">${val(record.owner_name)}</span></p>
                <p style="margin:0;"><strong>Contact No:</strong> <br><span style="color:#0f172a;">${val(record.contact_no)}</span></p>
                <p style="margin:0;"><strong>Email:</strong> <br><span style="color:#0f172a;">${val(record.email)}</span></p>
                <p style="margin:0; grid-column: 1 / -1;"><strong>Status:</strong> <br>${record.is_active ? '<span style="color:#10b981; font-weight:bold;">Active</span>' : '<span style="color:#ef4444; font-weight:bold;">Inactive</span>'}</p>
            </div>
            
            <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Geography Details</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <p style="margin:0;"><strong>State:</strong> <br><span style="color:#0f172a;">${val(record.state?.name)}</span></p>
                <p style="margin:0;"><strong>District:</strong> <br><span style="color:#0f172a;">${val(record.district?.name)}</span></p>
                <p style="margin:0; grid-column: 1 / -1;"><strong>Area Type:</strong> <br><span style="color:#0f172a; text-transform: capitalize;">${areaTypeText}</span></p>
            </div>

            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                ${geoHtml}
            </div>

            <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Address Details</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>Flat/Shop No:</strong> <br><span style="color:#0f172a;">${val(record.flat_no)}</span></p>
                <p style="margin:0;"><strong>Location/Area:</strong> <br><span style="color:#0f172a;">${val(record.location)}</span></p>
                <p style="margin:0;"><strong>PIN Code:</strong> <br><span style="color:#0f172a;">${val(record.pin)}</span></p>
                <p style="margin:0; grid-column: 1 / -1;"><strong>Complete Address:</strong> <br><span style="color:#0f172a;">${val(record.address)}</span></p>
            </div>
        `;
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('vendorViewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection