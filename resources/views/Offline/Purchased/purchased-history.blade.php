@extends('Offline.layouts.app')
@section('title', 'Purchase History - Shoe ERP')
@section('content')

<div class="main-content">
    <div class="workspace">
        
        <form method="GET" action="{{ route('purchased.history') }}" class="global-date-filter">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="filter-input" value="{{ request('start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="end_date" class="filter-input" value="{{ request('end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-filter">Filter History</button>
                <a href="{{ route('purchased.history') }}" class="btn-reset">Reset</a>
            </div>
        </form>

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
                            <th>Vendor Name</th>
                            <th>Total Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th data-sortable="false" style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($challans as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($c->challan_date)->format('d-M-Y') }}</td>
                            <td style="font-weight:600; color:#0ea5e9;">{{ $c->challan_no }}</td>
                            <td>{{ $c->vendor->vendor_name ?? 'Unknown' }}</td>
                            <td>{{ $c->transactions->count() }}</td>
                            <td class="num-col">₹ {{ number_format($c->total, 2) }}</td>
                            <td><span class="badge">Stock Updated</span></td>
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
    <div onclick="event.stopPropagation()" style="background:#fff; padding:24px; border-radius:8px; width:950px; max-height: 90vh; display: flex; flex-direction: column;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between;">
            <span>Challan Details</span>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; flex-grow: 1; padding-right:8px;"></div>
    </div>
</div>

<div id="imageLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.95); z-index:10000; align-items:center; justify-content:center;">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 40px; background: none; border: none; color: white; font-size: 40px; cursor: pointer; font-weight: bold; z-index:10001;">&times;</button>
    <img id="lightboxImg" src="" style="transition: transform 0.3s ease; transform-origin: center center; max-width: 90vw; max-height: 90vh;">
    <div style="position:absolute; bottom: 40px; display:flex; gap:15px; background:rgba(255,255,255,0.15); padding:10px 20px; border-radius:30px; z-index:10001;">
        <button type="button" onclick="zoomImg(0.5)" style="color:white; border:1px solid white; background:rgba(0,0,0,0.6); padding:8px 16px; border-radius:6px; cursor:pointer;">➕ Zoom In</button>
        <button type="button" onclick="zoomImg(-0.5)" style="color:white; border:1px solid white; background:rgba(0,0,0,0.6); padding:8px 16px; border-radius:6px; cursor:pointer;">➖ Zoom Out</button>
    </div>
</div>

@push('scripts')
<script>
    // --- ZOOM LIGHTBOX LOGIC ---
    let currentZoom = 1;
    window.openLightbox = function(src) {
        if(!src) return;
        currentZoom = 1;
        const img = document.getElementById('lightboxImg');
        img.style.transform = `scale(1)`;
        img.src = src;
        document.getElementById('imageLightbox').style.display = 'flex';
    };
    window.closeLightbox = function() {
        document.getElementById('imageLightbox').style.display = 'none';
    };
    window.zoomImg = function(step) {
        currentZoom += step;
        if(currentZoom < 0.5) currentZoom = 0.5;
        if(currentZoom > 5) currentZoom = 5;
        document.getElementById('lightboxImg').style.transform = `scale(${currentZoom})`;
    };

    // --- VIEW MODAL ---
    window.viewChallan = function(c) {
        let rows = '';
        if(c.transactions) {
            c.transactions.forEach((t, i) => {
                let benName = (t.product && t.product.ben_name) ? ` <span style="color:#64748b; font-weight:400; font-size:12px;">(${t.product.ben_name})</span>` : '';
                let uom = t.uom_relation ? t.uom_relation.keyword : '';
                
                rows += `<tr>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#64748b;">${i+1}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.product ? t.product.name : 'N/A'}${benName}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:600;">${t.quantity} <span style="font-size:11px; color:#64748b; font-weight:400;">${uom}</span></td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0;">₹${t.unit_price}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#64748b;">₹${t.mrp}</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; color:#ef4444;">${t.gst}%</td>
                    <td style="padding:10px 8px; border-bottom:1px solid #e2e8f0; font-weight:700;">₹${t.total_price}</td>
                </tr>`;
            });
        }

        // Build Uploaded Image Gallery
        let imageGallery = '';
        ['fst', 'sec', 'trd', 'foth', 'fiv'].forEach(slot => {
            let doc = c[`${slot}_image_doc`];
            if(doc) {
                imageGallery += `<img src="/storage/${doc}" class="challan-img-thumb" onclick="openLightbox(this.src)" title="Click to Zoom">`;
            }
        });
        if(!imageGallery) imageGallery = '<span style="font-size:12px; color:#64748b; font-style:italic;">No Challan images uploaded.</span>';

        const html = `
            <div style="display:grid; grid-template-columns:1fr 1fr 1.5fr; gap:16px; margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:6px; border:1px solid #e2e8f0;">
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Challan No</div>
                    <div style="font-weight:600; font-size:15px; color:#0ea5e9;">${c.challan_no}</div>
                </div>
                <div>
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700;">Vendor</div>
                    <div style="font-weight:600; font-size:15px;">${c.vendor ? c.vendor.vendor_name : 'Unknown'}</div>
                </div>
                <div style="border-left: 1px solid #cbd5e1; padding-left: 16px;">
                    <div style="font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:6px;">Uploaded Challan Images</div>
                    <div style="display:flex; gap:8px;">${imageGallery}</div>
                </div>
            </div>
            
            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">#</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Product</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Qty & UOM</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Rate (₹)</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">MRP (₹)</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">GST %</th>
                        <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1;">Total (₹)</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:16px; border-top:2px dashed #cbd5e1;">
                <div style="font-size:13px; color:#64748b;">
                    <strong>Remarks:</strong> <br> ${c.command ? c.command : 'None'}
                </div>
                <div style="font-size:22px; font-weight:700; color:#0f172a;">
                    Grand Total: ₹${c.total}
                </div>
            </div>
        `;
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('viewModal').style.display = 'flex';
    };
</script>
@endpush
@endsection