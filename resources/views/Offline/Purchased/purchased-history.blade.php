@extends('Offline.layouts.app')
@section('title', 'Purchase History - Shoe ERP')
@push('styles')
<style>
    .modal-info-grid { display:grid; grid-template-columns:1fr 1fr 1.5fr; gap:12px; margin-bottom:16px; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0; }
    .modal-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; margin-bottom:12px; }
    @media (max-width:768px) {
        .modal-info-grid { grid-template-columns:1fr; gap:10px; padding:10px; }
        .modal-images-col { border-left:none !important; padding-left:0 !important; }
    }
</style>
@endpush
@section('content')

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
                            <td style="text-align:right; white-space:nowrap;">
                                <button onclick='viewChallan(@json($c))' style="padding:5px 8px; font-size:11px; cursor:pointer; background:#fff; border:1px solid #cbd5e1; border-radius:4px; color:#0f172a; font-weight:600; vertical-align:middle; line-height:1;">👁️ View</button>
                                <a href="{{ route('purchased.print', $c->enc_id) }}" target="_blank" style="padding:5px 8px; font-size:11px; cursor:pointer; background:#fff; border:1px solid #cbd5e1; border-radius:4px; color:#0f172a; font-weight:600; text-decoration:none; display:inline-block; vertical-align:middle; line-height:1; margin-left:4px;">🖨️ Print</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

<div id="viewModal" onclick="if(event.target===this)closeViewModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding: 16px;">
    <div onclick="event.stopPropagation()" style="background:#fff; padding:20px; border-radius:8px; width:100%; max-width:950px; max-height: 90vh; display: flex; flex-direction: column; margin:0 auto;">
        <h2 style="font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center;">
            <span>Challan Details</span>
            <button onclick="closeViewModal()" style="background:none; border:none; font-size:28px; cursor:pointer; color:#64748b; line-height:1; padding:0;">&times;</button>
        </h2>
        <div id="modalContent" style="overflow-y: auto; flex-grow: 1; padding-right:4px;"></div>
    </div>
</div>

<div id="imageLightbox" onclick="if(event.target===this)closeLightbox()" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.95); z-index:10000; align-items:center; justify-content:center;">
    <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 40px; cursor: pointer; font-weight: bold; z-index:10001; line-height:1;">&times;</button>
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

    // --- CLOSE VIEW MODAL ---
    window.closeViewModal = function() {
        document.getElementById('viewModal').style.display = 'none';
    };

    // --- VIEW MODAL ---
    window.viewChallan = function(c) {
        let rows = '';
        if(c.transactions) {
            c.transactions.forEach((t, i) => {
                let benName = (t.product && t.product.ben_name) ? ` <span style="color:#64748b; font-weight:400; font-size:12px;">(${t.product.ben_name})</span>` : '';
                let uom = t.uom_relation ? t.uom_relation.keyword : '';
                
                rows += `<tr>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:12px;">${i+1}</td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; font-weight:600; font-size:12px;">${t.product ? t.product.name : 'N/A'}${benName}</td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; font-weight:600; font-size:12px;">${t.quantity} <span style="font-size:10px; color:#64748b; font-weight:400;">${uom}</span></td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; font-size:12px;">₹${t.unit_price}</td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:12px;">₹${t.mrp}</td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; color:#ef4444; font-size:12px;">${t.gst}%</td>
                    <td style="padding:8px 6px; border-bottom:1px solid #e2e8f0; font-weight:700; font-size:12px;">₹${t.total_price}</td>
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
            <div class="modal-info-grid">
                <div>
                    <div style="font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700;">Challan No</div>
                    <div style="font-weight:600; font-size:14px; color:#0ea5e9;">${c.challan_no}</div>
                </div>
                <div>
                    <div style="font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700;">Vendor</div>
                    <div style="font-weight:600; font-size:14px;">${c.vendor ? c.vendor.vendor_name : 'Unknown'}</div>
                </div>
                <div class="modal-images-col" style="border-left:1px solid #cbd5e1; padding-left:12px;">
                    <div style="font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700; margin-bottom:6px;">Uploaded Challan Images</div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">${imageGallery}</div>
                </div>
            </div>
            
            <div class="modal-table-wrap">
                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left; min-width:500px;">
                    <thead style="background:#f1f5f9;">
                        <tr>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">ID</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">Product</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">Qty & UOM</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">Price (₹)</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">MRP (₹)</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">GST %</th>
                            <th style="padding:8px 6px; border-bottom:1px solid #cbd5e1; font-size:10px;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:12px; border-top:2px dashed #cbd5e1; flex-wrap:wrap; gap:8px;">
                <div style="font-size:12px; color:#64748b;">
                    <strong>Remarks:</strong> ${c.command ? c.command : 'None'}
                </div>
                <div style="font-size:20px; font-weight:700; color:#0f172a;">
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