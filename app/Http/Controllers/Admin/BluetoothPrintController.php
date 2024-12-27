<?php

namespace App\Http\Controllers\Admin;

use App\Models\Receipt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class BluetoothPrintController extends Controller
{
    public function printReceipt($invoice_id)
    {
        Log::info('printReceipt method called with invoice_id: ' . $invoice_id);

        $receipt = Receipt::with(['sales.barCodeData'])->where('invoice_id', $invoice_id)->first();

        if (!$receipt) {
            Log::error('Receipt not found for invoice_id: ' . $invoice_id);
            return response()->json([[ 
                'type' => 0,
                'content' => 'Error: Receipt not found',
                'bold' => 1,
                'align' => 1,
                'format' => 0,
            ]], 404);
        }

        $data = [
            [
                'type' => 0,
                'content' => 'វិក្កយបត្រ',
                'bold' => 1,
                'align' => 1,
                'format' => 1,
            ],
            [
                'type' => 0,
                'content' => 'កាលបរិច្ឆេទ: ' . \Carbon\Carbon::parse($receipt->date)->format('d-m-Y'),
                'bold' => 0,
                'align' => 0,
                'format' => 0,
            ],
        ];

        foreach ($receipt->sales as $sale) {
            $data[] = [
                'type' => 0,
                'content' => $sale->barCodeData->product_name . ' x ' . $sale->quantity,
                'bold' => 0,
                'align' => 0,
                'format' => 0,
            ];
            $data[] = [
                'type' => 0,
                'content' => '$' . number_format($sale->total_price, 2),
                'bold' => 0,
                'align' => 2,
                'format' => 0,
            ];
        }

        $data[] = [
            'type' => 0,
            'content' => 'សរុប ($): $' . number_format($receipt->sales->first()->pay_amount_dollar - $receipt->sales->first()->due_amount_dollar, 2),
            'bold' => 1,
            'align' => 2,
            'format' => 0,
        ];
        $data[] = [
            'type' => 0,
            'content' => 'សរុប (៛): ៛' . number_format(($receipt->sales->first()->pay_amount_dollar - $receipt->sales->first()->due_amount_dollar) * 4100),
            'bold' => 1,
            'align' => 2,
            'format' => 0,
        ];
        $data[] = [
            'type' => 0,
            'content' => 'លុយអាប់: $' . number_format($receipt->sales->first()->due_amount_dollar, 2) . ' / ៛' . number_format($receipt->sales->first()->due_amount_riel),
            'bold' => 0,
            'align' => 2,
            'format' => 0,
        ];

        Log::info('Generated data for printing: ', $data);

        return response()->json($data, 200, [], JSON_FORCE_OBJECT);
    }
}
