<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bill - {{ $bill->bill_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; color: #000; font-size: 13px; background: #f8fafc; }
        
        .no-print { background: #ffffff; padding: 15px 40px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .btn { padding: 10px 20px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 6px; }
        .btn-print { background: #4f46e5; color: white; }
        
        .document-wrapper { margin: 0 auto; padding: 40px; max-width: 800px; background: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); position: relative; overflow: hidden; min-height: 900px; }
        .watermark { position: absolute; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; font-weight: 900; color: rgba(0, 0, 0, 0.03); z-index: 0; white-space: nowrap; pointer-events: none; }
        .wm-1 { top: 30%; } .wm-2 { top: 70%; }
        
        .content { position: relative; z-index: 1; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header-left { display: flex; gap: 15px; }
        .header-logo { width: 65px; height: 65px; background-color: #0f172a; color: #fff; font-size: 26px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        .header-right { text-align: right; }
        
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
        .details-box { width: 100%; padding: 12px; border: 1px solid #ccc; background: rgba(255, 255, 255, 0.8); }
        .details-box p { margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        
        .summary-container { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .summary { width: 350px; border: 1px solid #000; }
        .summary th, .summary td { border: none; padding: 6px 10px; font-size: 13px; }
        .total-row { font-weight: bold; border-top: 2px solid #000; font-size: 15px; }
        
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .sig-line { border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; font-weight: bold; }
        
        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; }
            .no-print { display: none !important; }
            .document-wrapper { box-shadow: none; max-width: 100%; padding: 15mm; }
            .watermark { position: fixed; color: rgba(0, 0, 0, 0.04) !important; -webkit-print-color-adjust: exact; }
            th { background-color: #f4f4f4 !important; -webkit-print-color-adjust: exact; }
            .header-logo { background-color: #0f172a !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <div style="font-size: 16px; font-weight: bold;">Bill No: {{ $bill->bill_no }}</div>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Bill</button>
    </div>

    <div class="document-wrapper">
        <div class="watermark wm-1">NANDI SHOE HOUSE</div>
        <div class="watermark wm-2">NANDI SHOE HOUSE</div>
        
        <div class="content">
            <div class="header">
                <div class="header-left">
                    <div class="header-logo">NSH</div>
                    <div>
                        <h1 style="margin: 0; text-transform: uppercase; font-size: 24px;">Nandi Shoe House</h1>
                        <p style="margin: 4px 0 0 0; font-size: 12px;">Store Retail Invoice</p>
                    </div>
                </div>
                <div class="header-right">
                    <h2 style="margin: 0 0 8px 0;">Retail Bill</h2>
                    <div style="font-size: 13px; font-weight: bold;">Bill No: {{ $bill->bill_no }}</div>
                    <div style="font-size: 12px; margin-top: 4px;">Date: {{ \Carbon\Carbon::parse($bill->created_at)->format('d-M-Y h:i A') }}</div>
                </div>
            </div>

            <div class="details">
                <div class="details-box">
                    <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Billed By (Store)</strong></p>
                    <p><strong>{{ optional($challan->storeStockDetails->first()->store)->store_name ?? 'Retail Store' }}</strong></p>
                    <p><strong>Cashier:</strong> {{ optional($challan->user)->name ?? 'System' }}</p>
                </div>
                
                <div class="details-box">
                    <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Billed To (Customer)</strong></p>
                    <p><strong>Name:</strong> {{ $bill->cus_name }}</p>
                    <p><strong>Phone:</strong> {{ $bill->phone }}</p>
                    <p><strong>Age:</strong> {{ $bill->age }} yrs</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">Sl</th>
                        <th>Product & Barcode</th>
                        <th class="text-right">MRP</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totQty = 0; @endphp
                    @foreach($challan->customerBillingItems as $item)
                    @php $totQty += $item->quantity; @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>
                            <small style="color:#555;">Code: {{ str_replace(['[', ']', '"'], '', $item->barcode_no) }}</small>
                        </td>
                        <td class="text-right">{{ number_format($item->mrp_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->sale_price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-container">
                <div class="summary">
                    <table>
                        <tr>
                            <td>Total Quantity</td>
                            <td class="text-right">{{ $totQty }} Units</td>
                        </tr>
                        <tr>
                            <td>Payment Mode</td>
                            <td class="text-right">
                                @if($bill->payment_mode == 1) Cash
                                @elseif($bill->payment_mode == 2) UPI (Txn: {{ $bill->transaction_no }})
                                @elseif($bill->payment_mode == 3) Card (Txn: {{ $bill->transaction_no }})
                                @endif
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td>Grand Total</td>
                            <td class="text-right">₹ {{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        
                        <!-- Show Cash Rec/Refund only if it was Cash -->
                        @if($bill->payment_mode == 1)
                        <tr>
                            <td>Amount Received</td>
                            <td class="text-right">₹ {{ number_format($bill->recived_money, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Refund Amount</td>
                            <td class="text-right">₹ {{ number_format($bill->refund_money, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="signatures">
                <div class="sig-line">Customer Signature</div>
                <div class="sig-line">Authorized Signatory<br><small>Nandi Shoe House</small></div>
            </div>
        </div>
    </div>
</body>
</html>