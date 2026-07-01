@extends('Offline.layouts.app')
@section('title', 'Premium Store Stock Dashboard - Shoe ERP')
@section('content')

<div class="kpi-grid">
            <div class="kpi-card" style="border-top-color: #0ea5e9;">
                <div class="kpi-title">Total Unique Products</div>
                <div class="kpi-value">{{ $totalUnique }}</div>
            </div>
            <div class="kpi-card" style="border-top-color: #10b981;">
                <div class="kpi-title">Total Items in Stock</div>
                <div class="kpi-value">{{ number_format($totalItems, 0) }}</div>
            </div>
        </div>

        <div class="matrix-card">
            
            <div class="toolbar">
                <select onchange="window.location.href='{{ url('store-total-stock') }}/' + this.value" id="storeForm" class="store-selector">
                    @foreach($stores as $store)
                        <option value="{{ $store->enc_id }}"
                            {{ isset($storeId) && $storeId == $store->id ? 'selected' : '' }}>
                            {{ $store->store_name }} (STR-00{{ $store->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="table-wrap">
                <div class="table-container">
                    <table class="datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Identification</th>
                                <th>Available Stock (quantity)</th>
                                <th>Last Received</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeStocks as $stock)
                            <tr class="stock-row">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="product-info">
                                        @php
                                            $imgDoc = $productImages[$stock->product_id] ?? null;
                                        @endphp
                                        
                                        @if($imgDoc)
                                            <img src="{{ asset('storage/' . $imgDoc) }}" class="p-img" alt="Product Image">
                                        @else
                                            <div class="p-img" style="display:flex; align-items:center; justify-content:center; font-size:10px; color:#999; text-align:center;">No Img</div>
                                        @endif
                                        
                                        <div>
                                            <div class="p-name">
                                                {{ $stock->product->name ?? 'Unknown' }}
                                                @if(!empty($stock->product->bengali_name))
                                                    <span style="font-weight: 500; color: var(--text-muted);">({{ $stock->product->bengali_name }})</span>
                                                @endif
                                            </div>
                                            <div class="p-sku">SKU: {{ $stock->product->sku ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="qty-wrapper">
                                        <div>
                                            <span class="qty-num" style="color: {{ $stock->quantity > 10 ? 'var(--brand-dark)' : 'var(--stock-low)' }};">{{ number_format($stock->quantity, 2) }}</span>
                                            <span class="qty-uom">{{ $stock->uomRelation->keyword ?? 'Unit' }}</span>
                                        </div>
                                        @if($stock->quantity > 10)
                                            <span class="stock-indicator ind-high">Optimal</span>
                                        @else
                                            <span class="stock-indicator ind-low">Low Stock</span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td>
                                    <div style="font-weight: 500; font-size: 13px;">{{ \Carbon\Carbon::parse($stock->updated_at)->format('d M Y') }}</div>
                                    <div style="font-size: 11px; color: var(--text-muted);">From: Main Godown</div>
                                </td>

                                <td>
                                    <a href="{{ route('store_stock.history', ['enc_store_id' => $stock->enc_store_id, 'enc_product_id' => $stock->enc_product_id]) }}" class="btn-action" style="text-decoration:none;">   
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        View Details
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">No stock available in this store.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection