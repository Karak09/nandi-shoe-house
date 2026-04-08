@extends('Offline.layouts.app')
@section('title', 'Professional Unit Master - Shoe ERP')
@section('content')

<div class="workspace-unit">
    <section class="form-section-u card-u">
        <div class="card-header-u">
            <span id="formTitle">Add New Unit</span>
            <button type="button" id="btnClear" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Reset</button>
        </div>
        <div class="card-body-u">
            <form id="unitForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="form-group-u">
                    <label class="form-label-u">Unit Name<span style="color:red">*</span></label>
                    <input type="text" id="name" name="name" class="form-control-u" placeholder="e.g. Pair, Box, Dozen" required autofocus>
                </div>

                <div class="form-group-u">
                    <label class="form-label-u">Unit Symbol/Keyword<span style="color:red">*</span></label>
                    <input type="text" id="keyword" name="keyword" class="form-control-u" placeholder="e.g. PR, BX, DZ" style="text-transform: uppercase;" required>
                </div>

                <div class="form-group-u" style="margin-top: 24px;">
                    <label class="form-label-u">System Status</label>
                    <div class="toggle-wrapper-u">
                        <label class="toggle-switch-u">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="slider-u"></span>
                        </label>
                        <div>
                            <span class="toggle-label-u">Active Unit</span>
                            <span class="toggle-desc-u">Available for selection in products</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions-u">
                    <button type="button" id="btnClear" class="btn-u btn-secondary-u">Clear</button>
                    <button type="submit" id="btnSubmit" class="btn-u btn-primary-u">Save Unit</button>
                </div>
            </form>
        </div>
    </section>

    <section class="table-section-u card-u">
        <div class="card-header-u">
            <span>Active Units List</span>
        </div>
        <div class="card-body-u" style="padding: 10px;">
            <table class="datatable" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #f8fafc;">
                    <tr>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border);">ID</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border);">Unit Name</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border);">Symbol / Keyword</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border);">Status</th>
                        <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); text-align: right;" data-sortable="false">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $u)
                    <tr>
                        <td style="padding: 16px 24px; border-bottom: 1px solid var(--border);"><div class="td-title-u">{{ $loop->iteration }}</div></td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid var(--border);"><div class="td-title-u">{{ $u->name }}</div></td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid var(--border);"><span class="td-keyword-u">{{ $u->keyword }}</span></td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid var(--border);">
                            @if($u->is_active)
                                <span class="status-badge-u status-active-u">Active</span>
                            @else
                                <span class="status-badge-u status-inactive-u">Inactive</span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid var(--border); text-align: right;">
                            <button class="action-link-u edit-link-u" onclick='editRecord(@json($u))'>Edit</button>
                            <button class="action-link-u delete-link-u" onclick="deleteRecord('{{ $u->encrypted_id }}')">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

@push('scripts')
<script>
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.form-control-u').forEach(el => el.style.borderColor = '');
    };

    document.getElementById('unitForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const url = encId ? `/api/units/${encId}` : `/api/units`;

        const payload = Object.fromEntries(new FormData(this).entries());
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
            btn.disabled = false; btn.innerHTML = encId ? 'Update Unit' : '+ Save Unit';
            if (error.errors) {
                for (let field in error.errors) {
                    let el = document.querySelector(`[name="${field}"]`);
                    if (el) {
                        el.style.borderColor = '#ef4444';
                        el.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color: #ef4444; font-size: 11px; margin-top: 4px; font-weight: 600;">${error.errors[field][0]}</div>`);
                    }
                }
            } else toastr.error('Failed to save.');
        }
    });

    window.editRecord = function(record) {
        window.clearFormErrors();
        document.getElementById('formTitle').innerText = 'Edit Unit';
        document.getElementById('btnSubmit').innerText = 'Update Unit';
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        document.getElementById('keyword').value = record.keyword;
        document.getElementById('is_active').checked = record.is_active == 1;
    };

    document.getElementById('btnClear').addEventListener('click', () => {
        window.clearFormErrors();
        document.getElementById('unitForm').reset();
        document.getElementById('encrypted_id').value = '';
        document.getElementById('formTitle').innerText = 'Add New Unit';
        document.getElementById('btnSubmit').innerText = '+ Save Unit';
    });

    window.deleteRecord = async function(encId) {
        if(!confirm('Delete this unit?')) return;
        try {
            const res = await fetch(`/api/units/${encId}`, { 
                method: 'DELETE', 
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            
            // Handles the specific 403 Product In Use error beautifully!
            if(!res.ok) {
                if(res.status === 403) { toastr.warning(data.message); return; }
                throw data;
            }
            toastr.success(data.message); setTimeout(() => location.reload(), 1000);
        } catch (e) { toastr.error('Error deleting.'); }
    };
</script>
@endpush
@endsection