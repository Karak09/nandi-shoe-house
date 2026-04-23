@extends('Offline.layouts.app')
@section('title', 'Purchase History - Shoe ERP')
@section('content')

<div class="main-content">
    <div class="workspace">
        
        <div class="filter-bar">
            <div class="filter-group">
                <label>Select Store</label>
                <select class="store-select" onchange="changeStore(this.value)">
                    <option value="">All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->enc_id }}" {{ (isset($enc_store_id) && $enc_store_id == $store->enc_id) ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <form method="GET" action="{{ $enc_store_id ? route('store_purchase_history.inward', $enc_store_id) : route('store_purchase_history.inward') }}" class="global-date-filter">
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="filter-input" value="{{ $start_date }}" required>
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="end_date" class="filter-input" value="{{ $end_date }}" required>
                </div>
                <div class="filter-btn-group" style="display:flex; gap:8px;">
                    <button type="submit" class="btn-filter">Filter History</button>
                    <a href="{{ route('store_purchase_history.inward') }}" class="btn-reset">Reset</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <span>Recent Purchase Challans</span>
            </div>
            <div class="table-container">
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Challan Date</th>
                            <th>Challan No</th>
                            <th>Store Name</th>
                            <th>Total Items</th>
                            <th>Total Amount</th>
                            <th data-sortable="false" style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($challans as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($c->challan_date)->format('d-M-Y') }}</td>
                            <td style="font-weight:600; color:#0ea5e9;">{{ $c->challan_no }}</td>
                            <td style="font-weight:600;">{{ $c->storeStockDetails->first()->store->store_name ?? 'N/A' }}</td>
                            <td>{{ $c->storeStockDetails->count() }}</td>
                            <td class="num-col">₹ {{ number_format($c->total, 2) }}</td>
                            <td style="text-align:right;">
                                <div style="display:flex; gap:6px; justify-content:flex-end;">
                                    <button class="btn-outline" style="padding:6px 10px; font-size:11px; cursor:pointer;" onclick='viewChallan(@json($c))'>👁️ View</button>
                                    
                                    <a href="{{ route('purchase.print', $c->enc_id) }}" target="_blank" class="btn-outline" style="padding:6px 10px; font-size:11px; cursor:pointer; text-decoration:none; color:inherit;">🖨️ Print</a>
                                    
                                    @if($c->total_qty > 0)
                                        <button class="btn-outline" style="padding:6px 10px; font-size:11px; cursor:pointer;" onclick='printBarcodes(@json($c->storeStockDetails))'>🏷️ Barcode</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="viewModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 16px;">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between; align-items: center;">
            <span style="margin: 0;">Challan Details</span>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:none; border:none; font-size:28px; line-height: 1; cursor:pointer; color:#64748b; padding: 0;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; flex-grow: 1; padding-right:4px;"></div>
    </div>
</div>

<script>
    function changeStore(encStoreId) {
        let startDate = document.getElementById('start_date').value;
        let endDate = document.getElementById('end_date').value;
        let baseUrl = "{{ url('store-purchase-history') }}";
        
        let targetUrl = encStoreId ? `${baseUrl}/${encStoreId}` : baseUrl;
        
        window.location.href = `${targetUrl}?start_date=${startDate}&end_date=${endDate}`;
    }

    function printBarcodes(details) {
        let barcodeData = details.filter(d => parseFloat(d.quantity) > 0).map(d => ({
            name: d.product ? d.product.name : 'Unknown Product',
            barcode: d.barcode_no,
            mrp: parseFloat(d.mrp).toFixed(2),
            quantity: Math.floor(d.quantity) 
        }));

        if(barcodeData.length > 0) {
            localStorage.setItem('print_barcodes', JSON.stringify(barcodeData));
            window.open('{{ route("store_stock.print_barcodes") }}', '_blank');
        } else {
            alert("No items with quantity available for barcodes.");
        }
    }

    window.viewChallan = function(c) {
        let rows = '';
        if(c.store_stock_details) {
            c.store_stock_details.forEach((t, i) => {
                let uom = t.uom_relation ? t.uom_relation.keyword : '';
                rows += `<tr>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">${i+1}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.product ? t.product.name : 'N/A'}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.quantity} <span style="font-size:11px;">${uom}</span></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹${t.unit_price}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹${t.mrp}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">${t.gst}%</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:700;">₹${t.total_price}</td>
                </tr>`;
            });
        }

        const storeName = (c.store_stock_details && c.store_stock_details.length > 0) ? c.store_stock_details[0].store.store_name : 'N/A';

        const html = `
            <div class="modal-grid">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Challan No</div>
                    <div style="font-weight:600; font-size:15px; color:#0ea5e9;">${c.challan_no}</div>
                </div>
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Store</div>
                    <div style="font-weight:600; font-size:15px;">${storeName}</div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead style="background:#f1f5f9;">
                        <tr>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">ID</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Product Name</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Qty & UOM</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Unit Price (₹)</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">MRP (₹)</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">GST %</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:20px; padding-top:16px; border-top:2px dashed #cbd5e1; flex-wrap: wrap; gap: 10px;">
                <div style="font-size:13px; color:#64748b;"><br></div>
                <div style="font-size:22px; font-weight:700; color:#0f172a;">Grand Total: ₹${c.total}</div>
            </div>
        `;
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endsection