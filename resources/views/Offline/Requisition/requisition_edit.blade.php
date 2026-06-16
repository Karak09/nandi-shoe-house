@extends('Offline.layouts.app')
@section('title', 'Process Requisition')
@section('page_title', 'Process Requisition')

@section('content')

    <style>
        .card-body-scroll { max-height: 600px; overflow-y: auto; }
        .req-input { width: 80px; text-align: center; font-weight: bold; display: none; }
        .req-text { font-weight: bold; font-size: 15px; }
        .barcode-select { display: none; width: 100%; margin-top: 8px; padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; }
        .flex-container { display: flex; align-items: center; gap: 10px; }
        
        @media (max-width: 768px) {
            .row > .col-md-6 { flex: 0 0 100%; max-width: 100%; margin-bottom: 20px; }
            .action-buttons button { width: 100%; margin-bottom: 10px; }
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="alert alert-secondary d-flex justify-content-between align-items-center flex-wrap">
            <div><strong>Req ID:</strong> {{ $requisition->req_id }}</div>
            <h4>
                @if($requisition->status == 2) <span class="badge badge-warning">MODIFIED</span> 
                @elseif($requisition->status == 5) <span class="badge badge-info">REQ. ACCEPTED</span> 
                @elseif($requisition->status == 4) <span class="badge badge-secondary">NEW</span> 
                @endif
            </h4>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-dark">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Current Availability at Destination</h5>
                    </div>
                    <div class="card-body p-0 card-body-scroll">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Available Stock Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $item)
                                @php
                                    $stock = $stockData[$item->product_id];
                                    $productName = $item->product->name ?? 'Unknown';
                                @endphp
                                <tr>
                                    <td class="align-middle"><strong>{{ $productName }}</strong></td>
                                    <td class="align-middle text-primary font-weight-bold">₹{{ number_format($stock['price'], 2) }}</td>
                                    <td>
                                        <div class="mb-2"><strong>Total Available:</strong> <span class="text-success font-weight-bold">{{ $stock['available_qty'] }}</span></div>
                                        
                                        @if(count($stock['barcodes']) > 0)
                                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                                @foreach($stock['barcodes'] as $bc => $qty)
                                                    <li class="border-bottom pb-1 mb-1">
                                                        <strong>Barcode:</strong> {{ $bc }} | <strong>Qty:</strong> {{ $qty }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Requested Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <form id="processForm">
                            <div class="table-responsive card-body-scroll">
                                <table class="table table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Requested Qty</th>
                                            <th>UOM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($requisition->items as $item)
                                        <tr>
                                            <td class="align-middle"><strong>{{ $item->product->name ?? 'Unknown' }}</strong></td>
                                            <td class="align-middle">
                                                <div class="flex-container">
                                                    <span class="req-text">

                                                        @if($item->modify_quantity !== null)

                                                            {{ $item->modify_quantity }}

                                                            <small class="text-muted">
                                                                (Original: {{ $item->quantity }})
                                                            </small>

                                                        @else

                                                            {{ $item->quantity }}

                                                        @endif

                                                        </span>
                                                    <input type="number" name="items[{{ $item->id }}][modify_quantity]" class="form-control req-input" value="{{ $item->quantity }}" min="0" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                </div>

                                                @if($requisition->where_req == 'Store')
                                                    <select name="items[{{ $item->id }}][barcode]" class="barcode-select">
                                                        <option value="">-- Select Barcode --</option>
                                                        @foreach($stockData[$item->product_id]['barcodes'] as $bc => $qty)
                                                            <option value="{{ $bc }}">{{ $bc }} (Avail: {{ $qty }})</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            <td class="align-middle">{{ $item->unit->name ?? 'Unknown' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-3 bg-light border-top">
                                <div id="remarksContainer" style="display: none;">
                                    <label class="font-weight-bold text-danger">Remarks (Mandatory)</label>
                                    <textarea name="remarks" id="remarks" class="form-control mb-3" placeholder="Enter remarks..."></textarea>
                                </div>
                                
                                <div class="text-right action-buttons">
                                    {{-- Requester --}}
                                    @if($isRequester)

                                        @if($requisition->status == 2)

                                            <button type="button"
                                                class="btn btn-success"
                                                onclick="processReq('requester_accept')">
                                                Accept Modification
                                            </button>

                                            <button type="button"
                                                class="btn btn-danger"
                                                onclick="prepareAction('requester_reject')">
                                                Reject Modification
                                            </button>

                                        @endif

                                    {{-- Destination Store / Godown --}}
                                    @elseif($isSender)

                                        @if($requisition->status != 5)

                                            <button type="button"
                                                class="btn btn-warning mr-2"
                                                id="modifyBtn"
                                                onclick="enableModify()">
                                                Modify
                                            </button>

                                        @endif

                                        <button type="button"
                                            class="btn btn-danger mr-2"
                                            id="rejectBtn"
                                            onclick="prepareAction('reject')">
                                            Reject
                                        </button>

                                        <button type="button"
                                            class="btn btn-success"
                                            id="approveBtn"
                                            onclick="prepareApprove()">
                                            Approve
                                        </button>

                                    @endif

                                    <button type="button"
                                        id="submitModifyBtn"
                                        class="btn btn-warning"
                                        style="display:none;"
                                        onclick="processReq('modify')">
                                        Submit Modification
                                    </button>

                                    <button type="button"
                                        id="submitRejectBtn"
                                        class="btn btn-danger"
                                        style="display:none;"
                                        onclick="processReq('reject')">
                                        Confirm Rejection
                                    </button>

                                    <button type="button"
                                        id="submitApproveBtn"
                                        class="btn btn-success"
                                        style="display:none;"
                                        onclick="processReq('approve')">
                                        Confirm Approval
                                    </button>
                                </div>
                            </div>
                        </form>
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

            function hideButton(id) {
                let el = document.getElementById(id);

                if (el) {
                    el.style.display = 'none';
                }
            }

            function showButton(id) {
                let el = document.getElementById(id);

                if (el) {
                    el.style.display = 'inline-block';
                }
            }

            function enableModify() {

                document.querySelectorAll('.req-input').forEach(el => {
                    el.style.display = 'block';
                });

                let remarks = document.getElementById('remarksContainer');
                if (remarks) {
                    remarks.style.display = 'block';
                }

                hideButton('modifyBtn');
                hideButton('rejectBtn');
                hideButton('approveBtn');

                showButton('submitModifyBtn');
            }

            function prepareAction(action) {

                let remarks = document.getElementById('remarksContainer');

                if (remarks) {
                    remarks.style.display = 'block';
                }

                hideButton('modifyBtn');
                hideButton('rejectBtn');
                hideButton('approveBtn');

                if (action === 'reject') {
                    showButton('submitRejectBtn');
                }

                if (action === 'requester_reject') {
                    showButton('submitRejectBtn');
                }
            }

            function prepareApprove(isGodown) {
                if (isGodown !== 1) {
                    document.querySelectorAll('.barcode-select').forEach(el => el.style.display = 'block');
                }

                hideButton('modifyBtn');
                hideButton('rejectBtn');
                hideButton('approveBtn');

                showButton('submitApproveBtn');
            }

            async function processReq(action) {
                const remarks = document.getElementById('remarks').value.trim();
                if((action === 'reject' || action === 'modify') && remarks === '') {
                    toastr.error('Remarks are mandatory for this action.');
                    return;
                }

                if(!confirm(`Confirm ${action.toUpperCase()}?`)) return;

                const form = document.getElementById('processForm');
                const formData = new FormData(form);
                formData.append('action', action);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch(`{{ route('requisition.process', $encrypted_id) }}`, {
                        method: 'POST', body: formData
                    });
                    const data = await res.json();
                    
                    if(res.ok && data.status === 'success') {
                        toastr.success(data.message);
                        setTimeout(() => window.location.replace("{{ route('requisition.list') }}"), 1500);
                    } else { toastr.error(data.message); }
                } catch (e) { toastr.error('System Error.'); }
            }
        </script>
    @endpush
@endsection