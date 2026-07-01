@extends('Offline.layouts.app')
@section('title', 'Godown Transfer Ledger (OUT) - Shoe ERP')
@section('content')

<header class="topbar" style="gap:16px; flex-wrap:wrap;">
    <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin:0;">Godown Transfer Ledger (OUT)</h1>
    <form method="GET" action="{{ route('store_all_stock.transaction') }}" id="storeForm" class="store-selector" style="flex-wrap:wrap;">
        <span class="store-badge">Filter By Target Store</span>
        <select name="store_id" onchange="document.getElementById('storeForm').submit()">
            <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                    {{ $store->store_name }}
                </option>
            @endforeach
        </select>
    </form>
</header>

<form method="GET" action="{{ route('store_all_stock.transaction') }}" class="global-date-filter">
    <input type="hidden" name="store_id" value="{{ request('store_id') }}">
    <div class="filter-group">
        <label>Start Date</label>
        <input type="date" name="start_date" class="filter-input" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
    </div>
    <div class="filter-group">
        <label>End Date</label>
        <input type="date" name="end_date" class="filter-input" value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
    </div>
    <div style="display:flex; gap:8px;">
        <button type="submit" class="btn-filter">Filter Ledger</button>
        <a href="{{ route('store_all_stock.transaction') }}" class="btn-reset">Reset</a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <span>Godown Transfer Ledger (OUTWARD)</span>
    </div>
    <div class="table-container">
        <table class="datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Transfer Date</th>
                    <th>Transfer Ref (Challan No)</th>
                    <th>Total Items Transferred</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th data-sortable="false" style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($challans as $c)
                <tr>
                    <th>{{ $loop->iteration }}</th>
                    <td>{{ \Carbon\Carbon::parse($c->challan_date)->format('d-M-Y') }}</td>
                    <td style="font-weight:600; color:#0ea5e9;">{{ $c->challan_no }}</td>
                    <td>{{ $c->transactions->count() }} Products</td>
                    <td class="num-col">₹ {{ number_format($c->total, 2) }}</td>
                    <td><span class="badge">Dispatched</span></td>
                    <td style="text-align:right;">
                        <button class="btn btn-outline" style="padding:6px 12px; font-size:11px; cursor:pointer;" onclick='viewChallan(@json($c))'>👁️ View</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:20px; border-radius:8px; max-width:800px; width:100%; max-height: 90vh; display: flex; flex-direction: column;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center;">
            <span>Transfer Details (OUT)</span>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; overflow-x: auto; flex-grow: 1;"></div>
    </div>
</div>

@push('scripts')
<script>
    window.viewChallan = function(c) {
        let rows = '';
        if(c.transactions) {
            c.transactions.forEach((t, i) => {
                let uom = t.uom_relation ? t.uom_relation.keyword : '';
                rows += `<tr>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0;">${i+1}</td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.product ? t.product.name : 'N/A'}</td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0; color:#ea580c; font-weight:bold;">${Math.abs(t.quantity)} <span style="font-size:11px;">${uom}</span></td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0;">₹${t.unit_price}</td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0; font-weight:600;">₹${t.total_price}</td>
                </tr>`;
            });
        }

        const html = `
            <div style="display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:6px; border:1px solid #e2e8f0;">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase;">Transfer Ref</div>
                    <div style="font-weight:600; font-size:15px;">${c.challan_no}</div>
                </div>
            </div>
            
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; margin-bottom:20px;">
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left; white-space:nowrap;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">#</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Product</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Qty & UOM</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Rate (₹)</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            </div>
            
            <div style="text-align:right; margin-top:20px; font-size:20px; font-weight:700; color:#0f172a; padding-top:16px; border-top:2px dashed #cbd5e1;">
                Grand Total: ₹${c.total}
            </div>
        `;
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection