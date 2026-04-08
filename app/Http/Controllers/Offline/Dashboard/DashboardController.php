<?php

namespace App\Http\Controllers\Offline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users\User;
// use App\Models\Requisition; // Example models you will need
// use App\Models\Transaction;

class DashboardController extends Controller
{
    public function superadmin()
    {
        // Replace with actual DB queries
        $data = [
            'gross_revenue' => 2458200,
            'revenue_trend' => 12.5,
            'total_purchases' => 1610450,
            'purchase_trend' => -4.2,
            'stock_valuation' => 4290000,
            'orders_processed' => 3492,
            'pending_requisitions' => 18,
            
            // Channels
            'offline_revenue' => 1425000,
            'online_revenue' => 640200,
            'third_party_revenue' => 393000,

            // Lists
            'recent_transactions' => [
                ['id' => 'CHL-2026-9811', 'type' => 'Purchase (IN)', 'origin' => 'Nike India', 'amount' => 450000, 'color' => '#4338ca', 'bg' => '#eef2ff'],
                ['id' => 'TRN-STR-0029', 'type' => 'Transfer', 'origin' => 'Kolkata Store', 'amount' => 185500, 'color' => '#c2410c', 'bg' => '#fff7ed'],
            ]
        ];

        return view('Offline.Dashboard.superadmin', compact('data'));
    }

    public function admin()
    {
        $data = [
            'products_sold' => 12450,
            'products_purchased' => 18200,
            'pending_requisitions' => 12,
            'low_stock_items' => 34,
            
            'store_performance' => [
                ['name' => 'Kolkata City Center', 'sold' => 4200, 'progress' => 85, 'color' => 'var(--accent)'],
                ['name' => 'Pune High Street', 'sold' => 3150, 'progress' => 65, 'color' => '#0ea5e9'],
            ],
            
            'requisitions' => [
                ['id' => 'REQ-2026-9012', 'store' => 'Kolkata City Center', 'items' => 62],
                ['id' => 'REQ-2026-9013', 'store' => 'Pune High Street', 'items' => 15],
            ],
            
            'activities' => [
                ['time' => '10 mins ago', 'action' => 'Godown Transfer', 'target' => 'TRN-8912', 'color' => '#0284c7', 'bg' => '#e0f2fe'],
                ['time' => '1 hour ago', 'action' => 'Price Update', 'target' => 'SKU: NK-AM-270', 'color' => '#475569', 'bg' => '#f1f5f9'],
            ]
        ];

        return view('Offline.Dashboard.admin', compact('data'));
    }

    public function salesManager()
    {
        $data = [
            'gross_revenue' => 1425000,
            'units_sold' => 4200,
            'avg_order_value' => 3392,
            'discounts_applied' => 85400,

            'top_products' => [
                ['name' => 'Nike Air Max 270', 'sku' => 'NK-AM-270', 'sold' => 840, 'revenue' => 3402000, 'stock' => 150, 'stock_status' => 'In Stock', 'bg' => '#dcfce7', 'color' => '#166534'],
                ['name' => 'Puma RS-X3', 'sku' => 'PM-RSX-01', 'sold' => 620, 'revenue' => 1984000, 'stock' => 12, 'stock_status' => 'Low Stock', 'bg' => '#fee2e2', 'color' => '#991b1b'],
            ]
        ];

        return view('Offline.Dashboard.sales_manager', compact('data'));
    }
}