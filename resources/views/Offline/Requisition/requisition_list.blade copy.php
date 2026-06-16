@extends('Offline.layouts.app')
@section('title', 'Requisition List')
@section('page_title', 'Requisition List')

@section('content')

    <div class="container-fluid mt-4">
        <table class="table table-bordered table-striped datatable" cellpadding="10" cellspacing="0">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Requisition ID</th>
                    <th>Req Store</th>
                    <th>Send Store</th>
                    <th>Status</th>
                    <th>Total Items</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requisitions as $req)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $req->req_id }}</td>
                    <td>{{ $req->req_store_id == 1 ? 'Godown' : 'Store '.$req->req_store_id }}</td>
                    <td>{{ $req->send_store_id == 1 ? 'Godown' : 'Store '.$req->send_store_id }}</td>
                    <td>
                        @if($req->status == 1) <span class="badge badge-success">Confirmed</span>
                        @elseif($req->status == 2) <span class="badge badge-warning">Modified</span>
                        @elseif($req->status == 3) <span class="badge badge-danger">Rejected</span>
                        @else <span class="badge badge-secondary">On-Hold</span> @endif
                    </td>
                    <td>{{ $req->items->count() }}</td>
                    <td>{{ $req->created_at->format('d M, Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewRequisition({{ $req }})">View</button>

                        @php
                            $canEdit = false;
                            
                            // 1. Superadmin sees all
                            if ($role == 1) { 
                                $canEdit = true; 
                            } 
                            // 2. Admin edits Godown
                            elseif ($role == 3 && $req->req_store_id == 1) { 
                                $canEdit = true; 
                            } 
                            // 3. Store Manager edits their store
                            elseif ($role == 6 && ($req->req_store_id == $storeId || $req->send_store_id == $storeId)) { 
                                $canEdit = true; 
                            } 
                            // 4. 3rd Party edits ONLY IF status is Modified (2)
                            elseif ($role == 7 && $req->status == 2) { 
                                $canEdit = true; 
                            }
                        @endphp

                        @if($canEdit && $req->status != 1 && $req->status != 3)
                            <a href="{{ route('requisition.edit', $req->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="viewReqModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Requisition Details: <span id="modalReqId"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><strong>Remarks:</strong> <span id="modalRemarks"></span></p>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>UOM</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsBody">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function viewRequisition(req) {
            document.getElementById('modalReqId').innerText = req.req_id;
            document.getElementById('modalRemarks').innerText = req.remarks || 'No remarks provided.';
            
            let itemsHtml = '';
            
            // Loop through the items and use the Eloquent relations we eagerly loaded
            req.items.forEach(item => {
                // Safely fetch names, fallback to ID or 'N/A' if the relation is missing/deleted
                let productName = item.product ? item.product.name : `Unknown (ID: ${item.product_id})`;
                let uomName = item.unit ? item.unit.name : `Unknown (ID: ${item.uom})`;

                itemsHtml += `<tr>
                    <td>${productName}</td>
                    <td>${item.quantity}</td>
                    <td>${uomName}</td>
                </tr>`;
            });
            
            document.getElementById('modalItemsBody').innerHTML = itemsHtml;
            $('#viewReqModal').modal('show');
        }
    </script>
    @endpush

@endsection