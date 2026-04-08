@extends('Offline.layouts.app')
@section('title', 'Transaction Ledger - Shoe ERP')
@section('content')

<div class="main-content">
    <div class="workspace">
        
        <form method="GET" action="{{ route('purchased.ledger') }}" class="global-date-filter">
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
                <a href="{{ route('purchased.ledger') }}" class="btn-reset">Reset</a>
            </div>
        </form>

        <div class="ledger-card">
            <div class="table-scroll">
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Reference Details</th>
                            <th>Product & Batch</th>
                            <th style="text-align: right;">Quantity & Unit</th>
                            <th style="text-align: right;">Total Amount</th>
                            <th data-sortable="false" style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $challanId => $group)
                        @php 
                            $first = $group->first(); 
                            $totalQty = $group->sum('quantity');
                            $totalAmount = $group->sum('total_price');
                            $totalItems = $group->count();
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 500;">{{ \Carbon\Carbon::parse($first->created_at)->format('d M Y') }}</div>
                                <div class="td-sub">{{ \Carbon\Carbon::parse($first->created_at)->format('H:i A') }}</div>
                            </td>
                            <td>
                                @if($first->transaction_type == 1)
                                    <span class="tx-badge tx-in">↓ INWARD</span>
                                @else
                                    <span class="tx-badge tx-out">↑ OUTWARD</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color:#0ea5e9;">Challan: {{ $first->purchaseDetails->challan_no ?? '-' }}</div>
                                <div class="td-sub">Vendor: {{ $first->purchaseDetails->vendor->vendor_name ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $totalItems }} Unique Item(s)</div>
                                <div class="td-sub">Batch: {{ $first->batch_no ? implode(', ', $first->batch_no) : '-' }}</div>
                            </td>
                            <td style="text-align: right;">
                                <div class="num-col" style="color: {{ $first->transaction_type == 1 ? '#059669' : '#ea580c' }};">
                                    {{ $first->transaction_type == 1 ? '+' : '-' }} {{ number_format($totalQty, 2) }} <span style="font-size:11px; color:#64748b;">TOTAL</span>
                                </div>
                            </td>
                            <td style="text-align: right;" class="num-col">₹ {{ number_format($totalAmount, 2) }}</td>
                            <td style="text-align: right;">
                                <button class="btn btn-outline" style="padding:4px 8px; font-size:11px; cursor:pointer;" onclick='viewTransaction(@json($group))'>👁️ View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px; border-radius:8px; width:750px; max-height: 90vh; display: flex; flex-direction: column;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between;">
            <span>Transaction Ledger Details</span>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; flex-grow: 1;"></div>
    </div>
</div>

@push('scripts')
<script>
    window.viewTransaction = function(group) {
        let first = group[0];
        let batch = first.batch_no ? first.batch_no.join(', ') : '-';
        let date = new Date(first.created_at).toLocaleString('en-GB');
        let totalChallanValue = 0;
        
        let rows = '';
        group.forEach((t, i) => {
            let uom = t.uom_relation ? t.uom_relation.keyword : '';
            let benName = (t.product && t.product.ben_name) ? ` <span style="color:#64748b; font-weight:400; font-size:12px;">(${t.product.ben_name})</span>` : '';
            totalChallanValue += parseFloat(t.total_price);
            
            rows += `<tr>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#64748b;">${i+1}</td>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.product ? t.product.name : 'N/A'}${benName}</td>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.quantity} <span style="font-size:11px; color:#64748b; font-weight:400;">${uom}</span></td>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹${t.unit_price}</td>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#ef4444;">${t.gst}%</td>
                <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:700;">₹${t.total_price}</td>
            </tr>`;
        });

        const html = `
            <div style="background:#f8fafc; padding:16px; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:16px; display:flex; justify-content:space-between;">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Reference Challan</div>
                    <div style="font-size:16px; font-weight:600; color:#0ea5e9;">${first.purchase_details ? first.purchase_details.challan_no : 'N/A'}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px; font-family:monospace;">Batch: ${batch}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Transaction Type</div>
                    <div style="font-weight:600; font-size:14px; color:${first.transaction_type == 1 ? '#059669' : '#ea580c'}">${first.transaction_type == 1 ? 'INWARD (Purchase)' : 'OUTWARD (Transfer)'}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">${date}</div>
                </div>
            </div>

            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px;">Included Items (${group.length})</div>
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left; margin-bottom:20px;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">#</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Product</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Qty & UOM</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Rate (₹)</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">GST %</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>

            <div style="background:#f1f5f9; padding:16px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:13px; font-weight:600; color:#475569;">Total Ledger Value Computed</div>
                <div style="font-size:20px; font-weight:700; color:#0f172a;">₹${totalChallanValue.toFixed(2)}</div>
            </div>
        `;
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection