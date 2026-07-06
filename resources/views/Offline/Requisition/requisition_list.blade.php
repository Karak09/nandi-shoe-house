@extends('Offline.layouts.app')
@section('title', 'Requisition List')
@push('styles')
<style>
    .req-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; }
    .req-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
    .req-badge.confirmed { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .req-badge.confirmed .dot { background: #22c55e; }
    .req-badge.modified { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .req-badge.modified .dot { background: #f59e0b; }
    .req-badge.rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .req-badge.rejected .dot { background: #ef4444; }
    .req-badge.accepted { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
    .req-badge.accepted .dot { background: #0ea5e9; }
    .req-badge.onhold { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .req-badge.onhold .dot { background: #94a3b8; }

    .btn-view-req { padding: 5px 10px; font-size: 11px; cursor: pointer; background: #fff; border: 1px solid #cbd5e1; border-radius: 5px; color: #0f172a; font-weight: 600; transition: 0.2s; }
    .btn-view-req:hover { background: #f1f5f9; border-color: #94a3b8; }
    .btn-edit-req { padding: 5px 10px; font-size: 11px; cursor: pointer; background: #2563eb; border: none; border-radius: 5px; color: #fff; font-weight: 600; text-decoration: none; transition: 0.2s; display: inline-block; }
    .btn-edit-req:hover { background: #1d4ed8; }

    .req-id-text { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0ea5e9; font-size: 13px; }
    .modal-req { background: #fff; border-radius: 16px; width: 100%; max-width: 1000px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: modalIn 0.25s ease; }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    .modal-req-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 16px 16px 0 0; }
    .modal-req-body { flex: 1; overflow-y: auto; padding: 20px 24px; }
    .modal-req-body::-webkit-scrollbar { width: 5px; }
    .modal-req-body::-webkit-scrollbar-track { background: transparent; }
    .modal-req-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 8px; }

    .modal-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; background: #f8fafc; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0; }
    .modal-info-item label { display: block; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .modal-info-item span { font-weight: 600; font-size: 14px; color: #0f172a; }

    .datatable td:last-child { white-space: nowrap; }
    .datatable td:last-child a,
    .datatable td:last-child button { display: inline-flex; align-items: center; justify-content: center; margin: 2px; vertical-align: middle; }

    .status-filter { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #0f172a; outline: none; background: #f8fafc; cursor: pointer; transition: 0.2s; min-width: 140px; }
    .status-filter:focus { border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1); }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('requisition.list') }}" class="filter-bar">
    <div class="global-date-filter">
        <div class="filter-group">
            <label>From Date</label>
            <input type="date" name="from_date" class="filter-input" value="{{ $fromDate }}">
        </div>
        <div class="filter-group">
            <label>To Date</label>
            <input type="date" name="to_date" class="filter-input" value="{{ $toDate }}">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="status-filter">
                <option value="">All Status</option>
                <option value="4" {{ request('status') === '4' ? 'selected' : '' }}>On-Hold</option>
                <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Modified</option>
                <option value="5" {{ request('status') === '5' ? 'selected' : '' }}>Req. Accepted</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Confirmed</option>
                <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ route('requisition.list') }}" class="btn-reset">Reset</a>
        </div>
    </div>
    <a href="{{ route('requisition.create') }}" class="btn-filter" style="text-decoration:none; height:36px; display:inline-flex; align-items:center; padding:0 20px;">+ New Requisition</a>
</form>

<div class="card">
    <div class="card-header">
        <span>Requisition List</span>
        <span class="text-muted" style="font-size:12px;font-weight:400;">{{ $requisitions->count() }} entries</span>
    </div>
    <div class="table-container">
        <table class="datatable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Req ID</th>
                    <th>Requester</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Date & Time</th>
                    <th data-sortable="false" style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requisitions as $req)
                <tr>
                    <td style="color:#64748b;">{{ $loop->iteration }}</td>
                    <td><span class="req-id-text">{{ $req->req_id }}</span></td>
                    <td>
                        <div style="font-weight:600; font-size:13px;">{{ $req->creator_name }}</div>
                        <div style="font-size:11px; color:#64748b;">{{ $req->who_req_name }}</div>
                    </td>
                    <td style="font-weight:500;">{{ $req->where_req_name }}</td>
                    <td>
                        @if($req->status == 1) <span class="req-badge confirmed"><span class="dot"></span>Confirmed</span>
                        @elseif($req->status == 2) <span class="req-badge modified"><span class="dot"></span>Modified</span>
                        @elseif($req->status == 3) <span class="req-badge rejected"><span class="dot"></span>Rejected</span>
                        @elseif($req->status == 5) <span class="req-badge accepted"><span class="dot"></span>Req. Accepted</span>
                        @else <span class="req-badge onhold"><span class="dot"></span>On-Hold</span> @endif
                    </td>
                    <td class="num-col">{{ $req->items->count() }}</td>
                    <td class="num-col" style="font-weight:700; color:#059669;">₹{{ number_format($req->total_amount, 2) }}</td>
                    <td style="font-size:12px; color:#64748b;">{{ $req->created_at->format('d M, Y h:i A') }}</td>
                    <td style="text-align:right;">
                        <button class="btn-view-req" onclick='openViewModal(@json($req))'>
                            <span style="font-size:14px;line-height:1;">&#128065;</span> View
                        </button>

                        @php
                            $canEdit = false;
                            $isRequester = ($req->user_id == Auth::id());
                            $isDestination = false;

                            if ($req->where_req == 'Store' && $req->req_store_id == $storeId) {
                                $isDestination = true;
                            }
                            if ($req->where_req == 'Godown' && $role == 6) {
                                $isDestination = true;
                            }

                            if (in_array($role, [1, 2])) {
                                $canEdit = true;
                            } else {
                                if ($isDestination) {
                                    $canEdit = true;
                                } elseif ($isRequester) {
                                    if ($req->status == 2) {
                                        $canEdit = true;
                                    }
                                }
                            }
                        @endphp

                        @if($canEdit && $req->status != 1 && $req->status != 3)
                            <a href="{{ route('requisition.edit', $req->encrypted_id) }}" class="btn-edit-req ml-1">
                                <span style="font-size:14px;line-height:1;">&#9998;</span> Edit
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:#64748b;">No requisitions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="viewReqModal" onclick="if(event.target===this)closeViewModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.7); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(4px);">
    <div class="modal-req" onclick="event.stopPropagation()">
        <div class="modal-req-header">
            <div>
                <h2 style="font-size:17px; font-weight:700; margin:0; display:flex; align-items:center; gap:10px;">
                    <span style="width:30px; height:30px; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-size:14px;">&#128203;</span>
                    Requisition: <span id="modalReqId" style="color:#0ea5e9; font-family:'JetBrains Mono',monospace;"></span>
                </h2>
            </div>
            <button onclick="closeViewModal()" style="background:none; border:none; font-size:24px; color:#94a3b8; cursor:pointer; padding:4px 8px; border-radius:6px; line-height:1;">&times;</button>
        </div>
        <div class="modal-req-body">
            <div class="modal-info-grid">
                <div class="modal-info-item">
                    <label>Initial Remarks</label>
                    <span id="modalRemarks" style="color:#64748b; font-weight:400;"></span>
                </div>
                <div class="modal-info-item" id="modalRemarks1Container" style="display:none;">
                    <label>Sender Remarks</label>
                    <span id="modalRemarks1"></span>
                </div>
                <div class="modal-info-item" id="modalRemarks2Container" style="display:none;">
                    <label>Requester Remarks</label>
                    <span id="modalRemarks2"></span>
                </div>
                <div class="modal-info-item" id="modalRemarks3Container" style="display:none;">
                    <label>Final Sender Remarks</label>
                    <span id="modalRemarks3"></span>
                </div>
            </div>

            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left; min-width:550px;">
                    <thead style="background:#f1f5f9;">
                        <tr>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase;">ID</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase;">Product</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase; text-align:center;">Req. Qty</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase; text-align:center;">Final Qty</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase;">UOM</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase; text-align:right;">Price</th>
                            <th style="padding:10px 8px; border-bottom:1px solid #cbd5e1; font-size:10px; color:#64748b; text-transform:uppercase; text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="modalItemsBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="padding:12px 8px; text-align:right; font-weight:700; border-top:2px solid #e2e8f0; font-size:13px;">Grand Total:</td>
                            <td id="modalGrandTotal" style="padding:12px 8px; text-align:right; font-weight:700; color:#059669; border-top:2px solid #e2e8f0; font-size:15px;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="requesterActions" style="display: none; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px;">
                <label style="font-size:13px; font-weight:700; color:#dc2626; display:block; margin-bottom:8px;">Remarks (Mandatory if Rejecting)</label>
                <textarea id="requesterRemarks" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none; min-height:60px; resize:vertical;" placeholder="Enter remarks..."></textarea>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
                    <button class="btn-filter" style="background:#dc2626;" onclick="requesterAction('requester_reject')">Reject Changes</button>
                    <button class="btn-filter" style="background:#059669;" onclick="requesterAction('requester_accept')">Accept Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) { window.location.reload(); }
    });

    let currentModalEncryptedId = '';

    window.openViewModal = function(req) {
        currentModalEncryptedId = req.encrypted_id;

        document.getElementById('modalReqId').textContent = req.req_id;
        document.getElementById('modalRemarks').textContent = req.remarks || 'N/A';

        let r1 = document.getElementById('modalRemarks1Container');
        let r1s = document.getElementById('modalRemarks1');
        if(req.remarks1) { r1.style.display = 'block'; r1s.textContent = req.remarks1; } else { r1.style.display = 'none'; }

        let r2 = document.getElementById('modalRemarks2Container');
        let r2s = document.getElementById('modalRemarks2');
        if(req.remarks2) { r2.style.display = 'block'; r2s.textContent = req.remarks2; } else { r2.style.display = 'none'; }

        let r3 = document.getElementById('modalRemarks3Container');
        let r3s = document.getElementById('modalRemarks3');
        if(req.remarks3) { r3.style.display = 'block'; r3s.textContent = req.remarks3; } else { r3.style.display = 'none'; }

        let html = '';
        let grandTotal = 0;

        if (req.items && req.items.length > 0) {
            req.items.forEach(function(item) {
                let productName = item.product ? item.product.name : 'Unknown';
                let unitName = item.unit ? item.unit.name : 'N/A';
                let price = item.product && item.product.price_master ? parseFloat(item.product.price_master.pro_mrp_price) : 0;

                let isModified = (item.modify_quantity !== null);
                let reqQty = parseFloat(item.quantity);
                let finalQty = isModified ? parseFloat(item.modify_quantity) : reqQty;

                let origTotal = price * reqQty;
                let finalTotal = price * finalQty;
                grandTotal += finalTotal;

                let qtyHtml = isModified ? '<del style="color:#dc2626;">' + reqQty + '</del>' : reqQty;
                let finalQtyHtml = isModified ? '<span style="color:#059669; font-weight:700;">' + finalQty + '</span>' : '<span>' + finalQty + '</span>';
                let totalHtml = isModified ? '<del style="color:#dc2626;">₹' + origTotal.toFixed(2) + '</del><br><span style="color:#059669; font-weight:700;">₹' + finalTotal.toFixed(2) + '</span>' : '<span>₹' + origTotal.toFixed(2) + '</span>';

                html += '<tr>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; color:#64748b;">' + item.product_id + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; font-weight:600;">' + productName + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; text-align:center;">' + qtyHtml + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; text-align:center;">' + finalQtyHtml + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9;">' + unitName + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; text-align:right;">₹' + price.toFixed(2) + '</td>' +
                    '<td style="padding:10px 8px; border-bottom:1px solid #f1f5f9; text-align:right;">' + totalHtml + '</td>' +
                '</tr>';
            });
        } else {
            html = '<tr><td colspan="7" style="padding:24px; text-align:center; color:#64748b;">No Items Found</td></tr>';
        }

        document.getElementById('modalItemsBody').innerHTML = html;
        document.getElementById('modalGrandTotal').textContent = '₹' + grandTotal.toFixed(2);

        const currentUserId = {{ Auth::id() ?? 0 }};
        const actionsDiv = document.getElementById('requesterActions');
        if (req.status == 2 && req.user_id == currentUserId && req.req_accept_by == 0) {
            actionsDiv.style.display = 'block';
        } else {
            actionsDiv.style.display = 'none';
        }

        document.getElementById('viewReqModal').style.display = 'flex';
    };

    window.closeViewModal = function() {
        document.getElementById('viewReqModal').style.display = 'none';
    };

    async function requesterAction(action) {
        const remarks = document.getElementById('requesterRemarks').value.trim();
        if(action === 'requester_reject' && remarks === '') { toastr.error('Remarks are mandatory.'); return; }

        const formData = new FormData();
        formData.append('action', action);
        formData.append('remarks', remarks);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch('{{ url("requisition/process") }}/' + currentModalEncryptedId, { method: 'POST', body: formData });
            const data = await res.json();
            if(res.ok && data.status === 'success') {
                toastr.success('Success');
                setTimeout(() => window.location.reload(), 1000);
            } else { toastr.error(data.message); }
        } catch (e) { toastr.error('System Error.'); }
    }
</script>
@endpush

@endsection