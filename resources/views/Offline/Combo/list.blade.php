@extends('Offline.layouts.app')
@section('title', 'Combo Creation History')
@section('content')

<style>
    .ben-name-display { color: #0f172a; font-size: 11px; display: block; font-weight: 500; margin-top: 2px; opacity:0.6; }
    .value-monospace { font-family: monospace; font-size: 11px; color: #1e293b; word-break: break-all; }

    .btn-print { background: #0ea5e9; color: white; padding: 5px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 11px; font-weight: 700; transition: 0.2s; white-space:nowrap; }
    .btn-print:hover { background: #0284c7; }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); z-index: 9999; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(5px); }
    .modal-content { background: white; width: 100%; max-width: 1200px; max-height: 95vh; border-radius: 12px; position: relative; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }

    .modal-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
    .modal-body { flex: 1; overflow-y: auto; padding: 20px 24px; }

    .modal-flex-grid { display: flex; gap: 24px; }
    .modal-column { flex: 1; min-width: 0; }

    .section-title { font-size: 12px; font-weight: 800; color: #0f172a; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; display: flex; align-items: center; gap: 8px; }

    .detail-table { width: 100%; font-size: 12px; border-collapse: collapse; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; }
    .detail-table th { background: #f8fafc; padding: 10px 8px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
    .detail-table td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }

    .datatable td:last-child { white-space: nowrap; }
    .datatable td:last-child button { display: inline-flex; align-items: center; justify-content: center; margin: 2px; vertical-align: middle; }

    @media (max-width: 1024px) {
        .modal-flex-grid { flex-direction: column; gap: 24px; }
        .modal-content { height: 100vh; border-radius: 0; width: 100%; }
        .detail-table th, .detail-table td { padding: 8px 5px; font-size: 11px; }
    }
</style>

<div class="filter-bar">
    <div class="filter-group">
        <label>Store</label>
        <select name="store_filter" class="filter-input">
            <option value="">All Stores</option>
            @foreach($stores as $s)
                <option value="{{ $s->id }}" {{ request('store_filter') == $s->id ? 'selected' : '' }}>
                    {{ $s->store_name ?? $s->name }}
                </option>
            @endforeach
        </select>
    </div>

    <form method="GET" action="{{ route('combo.list') }}" class="global-date-filter">
        <div class="filter-group">
            <label>From Date</label>
            <input type="date" name="from_date" class="filter-input" value="{{ $fromDate }}">
        </div>
        <div class="filter-group">
            <label>To Date</label>
            <input type="date" name="to_date" class="filter-input" value="{{ $toDate }}">
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ route('combo.list') }}" class="btn-reset">Reset</a>
        </div>
    </form>

    <a href="{{ route('combo.index') }}" class="btn-filter" style="text-decoration:none; height:36px; display:inline-flex; align-items:center; padding:0 20px;">+ New Combo</a>
</div>

<div class="card">
    <div class="card-header">
        <span>Combo Creation History</span>
    </div>
    <div class="table-container">
        <table class="datatable">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Reference</th>
                    <th>Target Product</th>
                    <th>Store</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th data-sortable="false" style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($combos as $c)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="font-family: monospace; font-weight: 700;">{{ $c->combo_code }}</td>
                    <td>
                        <strong>{{ $c->product->name ?? 'N/A' }}</strong>
                        <span class="ben-name-display">{{ $c->product->ben_name ?? '' }}</span>
                        <div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:2px;">
                            @if($c->product->colourRelation->colour_name ?? null)
                                <span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">{{ $c->product->colourRelation->colour_name }}</span>
                            @endif
                            @if($c->product->pro_size ?? null)
                                <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">{{ $c->product->pro_size }}</span>
                            @endif
                        </div>
                    </td>
                    <td>{{ $c->store->store_name ?? $c->store->name ?? '-' }}</td>
                    <td>{{ $c->user->details->f_name ?? '' }} {{ $c->user->details->l_name ?? 'Admin' }}</td>
                    <td style="color: #64748b; font-size: 13px;">{{ $c->created_at->format('d/m/Y h:i A') }}</td>
                    <td style="text-align:right;">
                        <button class="btn-outline" style="padding:6px 10px; font-size:11px; cursor:pointer;" onclick="openDetails('{{ $c->encrypted_id }}', '{{ $c->combo_code }}')">👁️ View</button>
                        <button class="btn-print" onclick="printLiveBarcodes('{{ $c->encrypted_id }}')">🖨️ Print</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="detailsModal" class="modal-overlay" onclick="closeModalOutside(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; margin: 0;">Transaction Breakdown</h2>
                <p id="modal_ref" style="color:#0ea5e9; font-family:monospace; font-weight:bold; margin: 5px 0 0 0; letter-spacing: 1px;"></p>
            </div>
            <span style="font-size: 30px; color: #94a3b8; cursor: pointer; line-height: 1;" onclick="closeModal()">&times;</span>
        </div>

        <div class="modal-body">
            <div class="modal-flex-grid">
                <div class="modal-column">
                    <div class="section-title">📦 Ingredients (Outward)</div>
                    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Product Name</th>
                                    <th>Size</th>
                                    <th>Colour</th>
                                    <th>Qty & UOM</th>
                                    <th>Unit Price (₹)</th>
                                    <th>GST %</th>
                                    <th>Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="raw_body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-column">
                    <div class="section-title">✨ Final Bundle (Inward)</div>
                    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Product Name</th>
                                    <th>Size</th>
                                    <th>Colour</th>
                                    <th>Qty & UOM</th>
                                    <th>Rate (₹)</th>
                                    <th>MRP (₹)</th>
                                    <th>GST %</th>
                                    <th>Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="finished_body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="row-raw">
    <tr>
        <td class="r-sl" style="color: #94a3b8;"></td>
        <td class="r-name"></td>
        <td class="r-size"></td>
        <td class="r-colour"></td>
        <td class="r-qty" style="color:#dc2626; font-weight:bold; white-space: nowrap;"></td>
        <td class="r-unit-price"></td>
        <td class="r-gst"></td>
        <td class="r-total" style="font-weight:600;"></td>
    </tr>
</template>

<template id="row-finished">
    <tr>
        <td class="f-sl" style="color: #94a3b8;"></td>
        <td class="f-name"></td>
        <td class="f-size"></td>
        <td class="f-colour"></td>
        <td class="f-qty" style="color:#16a34a; font-weight:bold; white-space: nowrap;"></td>
        <td class="f-rate"></td>
        <td class="f-mrp"></td>
        <td class="f-gst"></td>
        <td class="f-total" style="font-weight:600;"></td>
    </tr>
</template>

@push('scripts')
<script>
    async function printLiveBarcodes(encId) {
        if(!confirm("Print barcodes for available stock?")) return;

        try {
            const res = await fetch("{{ url('combo/get-print-data') }}/" + encId);
            const result = await res.json();

            if (result.status === 'success') {
                localStorage.setItem('print_barcodes', JSON.stringify(result.print_payload));
                window.open(result.redirect_url, '_blank');
                
                toastr.success("Printing " + result.print_payload[0].quantity + " barcodes...");
            } else {
                toastr.error(result.message || "Failed to fetch print data.");
            }
        } catch (e) {
            toastr.error("System error occurred.");
        }
    }
    
    async function openDetails(encId, ref) {
        document.getElementById('modal_ref').innerText = "REF: " + ref;
        try {
            const res = await fetch("{{ url('combo/details') }}/" + encId);
            const data = await res.json();

            if (data.status === 'success') {
                const rawBody = document.getElementById('raw_body');
                const finBody = document.getElementById('finished_body');
                rawBody.innerHTML = ''; finBody.innerHTML = '';

                data.raw.forEach((item, index) => {
                    const clone = document.getElementById('row-raw').content.cloneNode(true);
                    clone.querySelector('.r-sl').innerText = index + 1;
                    clone.querySelector('.r-name').innerHTML = `<b>${item.product?.name}</b><br><span class="ben-name-display">(${item.product?.ben_name || ''})</span>`;
                    let size = item.product?.pro_size || '-';
                    let colour = item.product?.colour_relation?.colour_name || '-';
                    clone.querySelector('.r-size').innerHTML = size !== '-' ? `<span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${size}</span>` : '<span style="color:#a1a1aa;">-</span>';
                    clone.querySelector('.r-colour').innerHTML = colour !== '-' ? `<span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${colour}</span>` : '<span style="color:#a1a1aa;">-</span>';
                    let uomName = item.uom_relation?.name || item.uom_relation?.keyword || '';
                    clone.querySelector('.r-qty').innerText = item.quantity + " " + uomName;
                    clone.querySelector('.r-unit-price').innerText = '₹' + (item.unit_price || 0);
                    clone.querySelector('.r-gst').innerText = (item.gst || 0) + '%';
                    let total = (item.quantity || 0) * (item.unit_price || 0);
                    clone.querySelector('.r-total').innerText = '₹' + total.toFixed(2);
                    rawBody.appendChild(clone);
                });

                data.finished.forEach((item, index) => {
                    const clone = document.getElementById('row-finished').content.cloneNode(true);
                    clone.querySelector('.f-sl').innerText = index + 1;
                    clone.querySelector('.f-name').innerHTML = `<b>${item.product?.name}</b><br><span class="ben-name-display">(${item.product?.ben_name || ''})</span>`;
                    let size = item.product?.pro_size || '-';
                    let colour = item.product?.colour_relation?.colour_name || '-';
                    clone.querySelector('.f-size').innerHTML = size !== '-' ? `<span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${size}</span>` : '<span style="color:#a1a1aa;">-</span>';
                    clone.querySelector('.f-colour').innerHTML = colour !== '-' ? `<span style="background:#ede9fe; color:#6d28d9; padding:1px 6px; border-radius:3px; font-size:10px; font-weight:600;">${colour}</span>` : '<span style="color:#a1a1aa;">-</span>';
                    let uomName = item.uom_relation?.name || item.uom_relation?.keyword || '';
                    clone.querySelector('.f-qty').innerText = item.quantity + " " + uomName;
                    clone.querySelector('.f-rate').innerText = '₹' + (item.unit_price || 0);
                    clone.querySelector('.f-mrp').innerText = '₹' + (item.mrp || 0);
                    clone.querySelector('.f-gst').innerText = (item.gst || 0) + '%';
                    clone.querySelector('.f-total').innerText = '₹' + (item.total_price || 0);
                    finBody.appendChild(clone);
                });

                document.getElementById('detailsModal').style.display = 'flex';
            }
        } catch (e) { toastr.error("Could not fetch details."); }
    }

    function closeModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    function closeModalOutside(event) {
        if (event.target.id === 'detailsModal') {
            closeModal();
        }
    }

    // Auto-submit store filter on change
    document.querySelector('[name="store_filter"]')?.addEventListener('change', function() {
        const from = document.querySelector('[name="from_date"]')?.value || '{{ $fromDate }}';
        const to = document.querySelector('[name="to_date"]')?.value || '{{ $toDate }}';
        const storeVal = this.value;
        let url = "{{ route('combo.list') }}?";
        if (storeVal) url += "store_filter=" + encodeURIComponent(storeVal) + "&";
        url += "from_date=" + encodeURIComponent(from) + "&to_date=" + encodeURIComponent(to);
        window.location.href = url;
    });
</script>
@endpush
@endsection
