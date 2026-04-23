<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Print Retail Barcodes</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        /* Global Reset */
        * { box-sizing: border-box; }

        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            background: #f1f5f9; 
            margin: 0; 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            align-items: center;
        }
        
        /* Print Controls Container */
        .print-controls { 
            background: white; 
            padding: 24px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1); 
            margin-bottom: 30px; 
            text-align: center; 
            width: 100%; 
            max-width: 600px;
        }

        .print-controls h2 { margin-top: 0; color: #0f172a; font-size: 20px; }
        .print-controls p { color: #64748b; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }

        .print-btn { 
            padding: 14px 24px; 
            font-size: 16px; 
            font-weight: bold; 
            background: #0ea5e9; 
            color: #fff; 
            cursor: pointer; 
            border: none; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
            transition: background 0.2s, transform 0.1s;
        }
        .print-btn:hover { background: #0284c7; }
        .print-btn:active { transform: scale(0.98); }

        /* Barcode Grid */
        .barcode-grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            justify-content: center; 
            width: 100%;
            max-width: 800px;
        }
        
        /* 50mm x 35mm standard retail sticker approximation */
        .barcode-label { 
            background: white; 
            border: 1px dashed #94a3b8; 
            border-radius: 6px; 
            width: 50mm; 
            height: 35mm; 
            padding: 4mm;
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            align-items: center;
            page-break-inside: avoid; 
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .b-header { display: flex; justify-content: space-between; width: 100%; align-items: flex-start; margin-bottom: 2px;}
        .b-name { font-weight: 800; font-size: 11px; max-width: 65%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-transform: uppercase; color: #0f172a;}
        .b-mrp { font-size: 12px; font-weight: 900; color: #0f172a;}
        .b-mrp span { font-size: 8px; color: #64748b;}
        
        /* FIX: Ensure JSBarcode SVG doesn't burst out of the label on long barcode numbers */
        .barcode-svg { width: 100%; max-width: 100%; height: 20mm; object-fit: contain; }

        /* =========================================
           📱 MOBILE RESPONSIVE FIXES
           ========================================= */
        @media (max-width: 600px) {
            body { padding: 12px; }
            .print-controls { padding: 20px 16px; }
            .print-btn { width: 100%; font-size: 15px; } /* Full width button for thumbs */
            .barcode-grid { gap: 10px; }
            /* On mobile screen, scale the label to fit if the screen is tiny, but keep print size exact */
            .barcode-label { max-width: 100%; } 
        }

        /* =========================================
           🖨️ ACTUAL PRINTER CSS
           ========================================= */
        @media print {
            body { background: white; padding: 0; margin: 0; display: block;}
            .print-controls { display: none !important; }
            .barcode-grid { gap: 2mm; max-width: none; justify-content: flex-start; display: block; }
            /* Float left helps thermal printers stack the roll sequentially */
            .barcode-label { border: none; outline: 1px dashed #ccc; box-shadow: none; float: left; margin: 1mm; }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <h2>Retail Barcodes Ready</h2>
        <p>Please ensure your thermal printer is selected and your paper size is set to <b>50mm x 35mm</b> before printing.</p>
        <button class="print-btn" onclick="window.print()">🖨️ Print All Barcodes</button>
    </div>

    <div class="barcode-grid" id="barcodeContainer"></div>

    <script>
        const barcodesData = JSON.parse(localStorage.getItem('print_barcodes'));
        const container = document.getElementById('barcodeContainer');

        if(barcodesData && barcodesData.length > 0) {
            // LOOP 1: Go through each unique product transferred
            barcodesData.forEach(item => {
                let qty = parseInt(item.quantity);
                
                // LOOP 2: Print X identical stickers for that product!
                for(let i = 0; i < qty; i++) {
                    let svgId = 'barcode_' + item.barcode + '_' + i;
                    
                    container.innerHTML += `
                        <div class="barcode-label">
                            <div class="b-header">
                                <div class="b-name">${item.name}</div>
                                <div class="b-mrp"><span>MRP </span>₹${item.mrp}</div>
                            </div>
                            <svg class="barcode-svg" id="${svgId}"></svg>
                        </div>
                    `;
                }
            });

            // After HTML is rendered, tell JsBarcode to draw the lines
            barcodesData.forEach(item => {
                let qty = parseInt(item.quantity);
                for(let i = 0; i < qty; i++) {
                    JsBarcode("#barcode_" + item.barcode + '_' + i, item.barcode, {
                        format: "CODE128", // Industry standard
                        lineColor: "#000",
                        width: 1.5,
                        height: 40,
                        displayValue: true,
                        fontSize: 12,
                        margin: 0
                    });
                }
            });
            
            // Auto open print dialog slightly after drawing
            setTimeout(() => { window.print(); }, 800);
        } else {
            container.innerHTML = "<h3 style='color:#64748b;'>No barcodes found to print.</h3>";
        }
    </script>
</body>
</html>