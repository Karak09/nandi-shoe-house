@extends('Offline.layouts.app')
@section('title', 'Premium Unit Conversion - Shoe ERP')
@section('content')

<div class="workspace-c">
    <section class="form-section-c card-c">
        <div class="card-header-c">
            <span id="formTitle">Unit Conversion Rule</span>
            <button type="button" id="btnClear" class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Reset</button>
        </div>
        <div class="card-body-c">
            <form id="convertForm" novalidate>
                <input type="hidden" id="encrypted_id" name="encrypted_id">
                
                <div class="form-row-c">
                    <div class="form-group-c full">
                        <label class="form-label-c">Conversion Name (name) <span style="color:red">*</span></label>
                        <input type="text" id="name" name="name" class="form-control-c" placeholder="e.g. Box to Pairs" required>
                    </div>
                </div>

                <div class="conversion-visual-c">
                    <span>1 <span id="vis-from">[From]</span></span>
                    <span class="arrow-icon-c">➔</span>
                    <span><span id="vis-factor">[Factor]</span> <span id="vis-to">[To]</span></span>
                </div>

                <div class="form-row-c">
                    <div class="form-group-c">
                        <label class="form-label-c">From Unit (from_unit)</label>
                        <select id="from_unit" name="from_unit" class="form-control-c" required>
                            <option value="">Select...</option>
                            @foreach($units as $u) <option value="{{ $u->id }}" data-key="{{ $u->keyword }}">{{ $u->name }} ({{ $u->keyword }})</option> @endforeach
                        </select>
                    </div>
                    <div class="form-group-c">
                        <label class="form-label-c">To Unit (to_unit)</label>
                        <select id="to_unit" name="to_unit" class="form-control-c" required>
                            <option value="">Select...</option>
                            @foreach($units as $u) <option value="{{ $u->id }}" data-key="{{ $u->keyword }}">{{ $u->name }} ({{ $u->keyword }})</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-c">
                    <div class="form-group-c">
                        <label class="form-label-c">Unit Factor (unit_factor)</label>
                        <input type="number" step="0.001" id="unit_factor" name="unit_factor" class="form-control-c" placeholder="e.g. 10.000" required>
                    </div>
                    <div class="form-group-c">
                        <label class="form-label-c">Price Factor (price_factor)</label>
                        <input type="number" step="0.001" id="price_factor" name="price_factor" class="form-control-c" placeholder="e.g. 1.000" required>
                    </div>
                </div>

                <div class="toggle-group-c">
                    <span class="toggle-text-c">Is Packet Entry? (packet)</span>
                    <label class="switch-c"><input type="checkbox" id="packet" name="packet"><span class="slider-c"></span></label>
                </div>
                <div class="toggle-group-c">
                    <span class="toggle-text-c">Active Status (is_active)</span>
                    <label class="switch-c"><input type="checkbox" id="is_active" name="is_active" checked><span class="slider-c"></span></label>
                </div>

                <div class="form-actions-c">
                    <button type="button" id="btnClear" class="btn-c btn-secondary-c">Clear</button>
                    <button type="submit" id="btnSubmit" class="btn-c btn-primary-c">Save Rule</button>
                </div>
            </form>
        </div>
    </section>

    <section class="table-section-c card-c">
        <div class="card-header-c">
            <span>Conversion Rules Directory</span>
        </div>
        <div class="card-body-c" style="padding: 10px;">
            <table class="datatable" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--surface-c);">
                    <tr>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">ID</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">Rule Name</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">Conversion Formula</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">Unit / Price Factor</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">Packet</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c);">Status</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: var(--text-muted-c); text-transform: uppercase; border-bottom: 1px solid var(--border-c); text-align: right;" data-sortable="false">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conversions as $c)
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c); font-weight: 500; font-size: 13px; color: var(--text-main-c);">{{ $loop->iteration }}</td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c); font-weight: 500; font-size: 13px; color: var(--text-main-c);">{{ $c->name }}</td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c);">
                            <span class="rule-badge-c">1 {{ $c->fromUnit->keyword ?? '?' }} ➔ {{ number_format($c->unit_factor, 2) }} {{ $c->toUnit->keyword ?? '?' }}</span>
                        </td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c);">
                            <div class="factor-num-c">U: {{ number_format($c->unit_factor, 3) }}</div>
                            <div style="font-size: 11px; color: var(--text-muted-c);">P: {{ number_format($c->price_factor, 3) }}</div>
                        </td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c); font-size: 13px; color: var(--text-main-c);">{{ $c->packet ? 'Yes' : 'No' }}</td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c); font-size: 13px; color: var(--text-main-c);">
                            @if($c->is_active)
                                <span class="status-dot-c dot-active-c"></span> Active
                            @else
                                <span class="status-dot-c dot-inactive-c"></span> Inactive
                            @endif
                        </td>
                        <td style="padding: 14px 16px; border-bottom: 1px solid var(--border-c); text-align: right;">
                            <button class="action-link-c" onclick='editRecord(@json($c))'>Edit</button>
                            <button class="action-link-c" style="color:var(--danger-c);" onclick="deleteRecord('{{ $c->encrypted_id }}')">Delete</button>
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
        document.querySelectorAll('.form-control-c').forEach(el => el.style.borderColor = '');
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
        document.getElementById('from_unit').value = record.from_unit;
        document.getElementById('to_unit').value = record.to_unit;
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
            
            // Handles 403 Product In Use error
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