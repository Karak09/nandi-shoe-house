@extends('Offline.layouts.app')
@section('title', 'Admin Operations Dashboard')
@section('page_title', 'Operations Command Center')

@push('styles')
    @endpush

@section('content')
<div class="kpi-grid">
    <div class="kpi-card sales">
        <div class="kpi-header">
            <span class="kpi-title">Products Sold (Outward)</span><span style="font-size: 18px;">🛒</span>
        </div>
        <div class="kpi-value"><span style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Pairs</span></div>
    </div>
    
    <div class="kpi-card purchases">
        <div class="kpi-header">
            <span class="kpi-title">Products Purchased</span><span style="font-size: 18px;">📦</span>
        </div>
        <div class="kpi-value"><span style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Units</span></div>
    </div>

    <div class="kpi-card pending">
        <div class="kpi-header">
            <span class="kpi-title">Pending Requisitions</span><span style="font-size: 18px;">⏳</span>
        </div>
        <div class="kpi-value" style="color: var(--stat-pending);"><span style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Requests</span></div>
    </div>

    <div class="kpi-card alerts">
        <div class="kpi-header">
            <span class="kpi-title">Low Stock SKUs</span><span style="font-size: 18px;">⚠️</span>
        </div>
        <div class="kpi-value" style="color: var(--stat-alert);"><span style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Items</span></div>
    </div>
</div>

<div class="middle-grid">
    <div class="card">
        <div class="card-title">Active Store Sales Volume</div>
        <div class="perf-list">
            <div class="perf-item">
                <div class="perf-header">
                    <span></span>
                    <span class="perf-val"> Pairs Sold</span>
                </div>
                <div class="progress-bg"><div class="progress-fill" style="width: %; background: ;"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="table-grid">
    <div class="card" style="padding: 0;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-light); font-weight: 700; background: #fffbeb;">⚠️ Requisitions Awaiting Approval</div>
        <table>
            <thead><tr><th>Req ID</th><th>Store</th><th>Items</th><th>Action</th></tr></thead>
            <tbody>
                <tr>
                    <td class="mono"></td>
                    <td></td>
                    <td>Units</td>
                    <td><button class="btn-action">Review</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding: 0;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-light); font-weight: 700;">Recent Activity</div>
        <table>
            <thead><tr><th>Time</th><th>Action Type</th><th>Target ID</th></tr></thead>
            <tbody>
                <tr>
                    <td style="color: var(--text-muted); font-size: 12px;"></td>
                    <td><span class="status-pill" style="background: ; color: ;"></span></td>
                    <td class="mono"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection