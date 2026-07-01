@extends('Offline.layouts.app')
@section('title', 'Category Master - Shoe ERP')
@section('page_title', 'Category Catalog')
@section('content')
<style>
    .cat-layout { height: calc(100vh - 140px); display: flex; flex-direction: column; }
    .cat-layout .view-section { display: none; animation: fadeIn 0.25s ease; }
    .cat-layout .view-section.active { display: flex; flex-direction: column; flex: 1; min-height: 0; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .cat-layout .v-card { background: #fff; border-radius: 14px; border: 1px solid #e8ecf1; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; flex: 1; min-height: 0; }
    .cat-layout .v-card-header { padding: 18px 24px; font-weight: 700; font-size: 15px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e8ecf1; color: #0f172a; letter-spacing: -0.2px; flex-shrink: 0; }
    .cat-layout .v-card-header .v-icon { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; margin-right: 10px; font-size: 14px; }
    .cat-layout .v-card-header .v-icon.form-icon { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
    .cat-layout .v-card-header .v-icon.table-icon { background: linear-gradient(135deg, #059669, #047857); color: #fff; }

    .cat-layout .v-card-body { padding: 24px; flex: 1; overflow-y: auto; }
    .cat-layout .v-card-body::-webkit-scrollbar { width: 5px; }
    .cat-layout .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .cat-layout .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .cat-layout .v-fg { margin-bottom: 18px; }
    .cat-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .cat-layout .v-fg label .required { color: #ef4444; }
    .cat-layout .v-fg .v-input,
    .cat-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .cat-layout .v-fg .v-input:focus,
    .cat-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .cat-layout .v-fg .v-input:hover,
    .cat-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }
    .cat-layout .v-fg textarea.v-input { min-height: 100px; resize: vertical; }

    .cat-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }



    .cat-layout .v-toggle-wrap { display: flex; align-items: center; gap: 12px; padding: 14px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #e8ecf1; }
    .cat-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .cat-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .cat-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .cat-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .cat-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .cat-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
    .cat-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }

    .cat-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 500px; }
    .cat-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .cat-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .cat-layout .v-table th { padding: 14px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .cat-layout .v-table td { padding: 14px 18px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .cat-layout .v-table tbody tr { transition: all 0.15s; }
    .cat-layout .v-table tbody tr:hover td { background: #f8fafc; }
    .cat-layout .v-td-title { font-weight: 600; color: #0f172a; }
    .cat-layout .v-td-sub { font-size: 12px; color: #94a3b8; }
    .cat-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .cat-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .cat-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .cat-layout .v-badge.active .dot { background: #10b981; }
    .cat-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .cat-layout .v-badge.inactive .dot { background: #94a3b8; }

    .cat-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .cat-layout .v-action:hover { transform: translateY(-1px); }
    .cat-layout .v-action-view { color: #059669; }
    .cat-layout .v-action-view:hover { background: #ecfdf5; }
    .cat-layout .v-action-edit { color: #2563eb; }
    .cat-layout .v-action-edit:hover { background: #eff6ff; }
    .cat-layout .v-action-delete { color: #ef4444; }
    .cat-layout .v-action-delete:hover { background: #fef2f2; }

    .cat-layout .v-table-wrap { flex: 1; overflow: auto; }
    .cat-layout .v-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
    .cat-layout .v-table-wrap::-webkit-scrollbar-track { background: transparent; }
    .cat-layout .v-table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    @media (max-width: 992px) {
        .cat-layout .v-grid-2 { grid-template-columns: 1fr !important; }
        .cat-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
        #viewModal > div { width: 95% !important; max-width: 480px !important; }
        #viewModalContent > div { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 576px) {
        .cat-layout .v-card-body { padding: 16px !important; }
        #viewModalContent > div { gap: 8px !important; }
        #viewModal > div { padding: 16px !important; }
    }
</style>
<div class="cat-layout">

    <div id="listView" class="view-section active">
        <section class="v-card">
            <div class="v-card-header">
                <span><span class="v-icon table-icon">📂</span> Categories</span>
                <button class="v-submit" style="width:auto;padding:8px 18px;font-size:12px;" onclick="toggleView('addView', true)">+ Add New Category</button>
            </div>
            
            <div class="v-card-body" style="padding:0;">
                <div class="v-table-wrap">
                    <table id="catTable" class="v-table datatable">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Category Name</th>
                                <th style="width:100px;">Status</th>
                                <th style="width:160px;text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $c)
                            <tr>
                                <td><span class="v-td-title" style="color:#94a3b8;">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div style="display:flex;flex-direction:column;padding-left: {{ $c->depth * 24 }}px;">
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            @if($c->depth > 0)
                                                <span style="color:#cbd5e1;font-family:monospace;font-size:14px;flex-shrink:0;">{{ str_repeat('│  ', $c->depth - 1) }}├── </span>
                                            @endif
                                            @if($c->depth === 0)
                                                <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);color:#4338ca;font-size:12px;flex-shrink:0;">📁</span>
                                            @else
                                                <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:#f1f5f9;color:#64748b;font-size:10px;flex-shrink:0;">📄</span>
                                            @endif
                                            <div>
                                                <span class="v-td-title">{{ $c->name }}</span>
                                                @if($c->ben_name)
                                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px;">({{ $c->ben_name }})</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($c->full_parent_path !== 'None (Main)')
                                            <div style="display:flex;align-items:center;gap:4px;margin-top:2px;padding-left:{{ $c->depth > 0 ? ($c->depth * 10 + 4) : 0 }}px;">
                                                <span style="font-size:10px;color:#94a3b8;">in</span>
                                                <span style="font-size:10px;color:#6366f1;background:#eef2ff;padding:1px 8px;border-radius:4px;font-weight:600;">{{ $c->full_parent_path }}</span>
                                            </div>
                                        @else
                                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;padding-left:{{ $c->depth > 0 ? ($c->depth * 10 + 4) : 0 }}px;">Top-level category</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($c->is_active)
                                        <span class="v-badge active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <button class="v-action v-action-view" onclick='viewRecord(@json($c))' title="View Details">👁️ View</button>
                                    <button class="v-action v-action-edit" onclick='editRecord(@json($c))'>✏️ Edit</button>
                                    <button class="v-action v-action-delete" onclick="deleteRecord('{{ $c->encrypted_id }}')">🗑️ Delete</button>
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
                <span><span class="v-icon form-icon">🏷️</span> <span id="formTitleText">Add New Category</span></span>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="v-reset" onclick="toggleView('listView')">Cancel</button>
                    <button type="button" id="btnSubmitTop" class="v-submit" style="width:auto;padding:8px 18px;font-size:12px;" onclick="document.getElementById('catForm').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))">Save Category</button>
                </div>
            </div>
            <div class="v-card-body">
                <form id="catForm" novalidate>
                    <input type="hidden" id="encrypted_id" name="encrypted_id">
                    
                    <div class="v-grid-2">
                        <div>
                            <div class="v-fg">
                                <label>Category Name (English) <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="v-input" placeholder="e.g. Sneakers, Formal Shoes" required>
                            </div>
                            <div class="v-fg">
                                <label>Bengali Name</label>
                                <input type="text" id="ben_name" name="ben_name" class="v-input" placeholder="e.g. স্নিকার্স">
                            </div>
                            <div class="v-fg">
                                <label>Description</label>
                                <textarea id="cat_des" name="cat_des" class="v-input" placeholder="Briefly describe what types of shoes go into this category..."></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="v-fg">
                                <label>Parent Category <span style="font-weight:400;color:#94a3b8;font-size:11px;">— pick where this category goes</span></label>
                                <select id="parent_id" name="parent_id" class="v-select">
                                    <option value="">-- Top Level (No Parent) --</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent['id'] }}">
                                            {{ $parent->full_path }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="v-fg" style="margin-top:18px;">
                                <label>Status</label>
                                <div class="v-toggle-wrap">
                                    <label class="v-toggle">
                                        <input type="checkbox" id="is_active" name="is_active" checked>
                                        <span class="v-slider"></span>
                                    </label>
                                    <span class="v-toggle-label">Category is Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px 32px; border-radius:12px; width:500px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="flex-shrink:0;display:flex;align-items:center;gap:10px;border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:16px;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:16px;">📂</span>
            <h2 style="font-size:17px;font-weight:700;color:#0f172a;margin:0;">Category Details</h2>
        </div>
        <div id="viewModalContent" style="font-size:14px;line-height:1.8;color:#334155;overflow-y:auto;padding-right:8px;flex-grow:1;"></div>
        <div style="flex-shrink:0;margin-top:20px;display:flex;justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('viewModal').style.display='none'" class="v-reset" style="padding:8px 24px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleView(viewId, isReset) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
        if (isReset) {
            document.getElementById('catForm').reset();
            document.getElementById('encrypted_id').value = '';
            document.getElementById('formTitleText').innerText = 'Add New Category';
            document.getElementById('btnSubmitTop').innerText = 'Save Category';
            window.clearFormErrors();
        }
    }

    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, .v-select, input, select, textarea').forEach(el => el.style.borderColor = '');
    };

    document.getElementById('catForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();
        const btn = document.getElementById('btnSubmitTop');
        btn.disabled = true; btn.innerHTML = 'Processing...';
        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/categories/${encId}` : `/api/categories`;
        const payload = Object.fromEntries(new FormData(this).entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;
        if (payload.parent_id === "") payload.parent_id = null;
        try {
            const res = await fetch(url, {
                method, headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            if (!res.ok) throw await res.json();
            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000);
        } catch (error) {
            btn.disabled = false; btn.innerHTML = encId ? 'Update Category' : 'Save Category';
            toastr.error(error.message || 'Please fix the errors highlighted below.');
            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.style.borderColor = '#ef4444';
                        let parent = field.parentElement;
                        let existing = parent ? parent.querySelector('.custom-error-text') : field.nextElementSibling;
                        if (existing && existing.classList.contains('custom-error-text')) existing.remove();
                        field.insertAdjacentHTML('afterend', `<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">${msg}</div>`);
                    }
                }
            }
        }
    });

    window.editRecord = function(record) {
        window.clearFormErrors();
        toggleView('addView', false);
        document.getElementById('formTitleText').innerText = 'Edit Category';
        document.getElementById('btnSubmitTop').innerText = 'Update Category';
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        document.getElementById('ben_name').value = record.ben_name || '';
        document.getElementById('cat_des').value = record.cat_des || '';
        document.getElementById('parent_id').value = record.parent_id || '';
        document.getElementById('is_active').checked = record.is_active == 1;
    };

    window.deleteRecord = async function(encId) {
        if (!confirm('Are you sure you want to delete this category?')) return;
        try {
            const res = await fetch(`/api/categories/${encId}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();
            if (!res.ok) { if (res.status === 403) { toastr.warning(data.message); return; } throw data; }
            toastr.success(data.message); setTimeout(() => location.reload(), 1000);
        } catch (e) { toastr.error('Deletion failed.'); }
    };

    window.buildHierarchyTree = function(fullPath) {
        if (!fullPath || fullPath === '-') return '<span style="color:#94a3b8;">None (Main Category)</span>';
        const parts = fullPath.split(' > ');
        let html = '<div style="display:flex;flex-direction:column;gap:2px;">';
        parts.forEach((part, i) => {
            const isLast = i === parts.length - 1;
            const colors = ['#6366f1', '#059669', '#d97706', '#dc2626', '#0891b2'];
            const bgColors = ['#eef2ff', '#ecfdf5', '#fffbeb', '#fef2f2', '#ecfeff'];
            const color = colors[i % colors.length];
            const bg = bgColors[i % bgColors.length];
            html += `
                <div style="display:flex;align-items:center;gap:6px;padding:5px 0;">
                    <span style="color:#cbd5e1;font-family:monospace;font-size:14px;width:20px;text-align:center;">
                        ${i === 0 ? '' : (isLast ? '└─' : '├─')}
                    </span>
                    ${i > 0 ? '<span style="color:#cbd5e1;font-family:monospace;">│</span>' : ''}
                    <span style="display:inline-block;padding:4px 12px;border-radius:6px;font-size:13px;font-weight:${isLast ? '700' : '500'};color:${isLast ? '#1e293b' : color};background:${isLast ? '#f8fafc' : bg};border:${isLast ? '2px solid ' + color : '1px solid transparent'};">${part}</span>
                    ${isLast ? '<span style="font-size:10px;color:#94a3b8;font-weight:600;background:#f1f5f9;padding:2px 8px;border-radius:4px;">selected</span>' : ''}
                </div>
            `;
        });
        html += '</div>';
        return html;
    };

    window.viewRecord = function(record) {
        const val = (item) => item ? item : '-';
        const content = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Category Name</strong><br>
                    <span style="color:#0f172a;font-weight:700;font-size:16px;">${val(record.name)}</span>
                </p>
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Bengali Name</strong><br>
                    <span style="color:#0f172a;font-size:15px;">${val(record.ben_name)}</span>
                </p>
            </div>
            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">
            <div style="margin-bottom:6px;">
                <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Hierarchy Path</strong>
            </div>
            <div style="background:#fafbfc;padding:12px 16px;border-radius:10px;border:1px solid #e8ecf1;">
                ${window.buildHierarchyTree(record.full_path)}
            </div>
            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <p style="margin:0;">
                    <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Status</strong><br>
                    ${record.is_active
                        ? '<span style="display:inline-flex;align-items:center;gap:6px;color:#059669;font-weight:700;background:#ecfdf5;padding:4px 12px;border-radius:6px;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;"></span>Active</span>'
                        : '<span style="display:inline-flex;align-items:center;gap:6px;color:#64748b;font-weight:600;background:#f1f5f9;padding:4px 12px;border-radius:6px;"><span style="width:8px;height:8px;border-radius:50%;background:#94a3b8;"></span>Inactive</span>'}
                </p>
            </div>
            ${record.cat_des ? `
            <hr style="border:0;border-top:1px solid #e8ecf1;margin:16px 0;">
            <div style="margin-bottom:8px;">
                <strong style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Description</strong>
            </div>
            <p style="margin:0;color:#0f172a;white-space:pre-wrap;font-size:13px;line-height:1.7;background:#f8fafc;padding:12px;border-radius:8px;">${val(record.cat_des)}</p>
            ` : ''}
        `;
        document.getElementById('viewModalContent').innerHTML = content;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection