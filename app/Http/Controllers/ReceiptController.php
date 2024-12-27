<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function store(Request $request)
    {
        $sale = Sale::with('items')->find($request->sale_id);

        $receipt = Receipt::create([
            'sale_id' => $sale->id,
            'date' => now(),
            'total_amount' => $sale->total_amount,
            'receipt_details' => json_encode($sale->items), // Assuming 'items' is a relationship or attribute containing sale items
        ]);

        return response()->json($receipt);
    }

    public function testReceipt($sale_id)
    {
        $sale = Sale::with('items')->find($sale_id);

        if (!$sale) {
            return "Sale not found.";
        }

        $receiptContent = '<h4>Receipt</h4>';
        $receiptContent .= '<p>Date: ' . now()->format('Y-m-d H:i:s') . '</p>';
        $receiptContent .= '<ul>';
        foreach ($sale->items as $item) {
            $receiptContent .= '<li>' . $item->product_name . ' - ' . $item->quantity . ' x ' . $item->price . '</li>';
        }
        $receiptContent .= '</ul>';
        $receiptContent .= '<p>Total: ' . $sale->total_amount . '</p>';

        return view('admin.sales.test-receipt', compact('receiptContent'));
    }
}
