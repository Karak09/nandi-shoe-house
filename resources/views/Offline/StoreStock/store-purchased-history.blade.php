@extends('Offline.layouts.app')
@section('title', 'Store Inward Ledger - Shoe ERP')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');
    
    :root {
        --brand-dark: #09090b;
        --brand-light: #ffffff;
        --accent: #6366f1;       
        --bg-base: #f4f4f5;
        --text-main: #18181b;
        --text-muted: #71717a;
        --border-light: #e4e4e7;
        --border-strong: #d4d4d8;
        --radius: 8px;
    }

    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg-base); font-family: 'Inter', sans-serif;}
    .topbar { background: var(--brand-light); padding: 16px 32px; border-bottom: 1px solid var(--border-light); }
    .page-title { font-size: 18px; font-weight: 600; color: var(--text-main); margin:0;}
    
    .workspace { padding: 24px 32px; display: flex; flex-direction: column; gap: 20px; }
    .ledger-card { background: var(--brand-light); border-radius: var(--radius); border: 1px solid var(--border-light); display: flex; flex-direction: column; overflow: hidden; }

    /* PARALLEL ROW: Store (Left) | Search (Right) */
    .toolbar-top { 
        padding: 16px 24px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: #fff; 
        border-bottom: 1px solid var(--border-light);
    }
    .store-badge { display: flex; align-items: center; gap: 8px; background: var(--bg-base); padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border-strong); font-size: 13px; }
    .search-box { width: 300px; padding: 10px 14px; border: 1px solid var(--border-strong); border-radius: 6px; font-size: 13px; outline: none; }

    /* DATE FILTER DIV UNDER */
    .filter-area { padding: 12px 24px; background: #fafafa; border-bottom: 1px solid var(--border-light); }
    .global-date-filter { display: flex; align-items: flex-end; gap: 15px; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-group label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
    .filter-input { padding: 8px 12px; border: 1px solid var(--border-strong); border-radius: 6px; font-size: 13px; background: white; }
    .btn-filter { padding: 0 20px; background: var(--brand-dark); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; height: 38px; }
    .btn-reset { padding: 0 16px; background: #f4f4f5; color: #18181b; border: 1px solid #d4d4d8; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; height: 38px; }

    /* TABLE STYLES */
    .table-wrap { overflow-y: auto; flex: 1; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 14px 24px; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-light); background: var(--brand-light); }
    .main-row td { padding: 16px 24px; font-size: 13px; border-bottom: 1px solid var(--border-light); cursor: pointer; }
    .detail-row { display: none; }
    .detail-row.expanded { display: table-row; }
    .num-val { font-weight: 600; font-variant-numeric: tabular-nums; }
    
    /* PAGINATION FOOTER (AS PER PHOTO) */
    .pagination-footer { 
        padding: 16px 24px; 
        border-top: 1px solid var(--border-light); 
        background: #fff; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <header class="topbar">
        <h1 class="page-title">Store Inward Receipts (GRN)</h1>
    </header>

    <div class="workspace">
        <div class="ledger-card">
            
            <div class="toolbar-top">
                <form method="GET" action="{{ route('store_purchase_history.inward') }}" id="storeForm">
                    <div class="store-badge">
                        <span style="color:var(--accent)">📍</span>
                        <select name="store_id" onchange="document.getElementById('storeForm').submit()" style="background:transparent; border:none; font-weight:bold; outline:none; cursor:pointer;">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->store_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="search-side">
                    <input type="text" class="search-box" id="searchBox" placeholder="Search anything...">
                </div>
            </div>

            <div class="filter-area">
                <form method="GET" action="{{ route('store_purchase_history.inward') }}" class="global-date-filter">
                    <input type="hidden" name="store_id" value="{{ request('store_id') }}">
                    <div class="filter-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="filter-input" value="{{ request('start_date', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="filter-input" value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="btn-filter">Filter History</button>
                        <a href="{{ route('store_purchase_history.inward') }}" class="btn-reset">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align:center;">ID</th>
                            <th>Challan Date</th>
                            <th>Challan No</th>
                            <th>Source / User</th>
                            <th style="text-align: center;">Total Items</th>
                            <th style="text-align: right;">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($challans as $challan)
                        <tr class="main-row item-row" onclick="toggleDetails({{ $challan->id }})">
                            <td style="text-align: center;"><span id="icon-{{ $challan->id }}">{{ $loop->iteration }}  ▶</span></td>
                            <td>{{ \Carbon\Carbon::parse($challan->challan_date)->format('d-M-Y') }}</td>
                            <td><b style="color:var(--accent);">{{ $challan->challan_no }}</b></td>
                            <td>
                                <div style="font-weight:600;">Main Godown</div>
                                <div style="font-size:11px; color:var(--text-muted);">
                                    👤 {{ optional($challan->user->details)->f_name ?? 'Admin' }} {{ optional($challan->user->details)->l_name ?? '' }} 
                                    <br>
                                    <span style="color:#94a3b8">({{ \Carbon\Carbon::parse($challan->created_at)->format('h:i A') }})</span>
                                </div>
                            </td>
                            <td style="text-align: center;">{{ $challan->storeStockDetails->count() }} Items</td>
                            <td style="text-align: right;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                                    <span class="num-val">₹ {{ number_format($challan->total, 2) }}</span>
                                    <button onclick="event.stopPropagation(); window.open('{{ route('purchase.print', Crypt::encrypt($challan->id)) }}', '_blank')" style="background:var(--accent); color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:11px;">Print 🖨️</button>
                                </div>
                            </td>
                        </tr>

                        <tr class="detail-row" id="details-{{ $challan->id }}">
                            <td colspan="6" style="padding: 10px 24px; background:#fcfcfc;">
                                <div style="border: 1px solid var(--border-strong); border-radius: 8px; background: #fff; overflow: hidden;">
                                    <table style="width:100%;">
                                        <thead style="background:#f8fafc;">
                                            <tr>
                                                <th style="padding:10px; width:50px; text-align:center;">S.N.</th>
                                                <th>Product Name</th>
                                                <th>Batch No</th>
                                                <th>Barcode No</th>
                                                <th style="text-align:right;">Qty & UOM</th>
                                                <th style="text-align:right;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($challan->storeStockDetails as $item)
                                            <tr>
                                                <td style="padding:10px; text-align:center;">{{ $loop->iteration }}</td>
                                                <td style="padding:10px;"><b>{{ $item->product->name ?? 'N/A' }}</b></td>
                                                <td>
                                                    <div style="font-size: 12px; color: var(--text-muted);"><b>Batch:</b> {{ is_array($item->batch_no) ? implode(', ', $item->batch_no) : $item->batch_no }}</div>
                                                </td>
                                                <td>
                                                    <div style="font-size: 13px; font-family: monospace; font-weight: 700; color: var(--text-main);">{{ $item->barcode_no }}</div>
                                                </td>
                                                <td style="text-align:right;">{{ number_format($item->quantity, 0) }} {{ $item->uomRelation->keyword ?? '' }}</td>
                                                <td style="text-align:right; font-weight:600;">₹{{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px;">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($challans, 'links'))
            <div class="pagination-footer">
                <div style="font-size:13px; color:var(--text-muted)">
                    Showing {{ $challans->firstItem() }} to {{ $challans->lastItem() }} of {{ $challans->total() }} entries
                </div>
                <div>
                    {{ $challans->appends(request()->input())->links() }}
                </div>
            </div>
            @endif
            
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.toggleDetails = function(id) {
        const detailRow = document.getElementById(`details-${id}`);
        const icon = document.getElementById(`icon-${id}`);
        detailRow.classList.toggle('expanded');
        icon.innerText = detailRow.classList.contains('expanded') ? '▼' : '▶';
    };

    document.getElementById('searchBox').addEventListener('input', function(e) {
        let filter = e.target.value.toLowerCase();
        document.querySelectorAll('.item-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection