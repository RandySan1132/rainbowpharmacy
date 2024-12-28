@extends('admin.layouts.app')
@section('content')
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
        width: 65mm; /* Scale the receipt a little bigger */
        font-size: 13px;
        line-height: 1.4;
        background: white;
        padding: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #ddd;
        overflow: hidden;
    }
    .receipt-container * {
        max-width: 100%;
    }
    .no-print {
        margin-top: 10px;
        text-align: center;
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
            size: 65mm auto; /* Ensure proper width */
            margin: 0; /* Remove default margins */
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Hanuman', Arial, sans-serif; /* Apply Hanuman font */
            background: none;
            display: block; /* Ensure proper block layout for printing */
        }
        .receipt-container {
            width: 65mm; /* Set exact width for the receipt */
            margin: 0; /* Ensure no extra margins */
            padding: 0; /* Remove any internal padding */
            border: none;
            box-shadow: none;
            overflow: visible;
            display: block;
            position: relative; /* Reset positioning */
            left: 0; /* Align left */
        }

        .no-print {
            display: none !important;
        }
        /* Hide all other elements when printing */
        body * {
            visibility: hidden;
        }
        .receipt-container, .receipt-container * {
            visibility: visible;
        }
        .receipt-container {
            position: absolute;
            left: 0;
            top: 0;
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

    }
</style>

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
        <p>អរគុណច្រើន! <strong></strong></p>
    </div>
</div>

<div class="no-print" style="display: flex; justify-content: left; gap: 10px; width: 100%; padding: 10px;">
        <button onclick="window.location.href='{{ route('sales.index') }}'" class="btn btn-secondary" style="background-color:rgb(0, 136, 255);">
        Invoice List
    </button>
    <button onclick="window.open('{{ route('admin.sales.bareReceipt', $receipt->id) }}', '_blank')" class="btn btn-primary">
        Print
    </button>
    <button class="btn btn-danger delete-btn" data-id="{{ $receipt->invoice_id }}" data-route="{{ route('sales.destroy', $receipt->invoice_id) }}">
        Delete
    </button>
</div>

<script>
document.querySelector('.delete-btn').addEventListener('click', function() {
    let invoiceId = this.getAttribute('data-id');
    let deleteRoute = this.getAttribute('data-route');

    console.log('Attempting to delete sale with invoice ID:', invoiceId); // Add this line

    if (confirm('Are you sure you want to delete this sale?')) {
        fetch(deleteRoute, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Delete response:', data); // Add this line
            if (data.success) {
                alert(data.message);
                window.location.href = '{{ route('sales.index') }}';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error); // Add this line
            alert('Failed to delete the sale.');
        });
    }
});
</script>
@endsection
