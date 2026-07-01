@extends('Offline.layouts.app')
@section('title', 'Colour Master - Shoe ERP')
@section('page_title', 'Colour Management')
@section('content')
<style>
    .colour-layout { display: flex; gap: 24px; height: calc(100vh - 140px); }

    .colour-layout .form-section { flex: 0 0 400px; display: flex; flex-direction: column; }
    .colour-layout .form-section .v-card-body { flex: 1; overflow-y: auto; padding: 24px; }
    .colour-layout .form-section .v-card-body::-webkit-scrollbar { width: 5px; }
    .colour-layout .form-section .v-card-body::-webkit-scrollbar-track { background: transparent; }
    .colour-layout .form-section .v-card-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .colour-layout .table-section { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .colour-layout .table-section .v-card-body { flex: 1; overflow: hidden; padding: 0; display: flex; flex-direction: column; }
    .colour-layout .table-section .v-table-wrap { flex: 1; overflow-y: auto; overflow-x: auto; }
    .colour-layout .table-section .v-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
    .colour-layout .table-section .v-table-wrap::-webkit-scrollbar-track { background: transparent; }
    .colour-layout .table-section .v-table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .colour-layout .v-fg { margin-bottom: 20px; }
    .colour-layout .v-fg label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; letter-spacing: 0.2px; }
    .colour-layout .v-fg label .required { color: #ef4444; }
    .colour-layout .v-fg .v-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #0f172a; outline: none; transition: all 0.25s ease; background: #fafbfc; box-sizing: border-box; }
    .colour-layout .v-fg .v-input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
    .colour-layout .v-fg .v-input:hover { border-color: #94a3b8; background: #fff; }

    .colour-layout .colour-picker-row { display: flex; gap: 12px; align-items: flex-end; }
    .colour-layout .colour-picker-row .v-fg { flex: 1; margin-bottom: 0; }
    .colour-layout .colour-swatch { width: 48px; height: 48px; border-radius: 10px; border: 2px solid #e2e8f0; flex-shrink: 0; cursor: pointer; transition: 0.2s; }
    .colour-layout .colour-swatch:hover { transform: scale(1.05); }
    .colour-layout input[type="color"] { width: 48px; height: 48px; padding: 2px; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; background: none; flex-shrink: 0; }
    .colour-layout input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
    .colour-layout input[type="color"]::-webkit-color-swatch { border: none; border-radius: 8px; }

    .colour-layout .v-toggle-wrap { display: flex; align-items: center; gap: 14px; padding: 16px 18px; background: #f8fafc; border-radius: 12px; border: 1px solid #e8ecf1; }
    .colour-layout .v-toggle { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
    .colour-layout .v-toggle input { opacity: 0; width: 0; height: 0; }
    .colour-layout .v-toggle .v-slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; transition: 0.3s; border-radius: 34px; }
    .colour-layout .v-toggle .v-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: 0.3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
    .colour-layout .v-toggle input:checked + .v-slider { background: linear-gradient(135deg, #10b981, #059669); }
    .colour-layout .v-toggle input:checked + .v-slider:before { transform: translateX(20px); }
    .colour-layout .v-toggle-label { font-size: 13px; font-weight: 600; color: #1e293b; }

    .colour-layout .v-submit { width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); letter-spacing: 0.2px; }
    .colour-layout .v-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
    .colour-layout .v-submit:active { transform: translateY(0); }
    .colour-layout .v-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .colour-layout .v-reset { padding: 6px 14px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
    .colour-layout .v-reset:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }

    .colour-layout .v-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; min-width: 500px; }
    .colour-layout .v-table thead { position: sticky; top: 0; z-index: 2; }
    .colour-layout .v-table thead tr { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .colour-layout .v-table th { padding: 14px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .colour-layout .v-table td { padding: 16px 20px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #0f172a; vertical-align: middle; transition: background 0.15s; }
    .colour-layout .v-table tbody tr { transition: all 0.15s; }
    .colour-layout .v-table tbody tr:hover td { background: #f8fafc; }

    .colour-layout .colour-cell { display: flex; align-items: center; gap: 10px; }
    .colour-layout .colour-dot { width: 28px; height: 28px; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0; }
    .colour-layout .colour-hex { font-family: monospace; font-size: 12px; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; }

    .colour-layout .v-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; }
    .colour-layout .v-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .colour-layout .v-badge.active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .colour-layout .v-badge.inactive { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

    .colour-layout .v-action { background: none; border: none; cursor: pointer; padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: all 0.2s; }
    .colour-layout .v-action:hover { transform: translateY(-1px); }
    .colour-layout .v-action-edit { color: #2563eb; }
    .colour-layout .v-action-edit:hover { background: #eff6ff; }
    .colour-layout .v-action-delete { color: #ef4444; }
    .colour-layout .v-action-delete:hover { background: #fef2f2; }

    @media (max-width: 992px) {
        .colour-layout { flex-direction: column !important; height: auto !important; gap: 16px !important; }
        .colour-layout .form-section { flex: none !important; width: 100% !important; }
        .colour-layout .table-section { width: 100% !important; min-height: 300px !important; }
    }
    @media (max-width: 576px) {
        .colour-layout .v-card-body { padding: 16px !important; }
    }
</style>
<div class="colour-layout">

    <section class="v-card form-section">
        <div class="v-card-header">
            <span><span class="v-icon form-icon">🎨</span> <span id="formTitle">Add New Colour</span></span>
            <button type="button" id="btnClear" class="v-reset" onclick="resetForm()">⟳ Reset</button>
        </div>
        <div class="v-card-body">
            <form id="colourForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">

                <div class="v-fg">
                    <label>Colour Name <span class="required">*</span></label>
                    <input type="text" id="colour_name" name="colour_name" class="v-input" placeholder="e.g. Red, Blue, Green" required>
                </div>

                <div class="colour-picker-row">
                    <div class="v-fg">
                        <label>Pick Colour <span class="required">*</span></label>
                        <input type="text" id="colour_id" name="colour_id" class="v-input" placeholder="#ff0000" required>
                    </div>
                    <input type="color" id="colourPicker" value="#ff0000" title="Pick a colour">
                </div>

                <div style="margin-top:20px;">
                    <div class="v-toggle-wrap">
                        <label class="v-toggle">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <span class="v-slider"></span>
                        </label>
                        <span class="v-toggle-label">Colour is Active</span>
                    </div>
                </div>
            </form>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e8ecf1;background:#fafbfc;">
            <button type="button" id="btnSubmit" class="v-submit">Save Colour</button>
        </div>
    </section>

    <section class="v-card table-section">
        <div class="v-card-header">
            <span><span class="v-icon table-icon">🎨</span> Colours</span>
        </div>
        <div class="v-card-body">
            <div class="v-table-wrap">
                <table id="colourTable" class="v-table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Colour</th>
                            <th>Colour ID</th>
                            <th>Status</th>
                            <th data-sortable="false" style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colours as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="colour-cell">
                                    <div class="colour-dot" style="background:{{ $c->colour_id }};"></div>
                                    <span style="font-weight:600;">{{ $c->colour_name }}</span>
                                </div>
                            </td>
                            <td><span class="colour-hex">{{ $c->colour_id }}</span></td>
                            <td>
                                @if($c->is_active)
                                    <span class="v-badge active"><span class="dot"></span>Active</span>
                                @else
                                    <span class="v-badge inactive"><span class="dot"></span>Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
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

@push('scripts')
<script>
    // --- COLOUR PICKER SYNC ---
    document.getElementById('colourPicker').addEventListener('input', function() {
        document.getElementById('colour_id').value = this.value;
    });
    document.getElementById('colour_id').addEventListener('input', function() {
        const val = this.value.trim();
        if (/^#[0-9a-f]{6}$/i.test(val)) {
            document.getElementById('colourPicker').value = val;
        }
    });

    function resetForm() {
        document.getElementById('colourForm').reset();
        document.getElementById('encrypted_id').value = '';
        document.getElementById('formTitle').innerText = 'Add New Colour';
        document.getElementById('btnSubmit').innerText = 'Save Colour';
        document.getElementById('colourPicker').value = '#ff0000';
        window.clearFormErrors();
    }

    function toggleForm(show) {
        if (show) {
            resetForm();
        }
    }

    window.clearFormErrors = function() {
        document.querySelectorAll('.custom-error-text').forEach(el => el.remove());
        document.querySelectorAll('.v-input, input, select, textarea').forEach(el => el.style.borderColor = '');
    };

    // --- FORM SUBMIT ---
    document.getElementById('btnSubmit').addEventListener('click', async function() {
        window.clearFormErrors();

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        const encId = document.getElementById('encrypted_id').value;
        const method = encId ? 'PUT' : 'POST';
        const url = encId ? `/api/colours/${encId}` : `/api/colours`;

        const payload = {
            colour_name: document.getElementById('colour_name').value,
            colour_id: document.getElementById('colour_id').value,
            is_active: document.getElementById('is_active').checked ? 1 : 0,
        };

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
            btn.innerHTML = encId ? 'Update Colour' : 'Save Colour';
            toastr.error(error.message || 'Please fix the validation errors.');

            if (error.errors) {
                for (let fieldName in error.errors) {
                    let msg = error.errors[fieldName][0];
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.style.borderColor = '#ef4444';
                        field.insertAdjacentHTML('afterend',
                            `<div class="custom-error-text" style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">${msg}</div>`
                        );
                    }
                }
            }
        }
    });

    // --- EDIT RECORD ---
    window.editRecord = function(record) {
        window.clearFormErrors();
        document.getElementById('formTitle').innerText = 'Edit Colour';
        document.getElementById('btnSubmit').innerText = 'Update Colour';
        document.getElementById('encrypted_id').value = record.encrypted_id;
        document.getElementById('colour_name').value = record.colour_name;
        document.getElementById('colour_id').value = record.colour_id;
        document.getElementById('colourPicker').value = record.colour_id;
        document.getElementById('is_active').checked = record.is_active == 1;
    };

    // --- DELETE RECORD ---
    window.deleteRecord = async function(encId) {
        if (!confirm('Are you sure you want to delete this colour?')) return;
        try {
            const res = await fetch(`/api/colours/${encId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('erp_jwt_token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            if (!res.ok) throw await res.json();
            toastr.success((await res.json()).message);
            setTimeout(() => location.reload(), 1000);
        } catch (error) { toastr.error('Deletion failed.'); }
    };
</script>
@endpush
@endsection
