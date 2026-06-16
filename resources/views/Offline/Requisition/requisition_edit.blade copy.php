@extends('Offline.layouts.app')
@section('title', 'Process Requisition')
@section('page_title', 'Process Requisition')

@section('content')

    <div class="container-fluid mt-4">
        
        <div class="alert alert-secondary d-flex justify-content-between">
            <strong>Req ID:</strong> {{ $requisition->req_id }}
            <strong>Status:</strong> 
            @if($requisition->status == 2) <span class="text-warning">MODIFIED (Needs Review)</span> 
            @elseif($requisition->status == 4) <span class="text-info">NEW (On-Hold)</span> 
            @endif
        </div>

        <div class="row">
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Current Availability at Destination</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Available Stock Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $item)
                                <tr>
                                    <td><strong>{{ $item->product_id }}</strong></td>
                                    <td>
                                        @if(isset($stockData[$item->product_id]) && count($stockData[$item->product_id]) > 0)
                                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                                @foreach($stockData[$item->product_id] as $stockRow)
                                                    <li class="border-bottom pb-1 mb-1">
                                                        <strong>Qty:</strong> <span class="text-success">{{ $stockRow->quantity }}</span> | 
                                                        <strong>UOM:</strong> {{ $stockRow->uom }} <br>
                                                        
                                                        @if($stockRow->batch_no)
                                                            <strong>Batch:</strong> {{ implode(', ', json_decode($stockRow->batch_no, true) ?? [$stockRow->batch_no]) }} <br>
                                                        @endif
                                                        
                                                        @if(isset($stockRow->barcode_no) && $stockRow->barcode_no)
                                                            <strong>Barcode:</strong> {{ implode(', ', json_decode($stockRow->barcode_no, true) ?? [$stockRow->barcode_no]) }}
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-danger">Out of Stock</span>
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
                            <table class="table table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Requested Qty</th>
                                        <th>UOM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisition->items as $item)
                                    <tr>
                                        <td class="align-middle"><strong>{{ $item->product_id }}</strong></td>
                                        <td>
                                            <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control req-input" value="{{ $item->quantity }}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $item->id }}][uom]" class="form-control req-input" value="{{ $item->uom }}" readonly>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="p-3 bg-light border-top text-right">
                                @if($role != 7 || ($role == 7 && $requisition->status == 2))
                                    <button type="button" class="btn btn-warning mr-2" id="modifyBtn" onclick="enableModify()">Modify Quantities</button>
                                    
                                    <button type="button" class="btn btn-danger mr-2" onclick="processReq('reject')">Reject</button>
                                    <button type="button" class="btn btn-success" onclick="processReq('confirm')">Approve & Deduct Stock</button>
                                    
                                    <button type="button" id="submitModifyBtn" class="btn btn-info d-none" onclick="processReq('modify')">Submit Modification</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function enableModify() {
                document.querySelectorAll('.req-input').forEach(el => el.removeAttribute('readonly'));
                document.getElementById('submitModifyBtn').classList.remove('d-none');
                document.getElementById('modifyBtn').classList.add('d-none'); // Hide the initial modify button
                toastr.info('Inputs unlocked. Modify quantities and click Submit Modification.');
            }

            async function processReq(action) {
                if(!confirm(`Are you sure you want to ${action.toUpperCase()} this requisition?`)) return;

                const form = document.getElementById('processForm');
                const formData = new FormData(form);
                formData.append('action', action);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch(`{{ url('requisition/process/'.$requisition->id) }}`, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    
                    if(res.ok && data.status === 'success') {
                        toastr.success(data.message);
                        setTimeout(() => window.location.href = "{{ route('requisition.list') }}", 1500);
                    } else {
                        toastr.error(data.message);
                    }
                } catch (e) {
                    toastr.error('System Error processing request.');
                }
            }
        </script>
    @endpush

@endsection