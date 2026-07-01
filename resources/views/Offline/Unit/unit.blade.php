@extends('Offline.layouts.app')
@section('title', 'Unit Master - Shoe ERP')
@section('page_title', 'Unit Management')
@section('content')
<style>
    .unit-layout { display: flex; gap: 24px; height: calc(100vh - 140px); }

    .unit-layout .form-section { flex: 0 0 400px; display: flex; flex-direction: column; }
    .unit-layout .form-section .v-card-body { flex: 1; overflow-y: auto; padding: 24px; }
    .unit-layout .form-section .v-card-body::-webkit-scrollbar { width: 5px; }
    .unit-layout .form-section .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .unit-layout .form-section .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .unit-layout .table-section { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .unit-layout .table-section .v-card-body { flex: 1; overflow: hidden; padding: 0; display: flex; flex-direction: column; }
    .unit-layout .table-section .v-table-wrap { flex: 1; overflow-y: auto; overflow-x: auto; }
    .unit-layout .table-section .v-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
    .unit-layout .table-section .v-table-wrap::-webkit-scrollbar-track { background: transparent; }
    .unit-layout .table-section .v-table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .unit-layout .v-fg { margin-bottom: 20px; }
    .unit-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .unit-layout .v-fg label .required { color: #ef4444; }
    .unit-layout .v-fg .v-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; }
    .unit-layout .v-fg .v-input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .unit-layout .v-fg .v-input:hover { border-color: #94a3b8; background: #fff; }

    .unit-layout .v-toggle-wrap { display: flex; align-items: center; gap: 14px; padding: 16px 18px; background: #f8fafc; border-radius: 12px; border: 1px solid #e8ecf1; }
    .unit-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .unit-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .unit-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .unit-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .unit-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .unit-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
    .unit-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }
    .unit-layout .v-toggle-desc { font-size: 11px; color: #94a3b8; display: block; margin-top: 2px; }

    .unit-layout .v-submit { width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
    .unit-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .unit-layout .v-submit:active { transform: translateY(0); }
    .unit-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .unit-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 550px; }
    .unit-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .unit-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .unit-layout .v-table th { padding: 14px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .unit-layout .v-table td { padding: 16px 20px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .unit-layout .v-table tbody tr { transition: all 0.15s; }
    .unit-layout .v-table tbody tr:hover td { background: #f8fafc; }
    .unit-layout .v-td-title { font-weight: 600; color: #0f172a; margin-bottom: 3px; }

    .unit-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .unit-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .unit-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .unit-layout .v-badge.active .dot { background: #10b981; }
    .unit-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .unit-layout .v-badge.inactive .dot { background: #94a3b8; }

    .unit-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .unit-layout .v-action:hover { transform: translateY(-1px); }
    .unit-layout .v-action-edit { color: #2563eb; }
    .unit-layout .v-action-edit:hover { background: #eff6ff; }
    .unit-layout .v-action-delete { color: #ef4444; }
    .unit-layout .v-action-delete:hover { background: #fef2f2; }

    @media (max-width: 992px) {
        .unit-layout { flex-direction: column !important; height: auto !important; gap: 16px !important; }
        .unit-layout .form-section { flex: none !important; width: 100% !important; }
        .unit-layout .form-section .v-card-body { max-height: none !important; }
        .unit-layout .table-section { width: 100% !important; min-height: 300px !important; }
    }
    @media (max-width: 576px) {
        .unit-layout .v-card-body { padding: 16px !important; }
    }
</style>
<div class="unit-layout">
    
    <section class="v-card form-section">
        <div class="v-card-header">
            <span><span class="v-icon form-icon">📏</span> <span id="formTitle">Add New Unit</span></span>
            <button type="button" id="btnClear" class="v-reset">Reset</button>
        </div>
        <div class="v-card-body">
            <form id="unitForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="v-fg">
                    <label>Unit Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="v-input" placeholder="e.g. Pair, Box, Dozen" required autofocus>
                </div>

                <div class="v-fg">
                    <label>Unit Symbol / Keyword <span class="required">*</span></label>
                    <input type="text" id="keyword" name="keyword" class="v-input" placeholder="e.g. PR, BX, DZ" style="text-transform: uppercase;" required>
                </div>

                <hr class="v-divider" style="margin:24px 0 18px 0;border:0;border-top:1px solid #e8ecf1;">
                <div class="v-fg">
                    <label>System Status</label>
                    <div class="v-toggle-wrap">
                        <label class="v-toggle">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="v-slider"></span>
                        </label>
                        <div>
                            <span class="v-toggle-label">Active Unit</span>
                            <span class="v-toggle-desc">Available for selection in products</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e8ecf1;">
                    <button type="submit" id="btnSubmit" class="v-submit">Save Unit</button>
                </div>
            </form>
        </div>
    </section>

    <section class="v-card table-section">
        <div class="v-card-header">
            <span><span class="v-icon table-icon">📋</span> Active Units List</span>
        </div>
        <div class="v-card-body">
            <div class="v-table-wrap">
                <table id="dataTable" class="v-table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Unit Name</th>
                            <th>Symbol / Keyword</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($units as $u)
                        <tr>
                            <td><span class="v-td-title">{{ $loop->iteration }}</span></td>
                            <td><span class="v-td-title">{{ $u->name }}</span></td>
                            <td><span style="font-family: monospace; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #475569; border: 1px solid #e2e8f0;">{{ $u->keyword }}</span></td>
                            <td>
                                @if($u->is_active)
                                    <span class="v-badge active"><span class="dot"></span>Active</span>
                                @else
                                    <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <button class="v-action v-action-edit" onclick='editRecord(@json($u))'>✏️ Edit</button>
                                <button class="v-action v-action-delete" onclick="deleteRecord('{{ $u->encrypted_id }}')">🗑️ Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, .v-select, select, textarea, input').forEach(el => el.style.borderColor = '');
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
            btn.disabled = false; btn.innerHTML = encId ? 'Update Unit' : 'Save Unit';
            if (error.errors) {
                for (let field in error.errors) {
                    let el = document.querySelector(`[name="${field}"]`);
                    if (el) {
                        el.style.borderColor = '#ef4444';
                        let parent = el.parentElement;
                        let existing = parent ? parent.querySelector('.custom-error-text') : el.nextElementSibling;
                        if (existing && existing.classList.contains('custom-error-text')) { existing.remove(); }
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
        document.getElementById('btnSubmit').innerText = 'Save Unit';
    });

    window.deleteRecord = async function(encId) {
        if(!confirm('Delete this unit?')) return;
        try {
            const res = await fetch(`/api/units/${encId}`, { 
                method: 'DELETE', 
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
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