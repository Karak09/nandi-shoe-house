@extends('Offline.layouts.app')
@section('title', 'Godown Stock - Shoe ERP')
@section('content')


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

        <div class="data-card" style="background:#fff; border-radius:8px; border:1px solid #e2e8f0; overflow:hidden;">
            <div class="table-container" style="overflow-x:auto;">
                <table class="datatable" style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <tr>
                            <th style="padding:12px 16px; font-size:11px; text-transform:uppercase; color:#64748b;">ID</th>
                            <th style="padding:12px 16px; font-size:11px; text-transform:uppercase; color:#64748b;">Product Details</th>
                            <th style="padding:12px 16px; font-size:11px; text-transform:uppercase; color:#64748b;">Total Stock</th>
                            <th style="padding:12px 16px; font-size:11px; text-transform:uppercase; color:#64748b;">Status</th>
                            <th style="padding:12px 16px; font-size:11px; text-transform:uppercase; color:#64748b; text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $s)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px;">{{ $loop->iteration }}</td>
                            <td style="padding:12px 16px;">
                                <div style="font-weight:600; color:#0f172a;">{{ $s->product->name ?? 'N/A' }}</div>
                                <div style="font-size:11px; color:#64748b; font-family:monospace;">SKU: {{ $s->product->sku ?? '-' }}</div>
                            </td>
                            <td style="padding:12px 16px;">
                                <div style="font-weight:700; font-size:15px; color: {{ $s->quantity > 20 ? '#0f172a' : '#ef4444' }};">
                                    {{ number_format($s->quantity, 2) }} <span style="font-size:11px; font-weight:600; color:#64748b;">{{ $s->uomRelation->keyword ?? '' }}</span>
                                </div>
                            </td>
                            <td style="padding:12px 16px;">
                                @if($s->quantity > 10)
                                    <span style="background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">In Stock</span>
                                @else
                                    <span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">Low Stock</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px; text-align:right;">
                                <a href="{{ route('godown_stock.history', $s->enc_product_id) }}" class="btn-action">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endsection