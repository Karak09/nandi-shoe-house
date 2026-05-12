@extends('Offline.layouts.app')
@section('title', 'Combo Creation History')
@section('content')

<style>
    .main-content {
        width: 100%;
        overflow-x: auto;
    }
    .list-container { width: 100%; overflow-x: auto;padding: 25px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
    .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    
    /* FILTER BAR */
    .filter-bar { background: #f8fafc; padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-end; border: 1px solid #e2e8f0; flex-wrap: wrap; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-label { font-size: 11px; font-weight: 800; color: #7c3aed; text-transform: uppercase; }
    .filter-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; outline: none; }
    .btn-filter { background: #8b5cf6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 11px; margin-left: 5px; transition: 0.2s; }
    .btn-reset { background: #ef4444; color: white; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 13px; }
    .btn-filter,
    .btn-print {
        white-space: nowrap;
    }
    /* TEXT STYLING */
    .ben-name-display { color: #8b5cf6; font-size: 11px; display: block; font-weight: 500; margin-top: 2px; }
    .stack-info { display: flex; flex-direction: column; gap: 2px; }
    .label-small { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-right: 4px; }
    .value-monospace { font-family: monospace; font-size: 11px; color: #1e293b; word-break: break-all; }

    /* New Print Button Style */
    .btn-print { background: #0ea5e9; color: white; padding: 5px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 11px; font-weight: 700; margin-left: 5px; transition: 0.2s; }
    .btn-print:hover { background: #0284c7; }
    
    /* MODAL DESIGN */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); z-index: 9999; justify-content: center; align-items: center; padding: 15px; backdrop-filter: blur(5px); }
    .modal-content { background: white; width: 100%; max-width: 1200px; max-height: 95vh; border-radius: 16px; position: relative; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    
    .modal-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .modal-body { flex: 1; overflow-y: auto; padding: 20px; }
    
    .modal-flex-grid { display: flex; gap: 20px; }
    .modal-column { flex: 1; min-width: 0; }
    
    .section-title { font-size: 12px; font-weight: 800; color: #8b5cf6; text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    
    .detail-table { width: 100%; font-size: 12px; border-collapse: collapse; background: #fff; border: 1px solid #eee; border-radius: 8px; }
    .detail-table th { background: #f8fafc; padding: 12px 10px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 1px solid #eee; }
    .detail-table td { padding: 12px 10px; border-bottom: 1px solid #f8fafc; vertical-align: top; }

    .table-wrapper { overflow-x: auto; width: 100%;-webkit-overflow-scrolling: touch; }
    .datatable {
        width: 100% !important;
        min-width: 950px;
        border-collapse: collapse;
        table-layout: auto;
    }

    /* Action buttons fix */
    .datatable td:last-child {
        white-space: nowrap;
    }

    .datatable td:last-child button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px;
        vertical-align: middle;
    }
    @media (max-width: 1024px) {
        .modal-flex-grid { flex-direction: column; gap: 30px; }
        .modal-content { height: 100vh; border-radius: 0; width: 100%; }
        .detail-table th, .detail-table td { padding: 8px 5px; font-size: 11px; }
    }
    
</style>

<div class="main-content">
    <div class="list-container">
        <div class="header-section">
            <h1 style="font-size: 22px; font-weight: 800; color: #1e293b;">Combo Creation History</h1>
            <a href="{{ route('combo.index') }}" class="btn-filter" style="text-decoration:none;">+ New Combo</a>
        </div>

        <form method="GET" action="{{ route('combo.list') }}" class="filter-bar">
            @if(count($stores) > 0)
            <div class="filter-group">
                <label class="filter-label">Store</label>
                <select name="store_filter" class="filter-input">
                    <option value="">All Stores</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->enc_id }}" {{ request('store_filter') == $s->enc_id ? 'selected' : '' }}>
                            {{ $s->store_name ?? $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="filter-group">
                <label class="filter-label">From Date</label>
                <input type="date" name="from_date" class="filter-input" value="{{ $fromDate }}">
            </div>

            <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input type="date" name="to_date" class="filter-input" value="{{ $toDate }}">
            </div>

            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ route('combo.list') }}" class="btn-reset">Reset</a>
        </form>

        <div class="table-wrapper">
            <table class="datatable">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Reference</th>
                        <th>Target Product</th>
                        <th>Store</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th style="text-align: right;">Action</th>
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
                        </td>
                        <td>{{ $c->store->store_name ?? $c->store->name ?? '-' }}</td>
                        <td>{{ $c->user->details->f_name ?? '' }} {{ $c->user->details->l_name ?? 'Admin' }}</td>
                        <td style="color: #64748b; font-size: 13px;">{{ $c->created_at->format('d/m/Y h:i A') }}</td>
                        <td style="text-align: right;">
                            <button class="btn-filter" style="padding: 5px 12px; font-size: 11px;" onclick="openDetails('{{ $c->encrypted_id }}', '{{ $c->combo_code }}')">View</button>
                            <button class="btn-print" onclick="printLiveBarcodes('{{ $c->encrypted_id }}')">Print</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="detailsModal" class="modal-overlay" onclick="closeModalOutside(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h2 style="font-size: 18px; font-weight: 800; margin: 0;">Transaction Breakdown</h2>
                <p id="modal_ref" style="color:#8b5cf6; font-family:monospace; font-weight:bold; margin: 5px 0 0 0; letter-spacing: 1px;"></p>
            </div>
            <span style="font-size: 35px; color: #94a3b8; cursor: pointer; line-height: 1;" onclick="closeModal()">&times;</span>
        </div>

        <div class="modal-body">
            <div class="modal-flex-grid">
                <div class="modal-column">
                    <div class="section-title">📦 Ingredients (Outward)</div>
                    <div class="table-wrapper">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Batch</th>
                                    <th>Barcode</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="raw_body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-column">
                    <div class="section-title">✨ Final Bundle (Inward)</div>
                    <div class="table-wrapper">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Barcode</th>
                                    <th>Qty</th>
                                    <th>Pricing</th>
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
        <td class="r-batch value-monospace"></td>
        <td class="r-barcode value-monospace"></td>
        <td class="r-qty" style="color:#ef4444; font-weight:bold; white-space: nowrap;"></td>
    </tr>
</template>

<template id="row-finished">
    <tr>
        <td class="f-sl" style="color: #94a3b8;"></td>
        <td class="f-name"></td>
        <td class="f-barcode value-monospace"></td>
        <td class="f-qty" style="color:#10b981; font-weight:bold; white-space: nowrap;"></td>
        <td class="f-price" style="line-height: 1.5;"></td>
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
                // 1. Update LocalStorage with the payload (which contains the LIVE quantity)
                localStorage.setItem('print_barcodes', JSON.stringify(result.print_payload));

                // 2. Open print page in a NEW tab
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
                    
                    let batches = item.batch_no;
                    clone.querySelector('.r-batch').innerHTML = Array.isArray(batches) 
                        ? batches.join('<br>') : (batches || '-');

                    let barcodes = item.barcode_no;
                    clone.querySelector('.r-barcode').innerHTML = Array.isArray(barcodes) 
                        ? barcodes.join('<br>') : (barcodes || '-');

                    clone.querySelector('.r-qty').innerText = item.quantity + " " + (item.uom_relation?.name || '');
                    rawBody.appendChild(clone);
                });

                data.finished.forEach((item, index) => {
                    const clone = document.getElementById('row-finished').content.cloneNode(true);
                    clone.querySelector('.f-sl').innerText = index + 1;
                    clone.querySelector('.f-name').innerHTML = `<b>${item.product?.name}</b><br><span class="ben-name-display">(${item.product?.ben_name || ''})</span>`;
                    
                    let bcode = item.barcode_no;
                    clone.querySelector('.f-barcode').innerText = Array.isArray(bcode) ? bcode[0] : (bcode || '-');
                    
                    clone.querySelector('.f-qty').innerText = item.quantity + " " + (item.uom_relation?.name || '');
                    clone.querySelector('.f-price').innerHTML = `<small>MRP:</small> ₹${item.mrp}<br><small>Rate:</small> ₹${item.unit_price}`;
                    finBody.appendChild(clone);
                });

                document.getElementById('detailsModal').style.display = 'flex';
            }
        } catch (e) { toastr.error("Could not fetch details."); }
    }

    function closeModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    // Modal close logic when clicking outside
    function closeModalOutside(event) {
        if (event.target.id === 'detailsModal') {
            closeModal();
        }
    }
</script>
@endpush
@endsection