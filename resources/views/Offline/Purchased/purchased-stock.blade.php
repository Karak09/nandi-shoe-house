@extends('Offline.layouts.app')
@section('title', 'Godown Stock - Shoe ERP')
@section('content')

<div class="main-content">
    <div class="workspace">
        <div class="kpi-grid">
            <div class="kpi-card" style="border-top-color: #0ea5e9;">
                <div class="kpi-title">Total Unique Products</div>
                <div class="kpi-value">{{ $stocks->count() }}</div>
            </div>
            <div class="kpi-card" style="border-top-color: #10b981;">
                <div class="kpi-title">Total Items in Godown</div>
                <div class="kpi-value">{{ number_format($stocks->sum('quantity'), 0) }}</div>
            </div>
        </div>

        <div class="data-card">
            <div class="table-container">
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Details</th>
                            <th>Total Stock</th>
                            <th>Packaging Setup</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="product-name">{{ $s->product->name ?? 'N/A' }}</div>
                                <div class="product-sku">{{ $s->product->sku ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="stock-val" style="color: {{ $s->quantity > 20 ? '#0f172a' : '#ef4444' }};">
                                    {{ number_format($s->quantity, 2) }} <span class="stock-uom">{{ $s->uomRelation->keyword ?? '' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($s->is_packet)
                                    <span style="font-size:11px; font-weight:700; color:#0ea5e9;">📦 PACKET ITEM</span>
                                @else
                                    <span style="font-size:11px; font-weight:600; color:#64748b; background:#f1f5f9; padding:2px 6px; border-radius:4px;">LOOSE ITEM</span>
                                @endif
                            </td>
                            <td>
                                @if($s->quantity > 10)
                                    <span class="status-badge status-ok">In Stock</span>
                                @else
                                    <span class="status-badge status-low">Low Stock</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection