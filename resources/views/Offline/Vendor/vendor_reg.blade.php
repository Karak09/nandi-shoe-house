@extends('Offline.layouts.app')
@section('title', 'Vendor Master - Shoe ERP')
@section('page_title', 'Vendor Management')

@section('content')
<div class="vendor-layout">
    
    <section class="v-card form-section">
        <div class="v-card-header">
            <span><span class="v-icon form-icon">📋</span> <span id="formTitle">Add New Vendor</span></span>
            <button type="button" id="btnClear" class="v-reset">⟳ Reset</button>
        </div>
        <div class="v-card-body">
            <form id="vendorForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="v-fg">
                    <label>Vendor Company Name <span class="required">*</span></label>
                    <input type="text" id="vendor_name" name="vendor_name" class="v-input" required>
                </div>
                <div class="v-fg">
                    <label>Owner / Contact Person <span class="required">*</span></label>
                    <input type="text" id="owner_name" name="owner_name" class="v-input" required>
                </div>

                <div class="v-grid-2">
                    <div class="v-fg">
                        <label>Contact No <span class="required">*</span></label>
                        <input type="text" id="contact_no" name="contact_no" class="v-input" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                    </div>
                    <div class="v-fg">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="v-input" placeholder="@gmail.com" required>
                    </div>
                </div>

                <div class="v-divider-label"><span>📍 Vendor Geography</span><span class="dash"></span></div>

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

                <div id="area_type_section" class="v-area-type">
                    <label style="font-size: 12px; font-weight: 600; color: #475569;">Select Area Type <span class="required">*</span></label>
                    <div class="v-area-options">
                        <label><input type="radio" name="area_type" value="rural" id="type_rural"> <span>🏘️ Rural (Gram Panchayat)</span></label>
                        <label><input type="radio" name="area_type" value="urban" id="type_urban"> <span>🏙️ Urban (Municipality)</span></label>
                    </div>
                </div>

                <div id="rural_section" class="v-geo-section">
                    <div class="v-grid-2" style="margin-bottom: 14px;">
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
                            <label>Village (Optional)</label>
                            <select id="vill_id" name="vill_id" class="v-select loc-trigger" data-target="post_id" data-url="/api/get-post-offices-by-village/">
                                <option value="">Select Village</option>
                            </select>
                        </div>
                        <div class="v-fg">
                            <label>Post Office (Optional)</label>
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

                <div class="v-divider-label"><span>📬 Address Details</span><span class="dash"></span></div>

                <div class="v-grid-2">
                    <div class="v-fg">
                        <label>Flat / Building No. <span class="required">*</span></label>
                        <input type="text" id="flat_no" name="flat_no" class="v-input" required>
                    </div>
                    <div class="v-fg">
                        <label>Location / Area <span class="required">*</span></label>
                        <input type="text" id="location" name="location" class="v-input" required>
                    </div>
                    <div class="v-fg">
                        <label>PIN Code <span class="required">*</span></label>
                        <input type="text" id="pin" name="pin" class="v-input" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                    </div>
                </div>
                
                <div class="v-fg">
                    <label>Complete Address <span class="required">*</span></label>
                    <textarea id="address" name="address" class="v-input" rows="3" required></textarea>
                </div>

                <div class="v-fg">
                    <label>Status</label>
                    <div class="v-toggle-wrap">
                        <label class="v-toggle">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="v-slider"></span>
                        </label>
                        <span class="v-toggle-label">Active Vendor</span>
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" id="btnSubmit" class="v-submit">💾 Save Vendor Details</button>
                </div>
            </form>
        </div>
    </section>

    <section class="v-card table-section">
        <div class="v-card-header">
            <span><span class="v-icon table-icon">📦</span> Vendor Directory</span>
        </div>
        <div class="v-table-wrap">
            <table id="vendorTable" class="v-table datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor Info</th>
                        <th>Contact Details</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendors as $v)
                    <tr>
                        <td><span class="v-td-title">{{ $loop->iteration }}</span></td>
                        <td>
                            <div class="v-td-title">{{ $v->vendor_name }}</div>
                            <div class="v-td-sub">Owner: {{ $v->owner_name }}</div>
                        </td>
                        <td>
                            <div class="v-td-title">{{ $v->contact_no }}</div>
                            <div class="v-td-sub">{{ $v->email }}</div>
                        </td>
                        <td>
                            @if($v->is_active)
                                <span class="v-badge active"><span class="dot"></span> Active</span>
                            @else
                                <span class="v-badge inactive"><span class="dot"></span> Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button class="v-action v-action-view" onclick='viewVendor(@json($v))' title="View Details">👁️ View</button>
                            <button class="v-action v-action-edit" onclick='editVendor(@json($v))'>✏️ Edit</button>
                            <button class="v-action v-action-delete" onclick="deleteVendor('{{ $v->encrypted_id }}')">🗑️ Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="vendorViewModal" class="v-modal-overlay" onclick="closeViewModal()">
    <div class="v-modal-box" onclick="event.stopPropagation()">
        <div class="v-modal-header">
            <h2><span class="m-icon">📄</span> Vendor Details</h2>
            <button class="v-modal-close" onclick="closeViewModal()">✕</button>
        </div>
        <div id="viewModalContent" class="v-modal-body"></div>
        <div class="v-modal-footer">
            <button type="button" onclick="closeViewModal()" class="v-reset" style="padding: 8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeViewModal() { document.getElementById('vendorViewModal').classList.remove('show'); }

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
        
        let areaTypeText = '-';
        if (record.area_type) {
            areaTypeText = record.area_type;
        } else if (record.block_id || record.gp_id) {
            areaTypeText = 'rural';
        } else if (record.muni_id || record.ward_id) {
            areaTypeText = 'urban';
        }

        let geoHtml = '';
        if (areaTypeText === 'rural') {
            geoHtml = `
                <p><strong>📍 Block</strong> <br><span>${val(record.block?.name)}</span></p>
                <p><strong>🏘️ Gram Panchayat</strong> <br><span>${val(record.gram_panchayat?.name)}</span></p>
                <p><strong>🌿 Village</strong> <br><span>${val(record.village?.name)}</span></p>
                <p><strong>📮 Post Office</strong> <br><span>${val(record.post_office?.name)}</span></p>
            `;
        } else if (areaTypeText === 'urban') {
            geoHtml = `
                <p><strong>🏛️ Municipality / Corp</strong> <br><span>${val(record.municipality?.name)}</span></p>
                <p><strong>🗳️ Ward</strong> <br><span>${val(record.ward?.name)}</span></p>
            `;
        } else {
            geoHtml = `<p class="full" style="color:#94a3b8;font-style:italic;">No local geography data provided.</p>`;
        }

        const content = `
            <div class="v-mgrid">
                <p><strong>Company</strong> <br><span style="font-weight:600;">${val(record.vendor_name)}</span></p>
                <p><strong>Owner</strong> <br><span>${val(record.owner_name)}</span></p>
                <p><strong>📞 Contact</strong> <br><span>${val(record.contact_no)}</span></p>
                <p><strong>✉️ Email</strong> <br><span>${val(record.email)}</span></p>
                <p class="full"><strong>Status</strong> <br>${record.is_active ? '<span style="color:#10b981;font-weight:600;">● Active</span>' : '<span style="color:#ef4444;font-weight:600;">● Inactive</span>'}</p>
            </div>
            
            <hr class="v-mdivider">
            <div class="v-mlabel"><span>📍 Geography Details</span><span class="ml-dash"></span></div>
            
            <div class="v-mgrid" style="margin-bottom:12px;">
                <p><strong>State</strong> <br><span>${val(record.state?.name)}</span></p>
                <p><strong>District</strong> <br><span>${val(record.district?.name)}</span></p>
                <p class="full"><strong>Area Type</strong> <br><span style="text-transform:capitalize;">${areaTypeText}</span></p>
            </div>

            <div class="v-mgeo-box">
                ${geoHtml}
            </div>

            <hr class="v-mdivider">
            <div class="v-mlabel"><span>📬 Address Details</span><span class="ml-dash"></span></div>
            
            <div class="v-mgrid">
                <p><strong>Flat/Shop No</strong> <br><span>${val(record.flat_no)}</span></p>
                <p><strong>Location/Area</strong> <br><span>${val(record.location)}</span></p>
                <p><strong>PIN Code</strong> <br><span>${val(record.pin)}</span></p>
                <p class="full"><strong>Complete Address</strong> <br><span>${val(record.address)}</span></p>
            </div>
        `;
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('vendorViewModal').classList.add('show');
    };
</script>
@endpush
@endsection