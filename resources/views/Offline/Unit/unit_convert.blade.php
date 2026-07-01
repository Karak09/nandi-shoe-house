@extends('Offline.layouts.app')
@section('title', 'Unit Conversion - Shoe ERP')
@section('page_title', 'Unit Conversion Management')
@section('content')
<style>
    .conv-layout { display: flex; gap: 24px; height: calc(100vh - 140px); }

    .conv-layout .form-section { flex: 0 0 440px; display: flex; flex-direction: column; }
    .conv-layout .form-section .v-card-body { flex: 1; overflow-y: auto; padding: 24px; }
    .conv-layout .form-section .v-card-body::-webkit-scrollbar { width: 5px; }
    .conv-layout .form-section .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .conv-layout .form-section .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .conv-layout .table-section { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .conv-layout .table-section .v-card-body { flex: 1; overflow: hidden; padding: 0; display: flex; flex-direction: column; }
    .conv-layout .table-section .v-table-wrap { flex: 1; overflow-y: auto; overflow-x: auto; }
    .conv-layout .table-section .v-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
    .conv-layout .table-section .v-table-wrap::-webkit-scrollbar-track { background: transparent; }
    .conv-layout .table-section .v-table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .conv-layout .v-fg { margin-bottom: 18px; }
    .conv-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .conv-layout .v-fg label .required { color: #ef4444; }
    .conv-layout .v-fg .v-input,
    .conv-layout .v-fg .v-select { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .conv-layout .v-fg .v-input:focus,
    .conv-layout .v-fg .v-select:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .conv-layout .v-fg .v-input:hover,
    .conv-layout .v-fg .v-select:hover { border-color: #94a3b8; background: #fff; }

    .conv-layout .v-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .conv-layout .conv-visual { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 16px 18px; border: 1.5px dashed #d1d5db; border-radius: 12px; margin-bottom: 18px; font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 600; color: #64748b; }
    .conv-layout .conv-visual .arrow { color: #2563eb; font-weight: bold; font-size: 18px; }

    .conv-layout .v-toggle-wrap { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e8ecf1; margin-bottom: 12px; }
    .conv-layout .v-toggle-wrap .v-toggle-text { font-size: 13px; font-weight: 500; color: #1e293b; }
    .conv-layout .v-toggle { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
    .conv-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .conv-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .conv-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .conv-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .conv-layout .v-toggle input:checked + .v-slider:before { transform: translateX(18px); }

    .conv-layout .v-submit { width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
    .conv-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .conv-layout .v-submit:active { transform: translateY(0); }
    .conv-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .conv-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 700px; }
    .conv-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .conv-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .conv-layout .v-table th { padding: 14px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .conv-layout .v-table td { padding: 14px 18px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .conv-layout .v-table tbody tr { transition: all 0.15s; }
    .conv-layout .v-table tbody tr:hover td { background: #f8fafc; }
    .conv-layout .v-td-title { font-weight: 600; color: #0f172a; margin-bottom: 3px; }
    .conv-layout .v-td-sub { font-size: 11px; color: #94a3b8; }

    .conv-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .conv-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .conv-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .conv-layout .v-badge.active .dot { background: #10b981; }
    .conv-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .conv-layout .v-badge.inactive .dot { background: #94a3b8; }
    .conv-layout .v-badge.yes { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .conv-layout .v-badge.yes .dot { background: #10b981; }
    .conv-layout .v-badge.no { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    .conv-layout .v-badge.no .dot { background: #94a3b8; }

    .conv-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .conv-layout .v-action:hover { transform: translateY(-1px); }
    .conv-layout .v-action-edit { color: #2563eb; }
    .conv-layout .v-action-edit:hover { background: #eff6ff; }
    .conv-layout .v-action-delete { color: #ef4444; }
    .conv-layout .v-action-delete:hover { background: #fef2f2; }

    @media (max-width: 992px) {
        .conv-layout { flex-direction: column !important; height: auto !important; gap: 16px !important; }
        .conv-layout .form-section { flex: none !important; width: 100% !important; }
        .conv-layout .form-section .v-card-body { max-height: 400px !important; }
        .conv-layout .table-section { width: 100% !important; min-height: 300px !important; }
        .conv-layout .v-grid-2 { grid-template-columns: 1fr !important; }
        .conv-layout .v-card-header { flex-wrap: wrap !important; gap: 12px !important; }
    }
    @media (max-width: 576px) {
        .conv-layout .v-card-body { padding: 16px !important; }
    }
</style>
<div class="conv-layout">
    
    <section class="v-card form-section">
        <div class="v-card-header">
            <span><span class="v-icon form-icon">🔄</span> <span id="formTitle">Unit Conversion Rule</span></span>
            <button type="button" id="btnClear" class="v-reset">Reset</button>
        </div>
        <div class="v-card-body">
            <form id="convertForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="v-fg">
                    <label>Conversion Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="v-input" placeholder="e.g. Box to Pairs" required>
                </div>

                <div class="conv-visual">
                    <span>1 <span id="vis-from">[From]</span></span>
                    <span class="arrow">➔</span>
                    <span><span id="vis-factor">[Factor]</span> <span id="vis-to">[To]</span></span>
                </div>

                <div class="v-grid-2">
                    <div class="v-fg">
                        <label>From Unit <span class="required">*</span></label>
                        <select id="from_unit" name="from_unit" class="v-select" required>
                            <option value="">Select...</option>
                            @foreach($units as $u) <option value="{{ $u->id }}" data-key="{{ $u->keyword }}">{{ $u->name }} ({{ $u->keyword }})</option> @endforeach
                        </select>
                    </div>
                    <div class="v-fg">
                        <label>To Unit <span class="required">*</span></label>
                        <select id="to_unit" name="to_unit" class="v-select" required>
                            <option value="">Select...</option>
                            @foreach($units as $u) <option value="{{ $u->id }}" data-key="{{ $u->keyword }}">{{ $u->name }} ({{ $u->keyword }})</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="v-grid-2">
                    <div class="v-fg">
                        <label>Unit Factor <span class="required">*</span></label>
                        <input type="number" step="0.001" id="unit_factor" name="unit_factor" class="v-input" placeholder="e.g. 10.000" required>
                    </div>
                    <div class="v-fg">
                        <label>Price Factor <span class="required">*</span></label>
                        <input type="number" step="0.001" id="price_factor" name="price_factor" class="v-input" placeholder="e.g. 1.000" required>
                    </div>
                </div>

                <hr class="v-divider" style="margin:22px 0 18px 0;border:0;border-top:1px solid #e8ecf1;">

                <div class="v-toggle-wrap">
                    <span class="v-toggle-text">Packet Entry?</span>
                    <label class="v-toggle"><input type="checkbox" id="packet" name="packet"><span class="v-slider"></span></label>
                </div>
                <div class="v-toggle-wrap">
                    <span class="v-toggle-text">Active Status</span>
                    <label class="v-toggle"><input type="checkbox" id="is_active" name="is_active" checked><span class="v-slider"></span></label>
                </div>

                <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e8ecf1;">
                    <button type="submit" id="btnSubmit" class="v-submit">Save Rule</button>
                </div>
            </form>
        </div>
    </section>

    <section class="v-card table-section">
        <div class="v-card-header">
            <span><span class="v-icon table-icon">📋</span> Conversion Rules Directory</span>
        </div>
        <div class="v-card-body">
            <div class="v-table-wrap">
                <table id="dataTable" class="v-table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Rule Name</th>
                            <th>Conversion Formula</th>
                            <th>Unit / Price Factor</th>
                            <th>Packet</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conversions as $c)
                        <tr>
                            <td><span class="v-td-title">{{ $loop->iteration }}</span></td>
                            <td><span class="v-td-title">{{ $c->name }}</span></td>
                            <td>
                                <span style="font-family:monospace;background:#f1f5f9;padding:4px 8px;border-radius:4px;font-size:12px;color:#475569;border:1px solid #e2e8f0;white-space:nowrap;">1 {{ $c->fromUnit->keyword ?? '?' }} ➔ {{ number_format($c->unit_factor, 2) }} {{ $c->toUnit->keyword ?? '?' }}</span>
                            </td>
                            <td>
                                <div class="v-td-title" style="font-size:12px;">U: {{ number_format($c->unit_factor, 3) }}</div>
                                <div class="v-td-sub">P: {{ number_format($c->price_factor, 3) }}</div>
                            </td>
                            <td>
                                @if($c->packet)
                                    <span class="v-badge yes"><span class="dot"></span>Yes</span>
                                @else
                                    <span class="v-badge no"><span class="dot"></span>No</span>
                                @endif
                            </td>
                            <td>
                                @if($c->is_active)
                                    <span class="v-badge active"><span class="dot"></span>Active</span>
                                @else
                                    <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <button class="v-action v-action-edit" onclick='editRecord(@json($c))'> ✏️ Edit</button>
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

@push('scripts')
<script>
    function updateVisual() {
        const fromSel = document.getElementById('from_unit');
        const toSel = document.getElementById('to_unit');
        const factor = document.getElementById('unit_factor').value;
        document.getElementById('vis-from').innerText = fromSel.options[fromSel.selectedIndex]?.getAttribute('data-key') || '[From]';
        document.getElementById('vis-to').innerText = toSel.options[toSel.selectedIndex]?.getAttribute('data-key') || '[To]';
        document.getElementById('vis-factor').innerText = factor || '[Factor]';
    }
    document.getElementById('from_unit').addEventListener('change', updateVisual);
    document.getElementById('to_unit').addEventListener('change', updateVisual);
    document.getElementById('unit_factor').addEventListener('input', updateVisual);

    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, .v-select, select, textarea, input').forEach(el => el.style.borderColor = '');
    };

    document.getElementById('convertForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        window.clearFormErrors();

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const url = encId ? `/api/unit-conversions/${encId}` : `/api/unit-conversions`;

        const payload = Object.fromEntries(new FormData(this).entries());
        payload.is_active = document.getElementById('is_active').checked ? 1 : 0;
        payload.packet = document.getElementById('packet').checked ? 1 : 0;

        try {
            const res = await fetch(url, {
                method: encId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            });
            if (!res.ok) throw await res.json();
            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000); 
        } catch (error) {
            btn.disabled = false; btn.innerHTML = encId ? 'Update Rule' : 'Save Rule';
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
        document.getElementById('formTitle').innerText = 'Edit Rule';
        document.getElementById('btnSubmit').innerText = 'Update Rule';
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('name').value = record.name;
        // Fix: from_unit/to_unit are serialized as objects (relation data) instead of raw IDs
        document.getElementById('from_unit').value = typeof record.from_unit === 'object' && record.from_unit ? record.from_unit.id : record.from_unit;
        document.getElementById('to_unit').value = typeof record.to_unit === 'object' && record.to_unit ? record.to_unit.id : record.to_unit;
        document.getElementById('unit_factor').value = record.unit_factor;
        document.getElementById('price_factor').value = record.price_factor;
        document.getElementById('packet').checked = record.packet == 1;
        document.getElementById('is_active').checked = record.is_active == 1;
        updateVisual();
    };

    document.getElementById('btnClear').addEventListener('click', () => {
        window.clearFormErrors();
        document.getElementById('convertForm').reset();
        document.getElementById('encrypted_id').value = '';
        document.getElementById('formTitle').innerText = 'Add Conversion Rule';
        document.getElementById('btnSubmit').innerText = 'Save Rule';
        updateVisual();
    });

    window.deleteRecord = async function(encId) {
        if(!confirm('Delete this rule?')) return;
        try {
            const res = await fetch(`/api/unit-conversions/${encId}`, { 
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