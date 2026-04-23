@extends('Offline.layouts.app')
@section('title', 'Product Stock History - Shoe ERP')

@section('content')
<div class="workspace">
    
    <a href="{{ route('store_stock.total', request()->segment(3)) }}" class="btn-back">
        ← Back to Total Stock
    </a>

    <div class="top-card">
        <div class="hist-header">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" class="hist-img" alt="Product">
            @else
                <div class="hist-img" style="display:flex; align-items:center; justify-content:center; color:#999; font-size:11px;">No Image</div>
            @endif
            
            <div class="prod-details">
                @php $firstItem = $details->first(); @endphp
                <h2 class="prod-name">
                    {{ $firstItem?->product?->name ?? 'Unknown Product' }} 
                    @if($firstItem && !empty($firstItem->product->ben_name))
                        <span class="bengali-name">({{ $firstItem->product->ben_name }})</span>
                    @endif
                </h2>
                <div style="color: var(--text-muted); font-size: 13px; margin-top:2px;">
                    Store: <b>{{ $store->store_name ?? 'Unknown Store' }}</b>
                </div>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box in">
                <div class="stat-val">{{ number_format($totalIn, 0) }}</div>
                <div class="stat-lbl">Total In</div>
            </div>
            <div class="stat-box out">
                <div class="stat-val">{{ number_format($totalOut, 0) }}</div>
                <div class="stat-lbl">Total Out</div>
            </div>
            <div class="stat-box avail">
                <div class="stat-val">{{ number_format($available, 0) }}</div>
                <div class="stat-lbl">Available Stock</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Details</th>
                        <th>Bill Date</th>
                        <th>MRP</th>
                        <th>Sale Price</th>
                        <th>Unit</th>
                        <th style="background:#f8fafc;">IN</th>
                        <th style="background:#fcf8f8;">OUT</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $row->product->name ?? 'N/A' }}</div>
                            <div style="font-size:11px; color:#71717a; margin-top:2px;">{{ is_array($row->barcode_no) ? implode(', ', $row->barcode_no) : ($row->barcode_no ?? '-') }}</div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</td>
                        <td>₹{{ number_format($row->mrp, 2) }}</td>
                        <td>₹{{ number_format($row->unit_price, 2) }}</td>
                        <td>{{ $row->uomRelation->keyword ?? 'Unit' }}</td>
                        
                        <td style="background:#f8fafc;">
                            @if($row->in_qty > 0)
                                <span class="badge-in">+{{ number_format($row->in_qty, 0) }}</span>
                            @else
                                <span style="color:#a1a1aa;">0</span>
                            @endif
                        </td>

                        <td style="background:#fcf8f8;">
                            @if($row->out_qty > 0)
                                <span class="badge-out">-{{ number_format($row->out_qty, 0) }}</span>
                            @else
                                <span style="color:#a1a1aa;">0</span>
                            @endif
                        </td>

                        <td style="font-weight:700; font-size:15px;">{{ number_format($row->running_stock, 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:#71717a;">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection