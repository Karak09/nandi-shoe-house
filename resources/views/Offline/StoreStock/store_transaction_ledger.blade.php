@extends('Offline.layouts.app')
@section('title', 'Godown Transfer Ledger (OUT) - Shoe ERP')

@push('styles')
<style>
    /* Identical to Purchase History */
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #f1f5f9; }
    
    .topbar { background: #ffffff; padding: 16px 32px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; z-index: 5; }
    .store-selector { display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 6px 16px; border-radius: 30px; border: 1px solid #cbd5e1; }
    .store-selector select { background: transparent; border: none; font-size: 14px; font-weight: 600; color: #0f172a; outline: none; cursor: pointer; padding-right: 8px; }
    .store-badge { background: #ea580c; color: white; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.5px; }

    .workspace { padding: 24px; display: flex; flex-direction: column; gap: 24px; height: 100%; overflow: hidden; }

    .card { background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 600; font-size: 16px; background-color: #f8fafc; color: #0f172a; display: flex; justify-content: space-between; align-items: center;}
    
    .table-container { overflow-y: auto; flex: 1; padding: 10px; }
    
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 12px 16px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #cbd5e1; position: sticky; top: 0; background: #fff; z-index: 5; }
    td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #e2e8f0; color: #0f172a; vertical-align: middle; }
    tr:hover td { background-color: #f8fafc; }
    
    .num-col { font-variant-numeric: tabular-nums; font-weight: 600; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background-color: #fff7ed; color: #ea580c; border:1px solid #fed7aa;}
</style>
@endpush

@section('content')
<div class="main-content">
    <header class="topbar">
        <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin:0;">Godown Transfer Ledger (OUT)</h1>
        <form method="GET" action="{{ route('store_all_stock.transaction') }}" id="storeForm" class="store-selector">
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

    <div class="workspace">
        
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

    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px; border-radius:8px; width:800px; max-height: 90vh; display: flex; flex-direction: column;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">Transfer Details (OUT)</h2>
        <div id="modalContent" style="overflow-y: auto; flex-grow: 1;"></div>
    </div>
</div>

@push('scripts')
<script>
    window.viewChallan = function(c) {
        let rows = '';
        if(c.transactions) {
            c.transactions.forEach((t, i) => {
                rows += `<tr>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0;">${i+1}</td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.product ? t.product.name : 'N/A'}</td>
                    <td style="padding:8px; border-bottom:1px solid #e2e8f0; color:#ea580c; font-weight:bold;">${Math.abs(t.quantity)}</td>
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
            
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">#</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Product</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Qty Transferred</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Rate (₹)</th>
                        <th style="padding:8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            
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