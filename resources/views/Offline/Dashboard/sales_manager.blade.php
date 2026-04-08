@extends('Offline.layouts.app')
@section('title', 'Sales Manager Dashboard')
@section('page_title', 'Sales & Revenue Dashboard')

@push('styles')
    @endpush

@section('content')
<div class="kpi-grid">
    <div class="kpi-card revenue">
        <div class="kpi-header">
            <span class="kpi-title">Gross Store Revenue</span>
        </div>
        <div class="kpi-value">₹ {{ number_format($data['gross_revenue']) }}</div>
    </div>
    
    <div class="kpi-card volume">
        <div class="kpi-header">
            <span class="kpi-title">Total Units Sold</span>
        </div>
        <div class="kpi-value">{{ number_format($data['units_sold']) }}</div>
    </div>
    
    <div class="kpi-card aov">
        <div class="kpi-header">
            <span class="kpi-title">Avg Order Value</span>
        </div>
        <div class="kpi-value">₹ {{ number_format($data['avg_order_value']) }}</div>
    </div>
</div>

<div class="middle-grid">
    <div class="card">
        <div class="card-title">Top Performing Products</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($data['top_products'] as $prod)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <div class="p-img"></div>
                                <div><div class="p-name">{{ $prod['name'] }}</div><div class="p-sku">{{ $prod['sku'] }}</div></div>
                            </div>
                        </td>
                        <td class="mono" style="text-align: right;">{{ number_format($prod['sold']) }}</td>
                        <td class="mono" style="text-align: right; color: var(--accent);">₹ {{ number_format($prod['revenue']) }}</td>
                        <td><span class="stock-badge" style="background: {{ $prod['bg'] }}; color: {{ $prod['color'] }};">{{ $prod['stock_status'] }} ({{ $prod['stock'] }})</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection