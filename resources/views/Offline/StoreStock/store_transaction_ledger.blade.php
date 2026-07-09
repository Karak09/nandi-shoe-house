@extends('Offline.layouts.app')
@section('title', 'Store All Out Transactions - Shoe ERP')
@section('content')

<header class="topbar" style="gap:16px; flex-wrap:wrap;">
    <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin:0;">Store All Out Transactions</h1>
    <form method="GET" action="{{ route('store_all_stock.transaction') }}" id="storeForm" class="store-selector" style="flex-wrap:wrap;">
        <span class="store-badge">Filter By Store</span>
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
        <span>All Store Out Transactions</span>
    </div>
    <div class="table-container">
        <table class="datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Reference Details</th>
                    <th>Store</th>
                    <th style="text-align: right;">Total Qty</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th data-sortable="false" style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $key => $group)
                @php
                    $first = $group->first();
                    $totalQty = $group->sum('quantity');
                    $totalAmount = $group->sum('total_price');
                    $itemCount = $group->count();
                    $jsonGroup = $group->map(function($item) {
                        return [
                            'name' => $item->product->name ?? 'N/A',
                            'ben_name' => $item->product->ben_name ?? '',
                            'colour' => $item->product->colourRelation->colour_name ?? '',
                            'quantity' => $item->quantity,
                            'uom' => $item->uomRelation->keyword ?? '',
                            'mrp' => $item->mrp,
                            'unit_price' => $item->unit_price,
                            'gst' => $item->gst,
                            'total_price' => $item->total_price,
                        ];
                    })->toJson();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ \Carbon\Carbon::parse($first->created_at)->format('d M Y') }}</div>
                        <div class="td-sub">{{ \Carbon\Carbon::parse($first->created_at)->format('H:i A') }}</div>
                    </td>
                    <td>
                        @if($first->transaction_type == 2)
                            <span style="background:#fef2f2; color:#dc2626; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600;">Sale OUT</span>
                        @elseif($first->transaction_type == 3)
                            <span style="background:#fff7ed; color:#ea580c; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600;">Combo OUT</span>
                        @elseif($first->transaction_type == 4)
                            <span style="background:#eff6ff; color:#2563eb; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600;">Requisition OUT</span>
                        @endif
                    </td>
                    <td>
                        @if($first->transaction_type == 3 && $first->combo)
                            <div style="font-weight: 600; color:#ea580c;">{{ $first->combo->combo_code ?? '-' }}</div>
                            <div class="td-sub">Combo: {{ $first->combo->product->name ?? '-' }}</div>
                        @elseif($first->transaction_type == 4 && $first->requisition)
                            <div style="font-weight: 600; color:#2563eb;">{{ $first->requisition->req_id ?? '-' }}</div>
                            <div class="td-sub">To: {{ $first->requisition->where_req ?? '-' }}</div>
                        @else
                            <div style="font-weight: 600; color:#64748b;">POS Sale</div>
                            <div class="td-sub">{{ $itemCount }} item(s)</div>
                        @endif
                    </td>
                    <td>{{ $first->store->store_name ?? '-' }}</td>
                    <td style="font-weight: 600; text-align: right;">
                        <span style="color:#ea580c;">-{{ number_format($totalQty, 2) }}</span>
                    </td>
                    <td style="text-align: right;" class="num-col">₹ {{ number_format($totalAmount, 2) }}</td>
                    <td style="text-align:right;">
                        <button class="btn btn-outline" style="padding:4px 8px; font-size:11px; cursor:pointer;"
                            data-type="{{ $first->transaction_type }}"
                            data-ref="{{ $first->transaction_type == 3 ? ($first->combo->combo_code ?? '-') : ($first->transaction_type == 4 ? ($first->requisition->req_id ?? '-') : 'POS Sale') }}"
                            data-store="{{ $first->store->store_name ?? '-' }}"
                            data-date="{{ \Carbon\Carbon::parse($first->created_at)->format('d M Y H:i A') }}"
                            data-items='{{ $jsonGroup }}'
                            onclick="viewTransaction(this)">
                            View
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 16px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:20px; border-radius:8px; max-width:800px; width:100%; max-height: 90vh; display: flex; flex-direction: column;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center;">
            <span>Transaction Details</span>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; overflow-x: auto; flex-grow: 1;"></div>
    </div>
</div>

@push('scripts')
<script>
    window.viewTransaction = function(btn) {
        var txType = parseInt(btn.getAttribute('data-type'));
        var refValue = btn.getAttribute('data-ref');
        var storeName = btn.getAttribute('data-store');
        var dateStr = btn.getAttribute('data-date');
        var group;
        try { group = JSON.parse(btn.getAttribute('data-items')); } catch(e) { group = []; }

        var typeName = '', typeColor = '', refLabel = '';
        if (txType == 2) { typeName = 'Sale OUT'; typeColor = '#dc2626'; refLabel = 'Barcode'; }
        else if (txType == 3) { typeName = 'Combo OUT'; typeColor = '#ea580c'; refLabel = 'Combo Code'; }
        else if (txType == 4) { typeName = 'Requisition OUT'; typeColor = '#2563eb'; refLabel = 'Requisition ID'; }

        var totalValue = 0;
        var rows = '';
        group.forEach(function(t, i) {
            totalValue += parseFloat(t.total_price);
            var colourHtml = t.colour ? '<span style="background:#ede9fe; color:#6d28d9; padding:2px 8px; border-radius:4px; font-weight:600;">' + t.colour + '</span>' : '<span style="color:#a1a1aa;">-</span>';
            rows += '<tr>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#64748b;">' + (i+1) + '</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">' + t.name + '</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-size:12px;">' + colourHtml + '</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">' + t.quantity + ' <span style="font-size:11px; color:#64748b; font-weight:400;">' + t.uom + '</span></td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹' + t.mrp + '</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹' + t.unit_price + '</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#ef4444;">' + t.gst + '%</td>'
                + '<td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:700;">₹' + t.total_price + '</td>'
                + '</tr>';
        });

        var html = '<div style="background:#f8fafc; padding:16px; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:16px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;">'
            + '<div style="flex:1; min-width:200px;">'
            + '<div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">' + refLabel + '</div>'
            + '<div style="font-size:16px; font-weight:600; color:' + typeColor + ';">' + refValue + '</div>'
            + '</div>'
            + '<div style="flex:1; min-width:200px;">'
            + '<div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Store</div>'
            + '<div style="font-weight:600; font-size:14px;">' + storeName + '</div>'
            + '</div>'
            + '<div style="text-align:right; flex:1; min-width:200px;">'
            + '<div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Transaction Type</div>'
            + '<div style="font-weight:600; font-size:14px; color:' + typeColor + ';">' + typeName + '</div>'
            + '<div style="font-size:12px; color:#64748b; margin-top:4px;">' + dateStr + '</div>'
            + '</div></div>'
            + '<div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px;">Included Items (' + group.length + ')</div>'
            + '<div style="overflow-x:auto; -webkit-overflow-scrolling:touch; margin-bottom:20px;">'
            + '<table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left; white-space:nowrap;">'
            + '<thead style="background:#f1f5f9;"><tr>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">#</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Product</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Colour</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Qty & UOM</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">MRP (₹)</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Unit price (₹)</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">GST %</th>'
            + '<th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>'
            + '</tr></thead>'
            + '<tbody>' + rows + '</tbody></table></div>'
            + '<div style="background:#f1f5f9; padding:16px; border-radius:6px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">'
            + '<div style="font-size:13px; font-weight:600; color:#475569;">Total Value</div>'
            + '<div style="font-size:20px; font-weight:700; color:#0f172a;">₹' + totalValue.toFixed(2) + '</div></div>';

        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection
