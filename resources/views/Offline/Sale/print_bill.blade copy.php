<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Invoice - {{ optional($transfer->billPayment)->bill_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; font-size: 13px; background: #f8fafc; }
        .no-print { background: #fff; padding: 15px 40px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .btn { padding: 10px 20px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 6px; }
        .btn-print { background: #4f46e5; color: white; }
        .document-wrapper { margin: 0 auto; padding: 40px; max-width: 800px; background: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); position: relative; overflow: hidden; min-height: 1050px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-logo { width: 65px; height: 65px; background-color: #0f172a; color: #fff; font-size: 26px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
        .details-box { width: 100%; padding: 12px; border: 1px solid #ccc; }
        .details-box p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .summary-container { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .summary { width: 350px; border: 1px solid #000; }
        .summary th, .summary td { border: none; padding: 6px 10px; }
        .summary .total-row { font-weight: bold; border-top: 2px solid #000; font-size: 15px; }
        @media print {
            @page { size: A4; margin: 0; } 
            body { background: white; }
            .no-print { display: none !important; }
            .document-wrapper { box-shadow: none; max-width: 100%; padding: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <div style="font-size: 16px; font-weight: bold;">Invoice: {{ optional($transfer->billPayment)->bill_no }}</div>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Invoice</button>
    </div>

    <div class="document-wrapper">
        <div class="header">
            <div class="header-left">
                <div class="header-logo">NSH</div>
                <div>
                    <h1 style="margin: 0; text-transform: uppercase;">Nandi Shoe House</h1>
                    <p style="margin: 4px 0 0 0;">{{ optional($transfer->store)->address ?? 'Kolkata, WB' }}</p>
                </div>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0 0 8px 0; text-transform: uppercase;">Tax Invoice</h2>
                <div>Bill No: {{ optional($transfer->billPayment)->bill_no }}</div>
                <div>Date: {{ \Carbon\Carbon::parse($transfer->created_at)->format('d-M-Y h:i A') }}</div>
            </div>
        </div>

        <div class="details">
            <div class="details-box">
                <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Billed To:</strong></p>
                <p><strong>Name:</strong> {{ optional($transfer->billPayment)->cus_name }}</p>
                <p><strong>Mobile:</strong> {{ optional($transfer->billPayment)->phone }}</p>
                <p><strong>Age:</strong> {{ optional($transfer->billPayment)->age }}</p>
            </div>
            <div class="details-box">
                <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Payment Details:</strong></p>
                @php
                    $mode = optional($transfer->billPayment)->payment_mode;
                    $modeStr = $mode == 1 ? 'Cash' : ($mode == 2 ? 'UPI' : 'Card');
                @endphp
                <p><strong>Method:</strong> {{ $modeStr }}</p>
                @if($mode == 2)
                    <p><strong>Transaction No:</strong> {{ optional($transfer->billPayment)->cash_transfer_status }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">Sl</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->customerBillingItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong><br>
                        <small>Barcode: {{ is_array(json_decode($item->barcode_no)) ? implode(', ', json_decode($item->barcode_no)) : $item->barcode_no }}</small>
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->sale_price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-container">
            <div class="summary">
                <table>
                    <tr><td>Total Quantity</td><td class="text-right">{{ $summary['totalQty'] }}</td></tr>
                    <tr class="total-row"><td>Grand Total</td><td class="text-right">₹ {{ number_format($summary['grandTotal'], 2) }}</td></tr>
                    
                    @if($mode == 1)
                        <tr><td>Amount Received</td><td class="text-right">₹ {{ number_format(optional($transfer->billPayment)->recived_money, 2) }}</td></tr>
                        <tr><td>Change Returned</td><td class="text-right">₹ {{ number_format(optional($transfer->billPayment)->refund_money, 2) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div style="margin-bottom: 50px; font-weight: bold; text-transform: uppercase;">
            Amount in words: {{ $amountInWords }} ONLY.
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 60px;">
            <div style="border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; font-weight: bold;">Customer Signature</div>
            <div style="border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; font-weight: bold;">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>