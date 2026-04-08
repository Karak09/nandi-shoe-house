@extends('Offline.layouts.app')

@section('title', 'Super Admin Command Center')
@section('page_title', 'Executive Overview')

@section('content')

<div class="time-filters" style="margin: -24px -32px 24px -32px;">
    <span class="filter-label">Viewing Data For:</span>
    <button class="time-btn">This Day</button>
    <button class="time-btn">Yesterday</button>
    <button class="time-btn active">This Month</button>
    <button class="time-btn">Last Month</button>
    <button class="time-btn">Last 6 Months</button>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Gross Revenue (Sales)</span>
            <div class="kpi-icon" style="color: var(--accent); background: #e0e7ff;">₹</div>
        </div>
        <div class="kpi-value">₹ 24,58,200</div>
        <div class="kpi-trend"><span class="trend-badge" style="background: #d1fae5; color: #10b981;">↑ 12.5%</span> vs last period</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Purchases (Inward)</span>
            <div class="kpi-icon" style="color: var(--text-main); background: #f4f4f5;">📦</div>
        </div>
        <div class="kpi-value">₹ 16,10,450</div>
        <div class="kpi-trend"><span class="trend-badge" style="background: #fee2e2; color: #ef4444;">↓ 4.2%</span> vs last period</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Est. Stock Valuation</span>
            <div class="kpi-icon" style="color: #f59e0b; background: #fef3c7;">📊</div>
        </div>
        <div class="kpi-value">₹ 42,90,000</div>
        <div class="kpi-trend"><span class="trend-badge" style="background: #d1fae5; color: #10b981;">↑ 2.1%</span> across 5 Godowns</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Orders Processed</span>
            <div class="kpi-icon" style="color: #10b981; background: #d1fae5;">✓</div>
        </div>
        <div class="kpi-value">3,492</div>
        <div class="kpi-trend"><span style="color: var(--text-main); font-weight: 600; margin-right:4px;">18</span> Pending Requisitions</div>
    </div>
</div>

<div class="b-grid">
    <div class="chart-card">
        <div style="font-size: 16px; font-weight: 700; margin-bottom: 24px;">Revenue vs. Expenditure Flow</div>
        <div style="height: 240px; border-bottom: 1px solid var(--border-strong); display: flex; align-items: flex-end; justify-content: space-around; padding-bottom: 8px;">
             <div style="width:10%; height: 50%; background: var(--accent); border-radius: 4px 4px 0 0;"></div>
             <div style="width:10%; height: 70%; background: var(--accent); border-radius: 4px 4px 0 0;"></div>
             <div style="width:10%; height: 40%; background: var(--accent); border-radius: 4px 4px 0 0;"></div>
             <div style="width:10%; height: 90%; background: var(--accent); border-radius: 4px 4px 0 0;"></div>
        </div>
    </div>

    <div class="channel-card">
        <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Sales by Channel</div>
        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">Breakdown of gross revenue</div>
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 800; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px dashed var(--border-light);">₹ 24,58,200</div>
        
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <div style="display:flex; justify-content: space-between; font-size: 13px; font-weight:600; margin-bottom: 8px;">
                    <span>Offline Retail</span>
                    <span style="font-family: 'JetBrains Mono';">₹ 14.25L</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--bg-base); border-radius: 10px;"><div style="width: 58%; height: 100%; background: #3b82f6; border-radius: 10px;"></div></div>
            </div>
            <div>
                <div style="display:flex; justify-content: space-between; font-size: 13px; font-weight:600; margin-bottom: 8px;">
                    <span>Own Website</span>
                    <span style="font-family: 'JetBrains Mono';">₹ 6.40L</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--bg-base); border-radius: 10px;"><div style="width: 26%; height: 100%; background: #10b981; border-radius: 10px;"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="table-card" style="padding: 0; overflow:hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-light); background: #fafafa; font-weight: 700;">Recent High-Value Transactions</div>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr>
                <th style="padding: 12px 24px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border-strong);">Transaction ID</th>
                <th style="padding: 12px 24px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border-strong);">Type</th>
                <th style="padding: 12px 24px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border-strong);">Origin</th>
                <th style="padding: 12px 24px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border-strong); text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 16px 24px; font-family: 'JetBrains Mono'; border-bottom: 1px solid var(--border-light);">CHL-2026-9811</td>
                <td style="padding: 16px 24px; border-bottom: 1px solid var(--border-light);"><span style="padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #eef2ff; color: #4338ca;">Purchase (IN)</span></td>
                <td style="padding: 16px 24px; font-weight: 600; border-bottom: 1px solid var(--border-light);">Nike India Pvt Ltd</td>
                <td style="padding: 16px 24px; font-family: 'JetBrains Mono'; border-bottom: 1px solid var(--border-light); text-align: right;">₹ 4,50,000.00</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection