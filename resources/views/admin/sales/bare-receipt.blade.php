<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        @font-face {
            font-family: 'Hanuman';
            src: url('{{ asset('fonts/Hanuman-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        /* General styles for both view and print */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Hanuman', Arial, sans-serif; /* Apply Hanuman font */
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .receipt-container {
            width: 58mm; /* Custom width */
            font-size: 14px;
            line-height: 1.4;
            background: white;
            padding: 10px; /* Custom padding for screen */
            overflow: hidden;
            margin: 20px; /* Custom margin for screen */
        }
        .receipt-container * {
            max-width: 100%;
        }
        h1 {
            font-size: 18px;
            margin: 0;
            text-align: center;
            font-weight: bold;
        }
        h4 {
            font-size: 12px;
            margin: 0;
            text-align: center;
            font-weight: normal;
        }
        .receipt-header, .receipt-footer {
            text-align: center;
        }
        .receipt-header p, .receipt-footer p {
            margin: 2px 0;
        }
        .receipt-logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .receipt-logo img {
            max-width: 50mm;
            height: auto;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 4px;
            border-bottom: 1px dashed #000;
        }
        .totals {
            margin-top: 10px;
            font-size: 12px;
        }
        .totals strong {
            display: inline-block;
            width: 50%;
        }
        .powered-by {
            margin-top: 10px;
            font-size: 10px;
            text-align: center;
        }

        /* Print-specific styles */
        @media print {
            @page {
                size: 58mm auto; /* Ensure proper width */
                margin: 10mm; /* Custom margin for print */
            }
            body {
                margin: 0;
                padding: 0;
                font-family: 'Hanuman', Arial, sans-serif; /* Apply Hanuman font */
                background: none;
                display: block; /* Ensure proper block layout for printing */
            }
            .receipt-container {
                width: 58mm; /* Set exact width for the receipt */
                margin: 0; /* Ensure no extra margins */
                padding: 0; /* Remove any internal padding */
                border: none;
                box-shadow: none;
                overflow: visible;
                display: block;
                position: relative; /* Reset positioning */
                left: 0; /* Align left */
            }
        }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function() { window.close(); window.opener.location.reload(); }">
    <div class="receipt-container">
        <div class="receipt-logo">
            <img src="{{ asset('assets/img/pharrrlg_forReceipt.png') }}" alt="Company Logo">
        </div>
        <div class="receipt-header">
            <br>
            ផ្ទះលេខ16 ផ្លូវ210 បុរីភ្នំពេញថ្មី ក្រុងតាខ្មៅ<br>
            <p>096 25 15 777</p>
            <hr>
            <p><strong>វិក្កយបត្រ</strong><br>
            កាលបរិច្ឆេទ: {{ \Carbon\Carbon::parse($receipt->date)->format('d-m-Y') }}</p>
            <hr>
        </div>

        <table>
        <thead>
            <tr>
                <th>SL.</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $sale->barCodeData->product_name }}
                        @if(strtolower($sale->barCodeData->category->name) == 'medicine')
                            ({{ $sale->sale_by == 'pill' ? 'គ្រាប់ថ្នាំ' : 'ប្រអប់' }})
                        @endif
                    </td>
                    <td>{{ $sale->quantity }}</td>
                    <td>
                        @if($sale->sale_by == 'pill')
                            ${{ number_format($sale->barCodeData->price_per_pill, 2) }}
                        @else
                            ${{ number_format($sale->barCodeData->price, 2) }}
                        @endif
                    </td>
                    <td>${{ number_format($sale->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Subtotal:</strong> ${{ number_format($receipt->total_amount, 2) }}</p>
        <p><strong>Discount:</strong> 
            @if($receipt->sales->first()->discount_type == 'percentage')
                {{ number_format($receipt->sales->first()->discount_value, 2) }}%
            @elseif($receipt->sales->first()->discount_type == 'fixed-dollar')
                ${{ number_format($receipt->sales->first()->discount_value, 2) }}
            @elseif($receipt->sales->first()->discount_type == 'fixed-riel')
                     ៛{{ number_format($receipt->sales->first()->discount_value) }}
            @endif
        </p>
        <p><strong>សរុប ($):</strong> ${{ number_format($receipt->sales->first()->pay_amount_dollar - $receipt->sales->first()->due_amount_dollar, 2) }}</p>
        <p><strong>សរុប (៛):</strong> ៛{{ number_format(($receipt->sales->first()->pay_amount_dollar - $receipt->sales->first()->due_amount_dollar) * 4100) }}</p>
        <p><strong>លុយអាប់:</strong> 
    ${{ number_format($receipt->sales->first()->due_amount_dollar, 2) }} / 
    {{ number_format($receipt->sales->first()->due_amount_riel) }}៛
</p>


    </div>


        <div class="receipt-footer">
            <hr>
            <p>អរគុណច្រើន!<strong></strong></p>
        </div>
    </div>
</body>
</html>
