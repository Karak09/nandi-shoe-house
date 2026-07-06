@extends('Offline.layouts.app')
@section('title', 'Godown Product History - Shoe ERP')
@section('content')

<a href="{{ url()->previous() }}" class="btn-back">
    ← Back to Godown Stock
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
            @php
                $colour = $firstItem?->product?->colourRelation?->colour_name ?? '';
                $size = $firstItem?->product?->pro_size ?? '';
            @endphp
            @if($colour || $size)
                <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap; margin-top:4px;">
                    @if($colour)
                        <span style="background:#ede9fe; color:#6d28d9; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">{{ $colour }}</span>
                    @endif
                    @if($size)
                        <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">{{ $size }}</span>
                    @endif
                </div>
            @endif
            <div style="color: var(--text-muted); font-size: 13px; margin-top:2px; font-weight:600;">
                📍 Main Godown Ledger
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
                    <th>Product Name (Batch)</th>
                    <th>Size</th>
                    <th>Colour</th>
                    <th>Bill Date</th>
                    <th>MRP</th>
                    <th>Sale Price</th>
                    <th>UOM</th>
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
                        <div style="font-weight:600; color:#0f172a;">{{ $row->product->name ?? 'N/A' }}</div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px;">
                            Batch: {{ is_array($row->batch_no) ? implode(', ', $row->batch_no) : ($row->batch_no ?? '-') }}
                        </div>
                    </td>
                    <td>
                        @if($row->product->pro_size ?? null)
                            <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">{{ $row->product->pro_size }}</span>
                        @else
                            <span style="color:#a1a1aa;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($row->product->colourRelation->colour_name ?? null)
                            <span style="background:#ede9fe; color:#6d28d9; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600;">{{ $row->product->colourRelation->colour_name }}</span>
                        @else
                            <span style="color:#a1a1aa;">-</span>
                        @endif
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
                    <td colspan="11" style="text-align:center; padding:40px; color:#71717a;">No transactions found for this product.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection