@extends('Offline.layouts.app')
@section('title', 'Requisition List')
@section('page_title', 'Requisition List')

@section('content')

    <style>
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100% !important;
            white-space: nowrap;
        }

        @media (max-width: 768px) {

            .container-fluid {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }

            .table {
                font-size: 12px;
            }

            .btn-sm {
                padding: 3px 8px;
                font-size: 11px;
            }

            .badge {
                font-size: 10px;
                padding: 4px 6px;
            }

            td, th {
                padding: 6px !important;
                vertical-align: middle !important;
            }

            #viewReqModal {
                padding: 10px !important;
            }

            #viewReqModal > div {
                width: 100% !important;
                max-width: 100% !important;
                padding: 10px !important;
            }
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="table-responsive w-100">
            <table class="table table-bordered table-striped datatable">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>ID</th>
                        <th>Req ID</th>
                        <th>Who Req</th>
                        <th>Where Req</th>
                        <th>Status</th>
                        <th>Total Items</th>
                        <th>Total Amount</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requisitions as $req)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $req->req_id }}</strong></td>
                        <td>{{ $req->creator_name }}</td>
                        <td>{{ $req->where_req_name }}</td>
                        <td>
                            @if($req->status == 1) <span class="badge badge-success">Confirmed</span>
                            @elseif($req->status == 2) <span class="badge badge-warning">Modified</span>
                            @elseif($req->status == 3) <span class="badge badge-danger">Rejected</span>
                            @elseif($req->status == 5) <span class="badge badge-info">Req. Accepted</span>
                            @else <span class="badge badge-secondary">On-Hold</span> @endif
                        </td>
                        <td>{{ $req->items->count() }}</td>
                        <td class="text-primary font-weight-bold">₹ {{ number_format($req->total_amount, 2) }}</td>
                        <td>{{ $req->created_at->format('d M, Y h:i A') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-view shadow-sm" data-req="{{ base64_encode($req->toJson()) }}">View</button>

                            @php
                                $canEdit = false;
                                
                                // 1. Identify roles in this specific transaction
                                $isRequester = ($req->user_id == Auth::id());
                                $isDestination = false;

                                if ($req->where_req == 'Store' && $req->req_store_id == $storeId) {
                                    $isDestination = true; // This store is receiving the request
                                }
                                if ($req->where_req == 'Godown' && $role == 6) {
                                    $isDestination = true; // Role 6 handles requests sent to Godown
                                }

                                // 2. Apply Edit Logic
                                if (in_array($role, [1, 2])) {
                                    $canEdit = true; // SuperAdmin & Admin can always edit
                                } else {
                                    if ($isDestination) {
                                        // Destination (Where Req) ALWAYS sees edit to approve/reject/modify
                                        $canEdit = true;
                                    } elseif ($isRequester) {
                                        // Requester ONLY sees edit if it was Modified (Status 2)
                                        if ($req->status == 2) {
                                            $canEdit = true;
                                        }
                                    }
                                }
                            @endphp

                            @if($canEdit && $req->status != 1 && $req->status != 3)
                                <a href="{{ route('requisition.edit', $req->encrypted_id) }}" class="btn btn-sm btn-primary shadow-sm ml-1">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="viewReqModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div onclick="event.stopPropagation()" style="background:#fff; padding:24px; border-radius:12px; width:900px; max-width:95%; max-height:90vh; overflow-y:auto;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h4>Requisition Details: <span id="modalReqId" class="text-primary"></span></h4>
                <button type="button" onclick="document.getElementById('viewReqModal').style.display='none'" class="btn btn-danger btn-sm">X</button>
            </div>

            <div class="mb-3 alert alert-secondary border">
                <div><strong>Initial Remarks:</strong> <span id="modalRemarks" class="text-muted"></span></div>
                <div id="modalRemarks1Container" style="display: none;"><strong>Sender Remarks:</strong> <span id="modalRemarks1" class="text-dark"></span></div>
                <div id="modalRemarks2Container" style="display: none;"><strong>Requester Remarks:</strong> <span id="modalRemarks2" class="text-dark"></span></div>
                <div id="modalRemarks3Container" style="display: none;"><strong>Final Sender Remarks:</strong> <span id="modalRemarks3" class="text-dark"></span></div>
            </div>

            <table class="table table-bordered table-striped table-sm">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th class="text-center">Req. Qty</th>
                        <th class="text-center">Final Qty</th>
                        <th>UOM</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody id="modalItemsBody"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right font-weight-bold">Grand Total:</td>
                        <td id="modalGrandTotal" class="text-right font-weight-bold text-success"></td>
                    </tr>
                </tfoot>
            </table>

            <div id="requesterActions" style="display: none; border-top: 1px solid #ccc; padding-top: 15px; margin-top: 15px;">
                <label class="text-danger font-weight-bold">Remarks (Mandatory if Rejecting)</label>
                <textarea id="requesterRemarks" class="form-control mb-3" rows="2" placeholder="Enter remarks..."></textarea>
                <div class="text-right">
                    <button class="btn btn-danger" onclick="requesterAction('requester_reject')">Reject Changes</button>
                    <button class="btn btn-success" onclick="requesterAction('requester_accept')">Accept Changes</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        // BACK BUTTON CACHE BUSTER
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) { window.location.reload(); }
        });

        let currentModalEncryptedId = '';

        $(document).on('click', '.btn-view', function () {
            let reqData = $(this).attr('data-req');
            let req = JSON.parse(atob(reqData));
            currentModalEncryptedId = req.encrypted_id;

            $('#modalReqId').text(req.req_id);
            $('#modalRemarks').text(req.remarks || 'N/A');
            
            // Show dynamic remarks
            if(req.remarks1) { $('#modalRemarks1Container').show(); $('#modalRemarks1').text(req.remarks1); } else { $('#modalRemarks1Container').hide(); }
            if(req.remarks2) { $('#modalRemarks2Container').show(); $('#modalRemarks2').text(req.remarks2); } else { $('#modalRemarks2Container').hide(); }
            if(req.remarks3) { $('#modalRemarks3Container').show(); $('#modalRemarks3').text(req.remarks3); } else { $('#modalRemarks3Container').hide(); }

            let html = '';
            let grandTotal = 0;

            if (req.items && req.items.length > 0) {
                req.items.forEach(function(item) {
                    let productName = item.product ? item.product.name : 'Unknown';
                    let unitName = item.unit ? item.unit.name : 'N/A';
                    let price = item.product && item.product.price_master ? parseFloat(item.product.price_master.pro_mrp_price) : 0;
                    
                    // Logic to handle 0 and modified quantities strictly
                    let isModified = (item.modify_quantity !== null);
                    let reqQty = parseFloat(item.quantity);
                    let finalQty = isModified ? parseFloat(item.modify_quantity) : reqQty;
                    
                    let origTotal = price * reqQty;
                    let finalTotal = price * finalQty;
                    grandTotal += finalTotal;

                    let qtyHtml = isModified ? `<del class="text-danger">${reqQty}</del>` : reqQty;
                    let finalQtyHtml = isModified ? `<span class="text-success font-weight-bold">${finalQty}</span>` : `<span class="text-dark">${finalQty}</span>`;
                    
                    let totalHtml = isModified ? `<del class="text-danger">₹${origTotal.toFixed(2)}</del><br><span class="text-success font-weight-bold">₹${finalTotal.toFixed(2)}</span>` : `<span class="text-dark">₹${origTotal.toFixed(2)}</span>`;

                    html += `<tr>
                                <td>${item.product_id}</td>
                                <td><strong>${productName}</strong></td>
                                <td class="text-center">${qtyHtml}</td>
                                <td class="text-center">${finalQtyHtml}</td>
                                <td>${unitName}</td>
                                <td class="text-right">₹${price.toFixed(2)}</td>
                                <td class="text-right">${totalHtml}</td>
                             </tr>`;
                });
            } else {
                html = `<tr><td colspan="7" class="text-center">No Items Found</td></tr>`;
            }

            $('#modalItemsBody').html(html);
            $('#modalGrandTotal').text(`₹${grandTotal.toFixed(2)}`);

            // Requester Action Visibility
            const currentUserId = {{ Auth::id() ?? 0 }};
            if (req.status == 2 && req.user_id == currentUserId && req.req_accept_by == 0) {
                $('#requesterActions').show();
            } else {
                $('#requesterActions').hide();
            }

            document.getElementById('viewReqModal').style.display = 'flex';
        });

        async function requesterAction(action) {
            const remarks = document.getElementById('requesterRemarks').value.trim();
            if(action === 'requester_reject' && remarks === '') { toastr.error('Remarks are mandatory.'); return; }

            const formData = new FormData();
            formData.append('action', action);
            formData.append('remarks', remarks);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch(`{{ url('requisition/process') }}/${currentModalEncryptedId}`, { method: 'POST', body: formData });
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