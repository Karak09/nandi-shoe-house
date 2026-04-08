@extends('Offline.layouts.app')
@section('title', 'Professional Category Master - Shoe ERP')
@section('page_title', 'Category Catalog')

@section('content')
<div class="cat-wrapper">
    <div class="content-area">
        
        <div id="listView" class="view-section active">
            <div class="header-actions">
                <div class="page-title">
                    <h1>Categories</h1>
                    <p>Manage your shoe hierarchy, from main departments to specific styles.</p>
                </div>
                <button class="btn btn-primary" onclick="toggleView('addView', true)">+ Add New Category</button>
            </div>

            <div class="card-full" style="padding: 20px;"> <table id="catTable" class="datatable"> <thead style="background: #f8fafc;">
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Full Path</th>
                            <th>Category ID</th>
                            <th>Code Prefix</th>
                            <th>Status</th>
                            <th style="text-align:right;" data-sortable="false">Actions</th> </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            
                            <td style="padding-left: {{ ($c->depth * 20) + 10 }}px;">
                                @if($c->depth > 0)
                                    <span style="color: #cbd5e1; font-family: monospace; font-size: 16px;">{{ str_repeat('│  ', $c->depth - 1) }}├── </span>
                                @endif
                                <div style="display: inline-block; vertical-align: top;">
                                    <div class="cat-name">{{ $c->name }}</div>
                                    @if($c->ben_name) <div class="cat-ben">{{ $c->ben_name }}</div> @endif
                                </div>
                            </td>
                            
                            <td style="font-size: 12px; color: #64748b;">{{ $c->full_parent_path }}</td>
                            <td style="font-weight: 500;">{{ $c->cat_id }}</td>
                            <td><span style="background:#e0e7ff; color:#4338ca; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600;">{{ $c->cat_code }}</span></td>
                            <td>
                                @if($c->is_active)
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Active</span>
                                @else
                                    <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <button class="action-link" style="color: #059669;" onclick='viewRecord(@json($c))' title="View Details">👁️</button>
                                <button class="action-link edit-link" onclick='editRecord(@json($c))'>Edit</button>
                                <button class="action-link delete-link" onclick="deleteRecord('{{ $c->encrypted_id }}')">Delete</button>
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
                    <h1 id="formTitleText">Add New Category</h1>
                    <p>Create a new category classification for your products.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="toggleView('listView')">Cancel & Back</button>
                    <button type="button" id="btnSubmitTop" class="btn btn-primary" onclick="document.getElementById('catForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Category</button>
                </div>
            </div>

            <form id="catForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="form-layout">
                    <div>
                        <div class="form-card">
                            <h2 class="card-title">General Information</h2>
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label class="form-label">Category Name (English) <span style="color:red">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Sneakers, Formal Shoes" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label class="form-label">Bengali Name (Bengali)</label>
                                <input type="text" id="ben_name" name="ben_name" class="form-control" placeholder="e.g. স্নিকার্স">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Category Description</label>
                                <textarea id="cat_des" name="cat_des" class="form-control" style="min-height: 120px;" placeholder="Briefly describe what types of shoes go into this category..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="form-card">
                            <h2 class="card-title">Hierarchy & Codes</h2>
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label class="form-label">Parent Category</label>
                                <select id="parent_id" name="parent_id" class="form-control">
                                    <option value="">-- Main Category (No Parent) --</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px dashed #cbd5e1; margin-bottom: 16px;">
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label class="form-label" style="color: #64748b;">Category ID</label>
                                    <input type="text" id="cat_id" class="form-control" placeholder="Auto-generated on Save" disabled style="background: #e2e8f0; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="color: #64748b;">Product Code Prefix</label>
                                    <input type="text" id="cat_code" class="form-control" placeholder="Auto-generated on Save" disabled style="background: #e2e8f0; cursor: not-allowed;">
                                </div>
                            </div>
                        </div>

                        <div class="form-card">
                            <h2 class="card-title">Status</h2>
                            <div class="check-group">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <label for="is_active" class="form-label" style="margin:0; font-weight:600; color:#10b981;">Category is Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:450px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="flex-shrink: 0;">
            <h2 style="font-size: 18px; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; color: #0f172a;">Category Details</h2>
        </div>
        
        <div id="viewModalContent" style="font-size: 14px; line-height: 1.8; color: #334155; overflow-y: auto; padding-right: 8px; flex-grow: 1;"></div>
        
        <div style="flex-shrink: 0; margin-top: 24px; display: flex; justify-content: flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="btn btn-outline" style="padding: 8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // --- UI TOGGLE ---
    function toggleView(viewId, isReset = false) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        document.querySelector('.content-area').scrollTop = 0;

        if(isReset) {
            document.getElementById('catForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add New Category';
            document.getElementById('btnSubmitTop').innerText = 'Save Category';
            
            // Clear auto-gen visual placeholders
            document.getElementById('cat_id').value = '';
            document.getElementById('cat_code').value = '';
            
            window.clearFormErrors();
        }
    }

    // --- HELPER: CLEAR ERRORS ---
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.form-control, input').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('catForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true; 
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/categories/${encId}` : `/api/categories`;

        const formData = new FormData(this);
        const payload = Object.fromEntries(formData.entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;
        
        if(payload.parent_id === "") payload.parent_id = null;

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
            
            if (!res.ok) {
                const errorData = await res.json();
                throw errorData; 
            }

            const data = await res.json();
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000); 
            
        } catch (error) {
            btn.disabled = false; 
            btn.innerHTML = encId ? 'Update Category' : 'Save Category';
            toastr.error(error.message || 'Please fix the errors highlighted below.');
            
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.style.borderColor = '#ef4444';
                        field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${msg}</div>`);
                    }
                }
            }
        }
    });

    // --- EDIT RECORD ---
    window.editRecord = function(record) {
        window.clearFormErrors();
        toggleView('addView');

        document.getElementById('formTitleText').innerText = 'Edit Category';
        document.getElementById('btnSubmitTop').innerText = 'Update Category';
        
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        document.getElementById('ben_name').value = record.ben_name || '';
        document.getElementById('cat_des').value = record.cat_des || '';
        document.getElementById('parent_id').value = record.parent_id || '';
        document.getElementById('is_active').checked = record.is_active == 1;

        // Populate visual placeholders (Still readonly, cannot be changed by user!)
        document.getElementById('cat_id').value = record.cat_id;
        document.getElementById('cat_code').value = record.cat_code;
    };

    // --- DELETE RECORD ---
    window.deleteRecord = async function(encId) {
        if(!confirm('Are you sure you want to delete this category?')) return;
        try {
            const res = await fetch(`/api/categories/${encId}`, {
                method: 'DELETE',
                headers: { 
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            });
            const data = await res.json();
            
            // Handle specific protection error (HTTP 403)
            if(!res.ok) {
                if (res.status === 403) {
                    toastr.warning(data.message); // Warn them about children
                    return;
                }
                throw data;
            }
            
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } catch (error) { toastr.error('Deletion failed.'); }
    };

    // --- VIEW MODAL ---
    window.viewRecord = function(record) {
        const val = (item) => item ? item : '-';
        
        const content = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>Category Name:</strong> <br><span style="color:#0f172a; font-weight:600;">${val(record.name)}</span></p>
                <p style="margin:0;"><strong>Bengali Name:</strong> <br><span style="color:#0f172a;">${val(record.ben_name)}</span></p>
                <p style="margin:0; grid-column: 1 / -1;"><strong>Parent Category:</strong> <br>
                    <span style="color:#059669; font-weight:600; background: #dcfce7; padding: 4px 8px; border-radius: 6px;">
                        ${record.full_path}
                    </span>
                </p>
                <p style="margin:0;"><strong>Status:</strong> <br>${record.is_active ? '<span style="color:#10b981; font-weight:bold;">Active</span>' : '<span style="color:#ef4444; font-weight:bold;">Inactive</span>'}</p>
            </div>
            
            <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
            
            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <p style="margin:0;"><strong>Category ID:</strong> <br><span style="color:#0f172a; font-family: monospace; font-size: 16px;">${val(record.cat_id)}</span></p>
                <p style="margin:0;"><strong>Code Prefix:</strong> <br><span style="background:#e0e7ff; color:#4338ca; padding:4px 8px; border-radius:4px; font-weight:600;">${val(record.cat_code)}</span></p>
            </div>

            <hr style="border:0; border-top: 1px dashed #cbd5e1; margin: 16px 0;">
            <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Description</div>
            
            <div>
                <p style="margin:0; color:#0f172a; white-space: pre-wrap;">${val(record.cat_des)}</p>
            </div>
        `;
        
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection