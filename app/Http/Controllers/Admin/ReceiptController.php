<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function printReceipt($invoiceId)
    {
        Log::info('printReceipt method called with invoiceId: ' . $invoiceId);
        $receipt = Receipt::where('invoice_id', $invoiceId)->with('sales.barCodeData')->first();
        if (!$receipt) {
            Log::error('Receipt not found for invoiceId: ' . $invoiceId);
            return redirect()->route('sales.index')->with('error', 'Receipt not found.');
        }
        return view('admin.sales.print-receipt', compact('receipt', 'invoiceId'));
    }

    public function bareReceipt($id)
    {
        $receipt = Receipt::findOrFail($id);
        return view('admin.sales.bare-receipt', compact('receipt'));
    }
}