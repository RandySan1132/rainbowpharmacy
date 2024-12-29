<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Receipt; // Add this import
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;  // Import DB facade
use App\Models\PurchaseSale; // Import the PurchaseSale model if you've created one
use App\Models\BarCodeData; // Ensure you have this import at the top
use App\Models\User; // Add this line to import the User model
use Illuminate\Support\Facades\Notification; // Add this line to import the Notification facade
use App\Notifications\LowStockNotification; // Add this line to import the LowStockNotification class
use App\Models\BoxInventory; // Ensure this line is present
use App\Models\Category; // Add this line
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function create()
    {
        $title = 'Create Sale (POS)';

        // Get products with stock quantities and barcodes
        $products = BarCodeData::with('category')
            ->whereHas('purchases', function($query) {
                $query->whereDate('expiry_date', '>', DB::raw('CURDATE()'));
            })
            ->withCount([
                'purchases as box_stock' => function ($query) {
                    $query->whereDate('expiry_date', '>', DB::raw('CURDATE()'))
                          ->select(DB::raw('SUM(quantity)'));
                },
                'purchases as pill_stock' => function ($query) {
                    $query->whereDate('expiry_date', '>', DB::raw('CURDATE()'))
                          ->select(DB::raw('SUM(pill_amount * quantity)'));
                },
                'purchases as leftover_pill_stock' => function ($query) {
                    $query->whereDate('expiry_date', '>', DB::raw('CURDATE()'))
                          ->select(DB::raw('SUM(leftover_pills)'));
                }
            ])
            ->with(['purchases' => function($query) {
                $query->whereDate('expiry_date', '>', DB::raw('CURDATE()'))
                      ->select('bar_code_id', 'leftover_pills', 'pill_amount', 'quantity', 'expiry_date');
            }])
            ->get();

        $categories = Category::all();
        // Fetch the 'Medicine' category ID
        $medicineCategory = Category::where('name', 'Medicine')->first();
        $medicineCategoryId = $medicineCategory ? $medicineCategory->id : null;

        return view('admin.sales.create', compact('title', 'products', 'categories', 'medicineCategoryId'));
    }
    

    public function index(Request $request)
    {
        $title = 'Sales';

        if ($request->ajax()) {
            $sales = Sale::with('barCodeData')
                ->select(
                    'invoice_id',
                    DB::raw('SUM(total_price) as total_price'),
                    'payment_method',
                    DB::raw('MAX(created_at) as created_at'),
                    DB::raw('SUM(quantity) as quantity')
                )
                ->groupBy('invoice_id', 'payment_method');

            return DataTables::of($sales)
                ->addColumn('product', function ($sale) {
                    return $sale->barCodeData ? $sale->barCodeData->product_name : 'No Product';
                })
                ->addColumn('quantity', function ($sale) {
                    return $sale->quantity;
                })
                ->addColumn('id', function ($sale) {
                    return $sale->id;
                })
                ->addColumn('invoice_id', function ($sale) {
                    return $sale->invoice_id;
                })
                ->addColumn('total_price', function ($sale) {
                    return number_format($sale->total_price, 2);
                })
                ->addColumn('payment_method', function ($sale) {
                    return $sale->payment_method ?? '';
                })
                ->addColumn('date', function ($sale) {
                    return \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d');
                })
                ->addColumn('action', function ($sale) {
                    $viewBtn = '<a href="' . route("sales.view", $sale->invoice_id) . '" class="btn btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>';
                    $deleteBtn = '<button data-id="' . $sale->invoice_id . '" 
                                   data-route="' . route('sales.destroy', $sale->invoice_id) . '" 
                                   class="btn btn-danger delete-btn">
                                   <i class="fas fa-trash"></i>
                               </button>';
                    return $viewBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $query->where(function ($subQuery) use ($searchValue) {
                            $subQuery->where('invoice_id', 'LIKE', "%{$searchValue}%")
                                ->orWhere('total_price', 'LIKE', "%{$searchValue}%")
                                ->orWhere('created_at', 'LIKE', "%{$searchValue}%");
                        });
                    }
                })
                ->make(true);
        }

        return view('admin.sales.index', compact('title'));
    }
    
    

    public function store(Request $request)
    {
        Log::info('Store method called.');
        $this->validate($request, [
            'cart' => 'required|array|min:1',
            'cart.*.bar_code_id' => 'required|exists:bar_code_data,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.sale_by' => 'required|in:box,pill',
            'discount_type' => 'nullable|string',
            'discount_value' => 'nullable|numeric|min:0',
            'pay_amount_dollar' => 'nullable|numeric|min:0',
            'pay_amount_riel' => 'nullable|numeric|min:0',
            'due_amount_dollar' => 'nullable|numeric|min:0',
            'due_amount_riel' => 'nullable|numeric|min:0',
            'cashback_dollar' => 'nullable|numeric|min:0',
            'cashback_riel' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $cartItems = $request->input('cart', []);
            $discountType = $request->input('discount_type');
            $discountValue = $request->input('discount_value', 0);
            $payAmountDollar = $request->input('pay_amount_dollar', 0);
            $payAmountRiel = $request->input('pay_amount_riel', 0);
            $dueAmountDollar = $request->input('due_amount_dollar', 0);
            $dueAmountRiel = $request->input('due_amount_riel', 0);

            // Correctly calculate and assign cashback:
            $cashbackDollar = $dueAmountDollar < 0 ? abs($dueAmountDollar) : 0;
            $cashbackRiel = $dueAmountRiel < 0 ? abs($dueAmountRiel) : 0;
            if ($dueAmountDollar < 0) { $dueAmountDollar = 0; }
            if ($dueAmountRiel < 0) { $dueAmountRiel = 0; }

            $paymentMethod = $request->input('payment_method', 'cash');

            // Generate a unique invoice ID
            $invoiceId = 'INV-' . strtoupper(uniqid());

            $totalAmount = 0; // Initialize total amount for the receipt

            foreach ($cartItems as $item) {
                // If you suspect the bar_code_id is coming as "17_pill", split it:
                if (strpos($item['bar_code_id'], '_') !== false) {
                    [$pureId, ] = explode('_', $item['bar_code_id']);
                    $barCodeId = (int) $pureId;
                } else {
                    $barCodeId = (int) $item['bar_code_id'];
                }

                $saleBy = $item['sale_by'];
                $quantity = (int) $item['quantity'];

                $product = BarCodeData::find($barCodeId);

                // Determine total price based on sale type
                $totalPrice = $saleBy === 'box'
                    ? $quantity * $product->price
                    : $quantity * $product->price_per_pill;

                $totalAmount += $totalPrice; // Add to total amount for the receipt

                // Retrieve available purchases using FIFO
                $purchases = Purchase::where('bar_code_id', $barCodeId)
                    ->whereDate('expiry_date', '>', DB::raw('CURDATE()'))
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Log the purchases for debugging
                Log::info('Purchases for product:', ['product_id' => $barCodeId, 'purchases' => $purchases]);

                // ↓↓↓ Add this total stock check ↓↓↓
                $availableStock = 0;
                if ($saleBy === 'box') {
                    $availableStock = $purchases->sum('quantity');
                    Log::info('Available box stock:', ['product_id' => $barCodeId, 'available_stock' => $availableStock]);
                    if ($availableStock < $quantity) {
                        throw new \Exception("Not enough box stock for product: {$product->product_name}");
                    }
                } elseif ($saleBy === 'pill') {
                    foreach ($purchases as $purchase) {
                        // Sum leftover pills regardless of box quantity
                        $availableStock += ($purchase->quantity * $purchase->pill_amount) + $purchase->leftover_pills;
                        Log::info('Calculating available pill stock:', [
                            'purchase_id' => $purchase->id,
                            'quantity' => $purchase->quantity,
                            'pill_amount' => $purchase->pill_amount,
                            'leftover_pills' => $purchase->leftover_pills,
                            'current_available_stock' => $availableStock
                        ]);
                    }
                    Log::info('Total available pill stock:', ['product_id' => $barCodeId, 'available_stock' => $availableStock]);
                    if ($availableStock < $quantity) {
                        Log::warning("Insufficient pill stock for product: {$product->product_name}. Requested: {$quantity}, Available: {$availableStock}");
                        throw new \Exception("Not enough pill stock for product: {$product->product_name}");
                    }
                }
                // ↑↑↑ End of added check ↑↑↑

                if ($purchases->isEmpty()) {
                    throw new \Exception("No available stock for product: {$product->product_name}");
                }

                $remainingQuantity = $quantity;
                $totalDeductedQuantity = 0; // Initialize the variable

                foreach ($purchases as $purchase) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    if ($saleBy === 'box') {
                        // Convert purchase->quantity (boxes) to a usable integer
                        $currentBoxes = (int) $purchase->quantity;
                        // Removed the check: if ($currentBoxes <= 0) { continue; }

                        // Deduct boxes first
                        $deductedBoxes = min($currentBoxes, $remainingQuantity);
                        Log::info('Deducting boxes:', ['purchase_id' => $purchase->id, 'deducted_boxes' => $deductedBoxes]);
                        $purchase->quantity = $currentBoxes - $deductedBoxes;
                        $purchase->save();
                        $remainingQuantity -= $deductedBoxes;
                        $totalDeductedQuantity += $deductedBoxes;

                        // If boxes hit zero but leftover pills remain, use leftover pills
                        while ($remainingQuantity > 0 && $purchase->leftover_pills > 0) {
                            $deductedPills = min($purchase->leftover_pills, $remainingQuantity);
                            $purchase->leftover_pills -= $deductedPills;
                            $purchase->save();
                            $remainingQuantity -= $deductedPills;
                            $totalDeductedQuantity += $deductedPills;
                            Log::info('Used leftover pills in box scenario:', [
                                'purchase_id' => $purchase->id,
                                'deducted_pills' => $deductedPills,
                                'remainingQuantity' => $remainingQuantity
                            ]);
                        }

                    } elseif ($saleBy === 'pill') {
                        // Use a while loop to allow multiple unbox attempts from the same purchase
                        while ($remainingQuantity > 0 && ($purchase->quantity > 0 || $purchase->leftover_pills > 0)) {
                            Log::info('Before deduction:', [
                                'purchase_id' => $purchase->id,
                                'remainingQuantity' => $remainingQuantity,
                                'leftover_pills' => $purchase->leftover_pills,
                                'purchase_boxes' => $purchase->quantity
                            ]);

                            // Check if leftover pills alone can fulfill the need
                            if ($purchase->leftover_pills >= $remainingQuantity) {
                                $deductedPills = $remainingQuantity;
                                $purchase->leftover_pills -= $deductedPills;
                                $purchase->save();

                                $remainingQuantity = 0;
                                $totalDeductedQuantity += $deductedPills;
                                Log::info('Deducted pills without unboxing:', [
                                    'purchase_id' => $purchase->id,
                                    'deducted_pills' => $deductedPills,
                                    'remainingQuantity' => $remainingQuantity,
                                    'leftover_pills' => $purchase->leftover_pills,
                                    'purchase_boxes' => $purchase->quantity
                                ]);
                                break;  // Move on once done
                            }

                            // If leftover pills are not enough, use them first
                            if ($purchase->leftover_pills > 0) {
                                $deductedPills = $purchase->leftover_pills;
                                $purchase->leftover_pills = 0;
                                $purchase->save();

                                $remainingQuantity -= $deductedPills;
                                $totalDeductedQuantity += $deductedPills;
                                Log::info('Partially used leftover pills:', [
                                    'purchase_id' => $purchase->id,
                                    'deducted_pills' => $deductedPills,
                                    'remainingQuantity' => $remainingQuantity,
                                    'purchase_boxes' => $purchase->quantity
                                ]);
                            }

                            // Unbox if leftover pills are not sufficient
                            if ($remainingQuantity > 0 && $purchase->quantity > 0) {
                                $purchase->quantity -= 1;
                                $purchase->leftover_pills += $purchase->pill_amount;
                                $purchase->save();

                                Log::info('Unboxed a box:', [
                                    'purchase_id' => $purchase->id,
                                    'new_quantity' => $purchase->quantity,
                                    'new_leftover_pills' => $purchase->leftover_pills
                                ]);
                            } else {
                                Log::info('No more boxes or leftover pills to unbox for purchase_id: ' . $purchase->id);
                                // Changed from 'break;' to 'continue;' so it moves on to the next purchase
                                continue;
                            }
                        }
                    }
                }

                if ($remainingQuantity > 0) {
                    Log::warning("Not enough stock after deductions for product: {$product->product_name}. Remaining Quantity: {$remainingQuantity}");
                    throw new \Exception("Not enough stock for product: {$product->product_name}");
                }

                // Create sale record with bar_code_id
                $sale = Sale::create([
                    'invoice_id' => $invoiceId,
                    'purchase_id' => null, // Primary purchase association can be handled as needed
                    'bar_code_id' => $barCodeId,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                    'sale_by' => $saleBy, // If not already set
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'pay_amount_dollar' => $payAmountDollar,
                    'pay_amount_riel' => $payAmountRiel,
                    'due_amount_dollar' => $dueAmountDollar,
                    'due_amount_riel' => $dueAmountRiel,
                    'cashback_dollar' => $cashbackDollar,
                    'cashback_riel' => $cashbackRiel,
                    'payment_method' => $paymentMethod,
                ]);

                // Associate sale with purchases
                PurchaseSale::create([
                    'sale_id' => $sale->id,
                    'purchase_id' => $purchases->first()->id, // Associate with the first purchase
                    'quantity' => $totalDeductedQuantity,
                    'sale_by' => $saleBy, // Add this field if not present
                ]);

                // Update stock status
                $this->updateStockStatus($barCodeId);
            }

            // Create a single receipt per invoice
            $receipt = Receipt::create([
                'invoice_id' => $invoiceId,
                'date' => now(),
                'total_amount' => $totalAmount,
                'receipt_details' => 'All items for this invoice', // or any relevant details
                'sale_id' => $sale->id, // Add this line
            ]);

            DB::commit();
            Log::info('Sale processed successfully.');

            return response()->json(['success' => true, 'message' => 'Sale processed successfully.', 'invoice_id' => $receipt->id]);
        } catch (\Exception $e) {
            Log::error('Error processing sale: ' . $e->getMessage());
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to process the sale: ' . $e->getMessage()], 500);
        }
    }
    

    public function reports(Request $request)
    {
        $title = 'Sales Reports';
    
        if ($request->ajax()) {
            $sales = Sale::with('purchase.barCodeData')->select('sales.*');
    
            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('product', function ($sale) {
                    return $sale->purchase && $sale->purchase->barCodeData
                        ? $sale->purchase->barCodeData->product_name
                        : 'No Product';
                })
                ->addColumn('total_price', function ($sale) {
                    return settings('app_currency', '$') . ' ' . number_format($sale->total_price, 2);
                })
                ->addColumn('date', function ($sale) {
                    return $sale->created_at->format('d M, Y');
                })
                ->make(true);
        }
    
        return view('admin.sales.reports', compact('title'));
    }
    
    
    public function generateReport(Request $request)
    {
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
    
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
    
        // Fetch sales within the specified date range
        $sales = Sale::with('purchase.barCodeData')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();
    
        $title = 'Sales Reports';
    
        // Return the report view with the sales data
        return view('admin.sales.reports', compact('sales', 'title'));
    }
    
    public function edit($id)
    {
        $sale = Sale::with(['purchase.barCodeData'])->findOrFail($id);
    
        // Group purchases by product and sum quantities
        $purchases = Purchase::with('barCodeData')
            ->select('bar_code_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('quantity', '>', 0)
            ->groupBy('bar_code_id')
            ->get();
    
        return view('admin.sales.edit', compact('sale', 'purchases'));
    }
    
    public function destroy($invoice_id)
    {
        // Increase the maximum execution time
        set_time_limit(300); // Set to 5 minutes

        DB::beginTransaction();
    
        try {
            Log::info('Attempting to delete sales for invoice_id: ' . $invoice_id);
    
            $receipt = Receipt::where('invoice_id', $invoice_id)->first();
    
            if (!$receipt) {
                throw new \Exception("No receipt found for invoice_id: {$invoice_id}");
            }
    
            Log::info('Receipt found: ' . $receipt->id);
    
            Sale::where('invoice_id', $invoice_id)->chunk(100, function($sales) {
                foreach ($sales as $sale) {
                    Log::info("Processing sale_id: {$sale->id}");
                    PurchaseSale::where('sale_id', $sale->id)->chunk(100, function($purchaseSales) use ($sale) {
                        foreach ($purchaseSales as $purchaseSale) {
                            $purchase = Purchase::find($purchaseSale->purchase_id);
                            if ($purchase) {
                                if ($sale->sale_by === 'box') {
                                    $purchase->quantity += $sale->quantity;
                                } else if ($sale->sale_by === 'pill') {
                                    $purchase->leftover_pills += $sale->quantity;
                                    while ($purchase->leftover_pills >= $purchase->pill_amount) {
                                        $purchase->leftover_pills -= $purchase->pill_amount;
                                        $purchase->quantity++;
                                    }
                                }
                                $purchase->save();
                                Log::info("Updated stock for purchase_id: {$purchase->id}");
                                $this->updateStockStatus($purchase->bar_code_id);
                            } else {
                                Log::warning("Purchase not found for purchase_id: {$purchaseSale->purchase_id}");
                            }
                        }
                        PurchaseSale::whereIn('id', $purchaseSales->pluck('id'))->delete();
                    });
                    $sale->forceDelete();
                    Log::info("Force-deleted sale_id: {$sale->id}");
                }
            });
    
            $receipt->delete();
            Log::info("Deleted receipt_id: {$receipt->id}");
    
            DB::commit();
    
            return response()->json(['success' => true, 'message' => 'Sale deleted and stock returned successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete sale: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete sale: ' . $e->getMessage()], 500);
        }
    }
    
    
    public function manualDelete($invoice_id)
    {
        try {
            Log::info('Manually attempting to delete sales for invoice_id: ' . $invoice_id);

            $receipt = Receipt::where('invoice_id', $invoice_id)->first();

            if (!$receipt) {
                throw new \Exception("No receipt found for invoice_id: {$invoice_id}");
            }

            Log::info('Receipt found: ' . $receipt->id);

            Sale::where('invoice_id', $invoice_id)->chunk(100, function($sales) {
                foreach ($sales as $sale) {
                    Log::info("Processing sale_id: {$sale->id}");
                    PurchaseSale::where('sale_id', $sale->id)->chunk(100, function($purchaseSales) use ($sale) {
                        foreach ($purchaseSales as $purchaseSale) {
                            $purchase = Purchase::find($purchaseSale->purchase_id);
                            if ($purchase) {
                                if ($sale->sale_by === 'box') {
                                    $purchase->quantity += $sale->quantity;
                                } else if ($sale->sale_by === 'pill') {
                                    $purchase->leftover_pills += $sale->quantity;
                                    while ($purchase->leftover_pills >= $purchase->pill_amount) {
                                        $purchase->leftover_pills -= $purchase->pill_amount;
                                        $purchase->quantity++;
                                    }
                                }
                                $purchase->save();
                                Log::info("Updated stock for purchase_id: {$purchase->id}");
                                $this->updateStockStatus($purchase->bar_code_id);
                            } else {
                                Log::warning("Purchase not found for purchase_id: {$purchaseSale->purchase_id}");
                            }
                        }
                        PurchaseSale::whereIn('id', $purchaseSales->pluck('id'))->delete();
                    });
                    $sale->forceDelete();
                    Log::info("Force-deleted sale_id: {$sale->id}");
                }
            });

            $receipt->delete();
            Log::info("Deleted receipt_id: {$receipt->id}");

            return redirect()->route('sales.index')->with('success', 'Sale deleted and stock returned successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete sale: ' . $e->getMessage());
            return redirect()->route('sales.index')->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }
    
    public function update(Request $request, $id)
    {
        // Validate the input
        $this->validate($request, [
            'bar_code_id' => 'required|exists:purchases,bar_code_id',
            'quantity' => 'required|integer|min:1'
        ]);
    
        // Find the sale to update
        $sale = Sale::findOrFail($id);
    
        // Calculate the difference in quantities
        $newQuantity = $request->quantity;
        $oldQuantity = $sale->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;
    
        // Get the product purchases
        $barCodeId = $request->bar_code_id;
        $purchases = Purchase::where('bar_code_id', $barCodeId)
                             ->orderBy('created_at', 'asc')
                             ->get();
    
        // Adjust inventory based on the quantity difference
        if ($quantityDifference > 0) {
            // Increase in quantity (deduct from inventory)
            $totalQuantityAvailable = $purchases->sum('quantity');
            if ($totalQuantityAvailable < $quantityDifference) {
                return back()->withErrors(['quantity' => 'Insufficient quantity available.']);
            }
    
            // Deduct the additional quantity using FIFO
            $remainingQuantity = $quantityDifference;
            foreach ($purchases as $purchase) {
                if ($remainingQuantity <= 0) break;
    
                $deductQuantity = min($purchase->quantity, $remainingQuantity);
                $purchase->update(['quantity' => $purchase->quantity - $deductQuantity]);
                $remainingQuantity -= $deductQuantity;
            }
        } elseif ($quantityDifference < 0) {
            // Decrease in quantity (add back to inventory)
            $remainingQuantity = abs($quantityDifference);
    
            foreach ($purchases as $purchase) {
                if ($remainingQuantity <= 0) break;
    
                // Calculate how much to add back without exceeding the original quantity
                $addQuantity = min($remainingQuantity, $purchase->original_quantity - $purchase->quantity);
                $purchase->update(['quantity' => $purchase->quantity + $addQuantity]);
                $remainingQuantity -= $addQuantity;
            }
        }
    
        // Calculate the total price based on the updated quantity
        $product = $purchases->first()->barCodeData;
        $totalPrice = $newQuantity * $product->price;
    
        // Update the sale record
        $sale->update([
            'purchase_id' => $purchases->first()->id,
            'quantity' => $newQuantity,
            'total_price' => $totalPrice,
        ]);
    
        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }
    
    protected function updateStockStatus($barCodeId)
    {
        // Calculate the total stock for the product by summing quantities from all associated purchases
        $totalStock = Purchase::where('bar_code_id', $barCodeId)->sum('quantity');
    
        // Fetch the product from the bar_code_data table
        $product = BarCodeData::find($barCodeId);
    
        if ($product) {
            // Update the product's in_stock status
            $product->in_stock = $totalStock > 0;
            $product->save();
    
            // If stock is 5 or below, create a notification
            if ($totalStock <= 5) {
                // Notify users with an existing role, e.g., 'super-admin'
                $admins = User::role('super-admin')->get(); // Use an existing role
                Notification::send($admins, new LowStockNotification($product, $totalStock));
            }
        }
    }
    
    public function view($invoice_id)
    {
        Log::info('View method called with invoice_id: ' . $invoice_id);
        $sales = Sale::with(['barCodeData', 'receipt'])->where('invoice_id', $invoice_id)->get();
        if ($sales->isEmpty()) {
            Log::error('Sale not found for invoice_id: ' . $invoice_id);
            return redirect()->route('sales.index')->with('error', 'Sale not found.');
        }

        // Collect all receipts related to the sales
        $receipts = $sales->pluck('receipt')->filter();

        if ($receipts->isEmpty()) {
            Log::error('Receipts not found for invoice_id: ' . $invoice_id);
            return redirect()->route('sales.index')->with('error', 'Receipts not found.');
        }

        Log::info('Receipts found for invoice_id: ' . $invoice_id);
        return view('admin.sales.view', compact('sales', 'invoice_id', 'receipts'));
    }
}



