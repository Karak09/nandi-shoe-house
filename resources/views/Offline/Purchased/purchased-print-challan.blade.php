<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Purchase Challan - {{ $challan->challan_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; color: #000; font-size: 13px; position: relative; background: #f8fafc; }
        .no-print { background: #ffffff; padding: 15px 40px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .btn { padding: 10px 20px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; border-radius: 6px; }
        .btn-print { background: #4f46e5; color: white; box-shadow: 0 2px 4px rgba(79,70,229,0.2); }
        .btn-back { background: #e2e8f0; color: #0f172a; }
        .document-wrapper { margin: 0 auto; padding: 40px; max-width: 800px; background: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); position: relative; overflow: hidden; min-height: 1050px; }
        .watermark { position: absolute; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; font-weight: 900; color: rgba(0, 0, 0, 0.03); z-index: 0; white-space: nowrap; pointer-events: none; letter-spacing: 5px; }
        .wm-1 { top: 20%; }
        .wm-2 { top: 50%; }
        .wm-3 { top: 80%; }
        .content { position: relative; z-index: 1; }
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-logo { width: 65px; height: 65px; background-color: #0f172a; color: #fff; font-size: 26px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; }
        .header-right { text-align: right; }
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 13px; gap: 20px; }
        .details-box { width: 100%; padding: 12px; border: 1px solid #ccc; background: rgba(255, 255, 255, 0.8); }
        .details-box p { margin: 5px 0; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; background: rgba(255, 255, 255, 0.9); min-width: 600px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-container { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .summary { width: 350px; border: 1px solid #000; background: rgba(255, 255, 255, 0.9); }
        .summary table { width: 100%; margin: 0; min-width: auto; }
        .summary th, .summary td { border: none; padding: 6px 10px; font-size: 13px; }
        .summary .highlight-row { font-weight: bold; border-top: 1px dashed #000; }
        .summary .total-row { font-weight: bold; border-top: 2px solid #000; font-size: 15px; }
        .amount-words { margin-bottom: 50px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .sig-line { border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; font-weight: bold; }
        @media (max-width: 768px) {
            .no-print { flex-direction: column; gap: 15px; padding: 15px 20px; text-align: center; }
            .btn { width: 100%; }
            .document-wrapper { padding: 20px; min-height: auto; }
            .header { flex-direction: column; gap: 20px; align-items: flex-start; }
            .header-right { text-align: left; width: 100%; border-top: 1px dashed #ccc; padding-top: 15px; }
            .details { flex-direction: column; gap: 15px; }
            .summary-container { justify-content: center; width: 100%; }
            .summary { width: 100%; }
            .signatures { flex-direction: column; gap: 50px; align-items: center; margin-top: 40px; }
            .sig-line { width: 100%; max-width: 250px; }
            .watermark { font-size: 40px; }
        }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .document-wrapper { box-shadow: none; max-width: 100%; padding: 15mm; margin: 0; min-height: auto; }
            .watermark { position: fixed; color: rgba(0, 0, 0, 0.04) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 80px; }
            .header { flex-direction: row; align-items: flex-start; }
            .header-right { text-align: right; border-top: none; padding-top: 0; }
            .details { flex-direction: row; }
            .summary-container { justify-content: flex-end; }
            .summary { width: 350px; }
            .signatures { flex-direction: row; gap: 0; margin-top: 60px; }
            .sig-line { width: 200px; }
            th { background-color: #f4f4f4 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .header-logo { background-color: #0f172a !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .signatures { page-break-inside: avoid; }
            .table-responsive { overflow: visible; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <div style="font-size: 16px; font-weight: bold;">Challan: {{ $challan->challan_no }}</div>
        <div>
            <button onclick="window.print()" class="btn btn-print">🖨️ Print Challan</button>
        </div>
    </div>

    <div class="document-wrapper">

        <div class="watermark wm-1">NANDI SHOE HOUSE</div>
        <div class="watermark wm-2">NANDI SHOE HOUSE</div>
        <div class="watermark wm-3">NANDI SHOE HOUSE</div>

        <div class="content">
            <div class="header">
                <div class="header-left">
                    <div class="header-logo">NSH</div>
                    <div>
                        <h1 style="margin: 0; text-transform: uppercase; font-size: 24px; font-weight: 900; letter-spacing: 1px;">Nandi Shoe House</h1>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #444;">Central Godown Facility, Sector V, Salt Lake City, Kolkata - 700091</p>
                    </div>
                </div>
                <div class="header-right">
                    <h2 style="margin: 0 0 8px 0; color: #000; text-transform: uppercase; font-size: 20px;">Purchase Challan</h2>
                    <div style="font-size: 13px; font-weight: bold;">Challan No: {{ $challan->challan_no }}</div>
                    <div style="font-size: 12px; margin-top: 4px;">Date: {{ \Carbon\Carbon::parse($challan->challan_date)->format('d-M-Y') }}</div>
                </div>
            </div>

            <div class="details">
                <div class="details-box">
                    <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Vendor Details</strong></p>
                    <p><strong>{{ $challan->vendor->vendor_name ?? 'N/A' }}</strong></p>
                    @if($challan->vendor && $challan->vendor->address)
                        <p><strong>Address:</strong> {{ $challan->vendor->address }}</p>
                    @endif
                    @if($challan->vendor && $challan->vendor->mobile)
                        <p><strong>Mobile:</strong> {{ $challan->vendor->mobile }}</p>
                    @endif
                    @if($challan->vendor && $challan->vendor->gstin)
                        <p><strong>GSTIN:</strong> {{ $challan->vendor->gstin }}</p>
                    @endif
                </div>

                <div class="details-box">
                    <p style="text-decoration: underline; margin-bottom: 10px;"><strong>Purchase Details</strong></p>
                    <p><strong>Challan No:</strong> {{ $challan->challan_no }}</p>
                    <p><strong>Challan Date:</strong> {{ \Carbon\Carbon::parse($challan->challan_date)->format('d-M-Y') }}</p>
                    <p><strong>Received By:</strong> {{ optional($challan->user->details)->f_name ?? 'System' }} {{ optional($challan->user->details)->l_name ?? '' }}</p>
                    @if($challan->command)
                        <p><strong>Remarks:</strong> {{ $challan->command }}</p>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">Sl No.</th>
                            <th>Product Description</th>
                            <th class="text-center" style="width: 50px;">UOM</th>
                            <th class="text-right" style="width: 60px;">Qty</th>
                            <th class="text-right" style="width: 80px;">MRP (₹)</th>
                            <th class="text-right" style="width: 80px;">Unit Price (₹)</th>
                            <th class="text-right" style="width: 80px;">GST %</th>
                            <th class="text-right" style="width: 100px;">Total Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($challan->transactions as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ optional($item->product)->name ?? 'N/A' }}</strong>
                                @if($item->product)
                                    @php
                                        $attrs = [];
                                        if ($item->product->colourRelation) $attrs[] = $item->product->colourRelation->colour_name;
                                        if ($item->product->pro_size) $attrs[] = $item->product->pro_size;
                                    @endphp
                                    @if(count($attrs))
                                        <br><small style="color: #2563eb; font-weight:600;">{{ implode(' | ', $attrs) }}</small>
                                    @endif
                                    @if($item->product->ben_name)
                                        <br><small style="color: #555;">{{ $item->product->ben_name }}</small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">{{ optional($item->uomRelation)->keyword ?? '' }}</td>
                            <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-right">{{ number_format($item->mrp, 2) }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">{{ number_format($item->gst, 2) }}%</td>
                            <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-container">
                <div class="summary">
                    <table>
                        <tr>
                            <td>Total Quantity</td>
                            <td class="text-right">{{ number_format($summary['totalQty'], 2) }} Units</td>
                        </tr>
                        <tr>
                            <td>Sub Total (Base Price)</td>
                            <td class="text-right">₹ {{ number_format($summary['subTotal'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>CGST (Total)</td>
                            <td class="text-right">₹ {{ number_format($summary['totalCGST'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST (Total)</td>
                            <td class="text-right">₹ {{ number_format($summary['totalSGST'], 2) }}</td>
                        </tr>
                        <tr class="highlight-row">
                            <td>Net Amount</td>
                            <td class="text-right">₹ {{ number_format($summary['netAmount'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Round Off</td>
                            <td class="text-right">{{ $summary['roundOff'] >= 0 ? '+' : '' }}₹ {{ number_format($summary['roundOff'], 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Grand Total</td>
                            <td class="text-right">₹ {{ number_format($summary['grandTotal'], 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="amount-words">
                Amount in words: {{ $amountInWords }} ONLY.
            </div>

            <div class="signatures">
                <div class="sig-line">Prepared By<br><small>(Godown Manager)</small></div>
                <div class="sig-line">Verified By<br><small>(Accountant)</small></div>
                <div class="sig-line">Authorized Signatory<br><small>Nandi Shoe House</small></div>
            </div>
        </div>
    </div>
</body>
</html>