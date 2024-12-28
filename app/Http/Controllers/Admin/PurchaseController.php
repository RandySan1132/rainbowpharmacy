<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;  // Import User model

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BarCodeData;
use Illuminate\Support\Facades\Log; // Ensure you have this import at the top
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockNotification;
use Spatie\Permission\Models\Role; // Ensure you have this import at the top
use App\Models\BoxInventory; // Ensure you have this import at the top
use Carbon\Carbon; // Ensure you have this import

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Purchases';
        if ($request->ajax()) {
            $purchases = Purchase::with(['barCodeData', 'category', 'supplier', 'purchaseDetails'])
                ->get();
    
            return DataTables::of($purchases)
                ->addColumn('product', function ($purchase) {
                    $product = $purchase->barCodeData;
                    if ($product) {
                        $image = '<img class="avatar-img" src="' . asset("storage/purchases/" . $product->image) . '" alt="product" style="max-width: 50px;">';
                        return $product->product_name . ' ' . $image;
                    }
                    return 'No Product';
                })
                ->addColumn('category', function ($purchase) {
                    return $purchase->category ? $purchase->category->name : 'No Category';
                })
                ->addColumn('supplier', function ($purchase) {
                    return $purchase->supplier ? $purchase->supplier->name : 'No Supplier';
                })
                ->addColumn('invoice_no', function ($purchase) {
                    return $purchase->purchaseDetails->first()->invoice_no ?? '-';
                })
                ->addColumn('purchase_date', function ($purchase) {
                    return $purchase->purchaseDetails->first()->date ?? '-';
                })
                ->addColumn('cost_price', function ($purchase) {
                    return settings('app_currency', '$') . ' ' . $purchase->cost_price;
                })
                ->addColumn('quantity', function ($purchase) {
                    return $purchase->quantity;
                })
                ->addColumn('expiry_date', function ($purchase) {
                    return date_format(date_create($purchase->expiry_date), 'm/d/Y');
                })
                ->addColumn('pill_amount', function ($purchase) {
                    return ($purchase->pill_amount ?? 0) * ($purchase->quantity ?? 0);
                })
                ->addColumn('leftover_pills', function ($purchase) {
                    return $purchase->leftover_pills ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $editBtn = auth()->user()->can('edit-purchase') 
                        ? '<a href="' . route("purchases.edit", $row->id) . '" class="btn btn-info mr-2">
                               <i class="fas fa-edit"></i> 
                           </a>' 
                        : '';
    
                    $deleteBtn = auth()->user()->can('destroy-purchase') 
                        ? '<button class="btn btn-danger delete-btn" 
                                    data-id="' . $row->id . '" 
                                    data-route="' . route('purchases.destroy', $row->id) . '">
                               <i class="fas fa-trash"></i> 
                           </button>' 
                        : '';
    
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['product', 'action'])
                ->make(true);
        }
    
        return view('admin.purchases.index', compact('title'));
    }
    




    public function create()
    {
        $title = 'Create Purchase';
        $categories = Category::all();
        $suppliers = Supplier::all();
        $products = BarCodeData::all(); // Fetch all products with pill amounts
        return view('admin.purchases.create', compact('title', 'categories', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $productIds = $request->product_id ?? [];
            if (count($productIds) !== count(array_unique($productIds))) {
                throw new \Exception('Duplicate products are not allowed in a single purchase.');
            }

// Handle invoice image upload
$invoiceImagePath = null;
if ($request->hasFile('invoice_image')) {
    // Store the file in 'public/storage/purchases' (mapped to 'storage/app/public/purchases')
    $invoiceImagePath = $request->file('invoice_image')->storeAs(
        'public/purchases', // This will save the file in storage/app/public/purchases
        $request->file('invoice_image')->getClientOriginalName()
    );
}

    
            foreach ($productIds as $index => $productId) {
                // Fetch product details from the bar_code_data table
                $product = BarCodeData::findOrFail($productId);
                $categoryName = strtolower($product->category->name);

                // Default to 0 if not medicine or pill_amount is null
                $pillAmount = ($categoryName === 'medicine' && $product->pill_amount !== null)
                    ? $product->pill_amount
                    : 0;
    
                // Log the values being set
                Log::info('Creating purchase', [
                    'bar_code_id' => $product->id,
                    'category_id' => $product->category_id,
                    'supplier_id' => $request->supplier,
                    'quantity' => $request->box_qty[$index] ?? 0,
                    'cost_price' => $request->cost_price[$index] ?? $product->cost_price,
                    'expiry_date' => $request->expiry_date[$index] ?? null,
                    'original_quantity' => $request->box_qty[$index] ?? 0,
                    'image' => $product->image,
                    'pill_amount' => $pillAmount,
                    'original_pill_amount' => $pillAmount,
                    'total_pill_amount' => ($pillAmount !== null) ? ($request->box_qty[$index] ?? 0) * $pillAmount : null,
                ]);
    
                $expiryDateValue = $request->expiry_date[$index] ?? null;
                $nearExpiryDateValue = $expiryDateValue
                    ? Carbon::parse($expiryDateValue)->subDays(7)->toDateString()
                    : null;

                Log::info('Storing purchase', [
                    'expiry_date' => $expiryDateValue,
                    'near_expiry_date' => $nearExpiryDateValue,
                ]);

                // Create a new purchase entry
                $purchase = Purchase::create([
                    'bar_code_id' => $product->id,
                    'category_id' => $product->category_id,
                    'supplier_id' => $request->supplier,
                    'quantity' => $request->box_qty[$index] ?? 0,
                    'cost_price' => $request->cost_price[$index] ?? $product->cost_price,
                    'expiry_date' => $expiryDateValue,
                    'near_expiry_date' => $nearExpiryDateValue,
                    'original_quantity' => $request->box_qty[$index] ?? 0,
                    'image' => $product->image,
                    'pill_amount' => $pillAmount,
                    'original_pill_amount' => $pillAmount,
                    'total_pill_amount' => ($pillAmount !== null) ? ($request->box_qty[$index] ?? 0) * $pillAmount : null,
                ]);

                Log::info('Purchase stored', [
                    'purchase_id' => $purchase->id,
                    'near_expiry_date' => $purchase->near_expiry_date,
                ]);
    
                // Store invoice details in purchase_details table
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'bar_code' => $product->bar_code,
                    'invoice_no' => $request->invoice_no,
                    'date' => $request->date,
                    'total_purchase_price' => $request->total_purchase_price[$index] ?? 0,
                    'invoice_image' => $invoiceImagePath ? basename($invoiceImagePath) : null, // Ensure this line is present
                ]);
    
                // Create entries in the box_inventories table only for medicine products
                if ($product->category->name == 'medicine') {
                    for ($i = 0; $i < $purchase->quantity; $i++) {
                        BoxInventory::create([
                            'purchase_id' => $purchase->id,
                            'remaining_pills' => $pillAmount,
                        ]);
                    }
                }
    
                // Update stock status for each product after creating BoxInventory
                $this->updateStockStatus($product->id);
            }
        });
    
        return redirect()->route('admin.purchases.index')->with('success', 'Purchase added successfully.');
    }
    



    public function edit($id)
    {
        $purchase = Purchase::with('purchaseDetails')->findOrFail($id);
        $invoice_no = $purchase->purchaseDetails->first()->invoice_no ?? '';
        $purchases = Purchase::whereHas('purchaseDetails', function ($query) use ($invoice_no) {
            $query->where('invoice_no', $invoice_no);
        })->get();

        return view('admin.purchases.edit', compact('purchases'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->validate($request, [
            'supplier' => 'required',
            'invoice_no' => 'required',
            'date' => 'required|date',
            'original_quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        // Fetch product details from the bar_code_data table
        $product = BarCodeData::findOrFail($purchase->bar_code_id);

        // Check if the product is in 'medicine' category
        if ($product->category->name == 'medicine') {
            $this->validate($request, [
                'pill_amount' => 'required|integer|min:1', // Ensure pill_amount is validated
            ]);
            $pillAmount = $request->pill_amount;
        } else {
            $pillAmount = null;
        }

        // Check for changes in quantity and pill_amount
        $quantityDifference = $request->original_quantity - $purchase->original_quantity;
        $pillAmountChanged = $pillAmount != $purchase->pill_amount;

        $expiryDateValue = $request->expiry_date;
        $nearExpiryDateValue = $expiryDateValue
            ? Carbon::parse($expiryDateValue)->subDays(7)->toDateString()
            : null;

        Log::info('Updating purchase', [
            'expiry_date' => $expiryDateValue,
            'near_expiry_date' => $nearExpiryDateValue,
        ]);

        // Update purchase
        $purchase->update([
            'original_quantity' => $request->original_quantity,
            'quantity' => $purchase->quantity + $quantityDifference,
            'cost_price' => $request->cost_price,
            'expiry_date' => $expiryDateValue,
            'near_expiry_date' => $nearExpiryDateValue,
            'pill_amount' => $pillAmount,
            'original_pill_amount' => $pillAmount,
        ]);

        Log::info('Purchase updated', [
            'purchase_id' => $purchase->id,
            'near_expiry_date' => $purchase->near_expiry_date,
        ]);

        // Update purchase detail
        $purchaseDetail = $purchase->purchaseDetails->first();
        $purchaseDetail->update([
            'invoice_no' => $request->invoice_no,
            'date' => $request->date,
            'total_purchase_price' => $request->total_purchase_price,
        ]);

        // If quantity has increased, add new BoxInventory records
        if ($quantityDifference > 0 && $product->category->name == 'medicine') {
            for ($i = 0; $i < $quantityDifference; $i++) {
                BoxInventory::create([
                    'purchase_id' => $purchase->id,
                    'remaining_pills' => $pillAmount,
                ]);
            }
        }

        // If pill_amount has changed, update existing BoxInventory records
        if ($pillAmountChanged && $product->category->name == 'medicine') {
            foreach ($purchase->boxInventories as $boxInventory) {
                // Update remaining_pills based on the new pill_amount
                $boxInventory->update([
                    'remaining_pills' => min($boxInventory->remaining_pills, $pillAmount),
                ]);
            }
        }

        // Update stock status for the product
        $this->updateStockStatus($purchase->bar_code_id);

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
    }


    public function destroy($id)
    {
        Log::emergency("Destroy method invoked for purchase with ID: {$id}");
        try {
            $purchase = Purchase::findOrFail($id);
            Log::info("Purchase found: ID = {$purchase->id}");
            
            $purchase->delete();
            Log::info("Purchase deleted successfully: ID = {$purchase->id}");
            
            // Update stock status if needed
            $this->checkAndUpdateStockStatus($purchase->bar_code_id);
            Log::info("Stock status updated for barcode ID: {$purchase->bar_code_id}");
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting purchase with ID {$id}: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase: ' . $e->getMessage()
            ], 500);
        }
    }



    public function getBarcodeData(Request $request)
    {
        Log::info('getBarcodeData called', ['request' => $request->all()]); // Log the request data

        $product = BarCodeData::when($request->bar_code, function ($query) use ($request) {
                return $query->where('bar_code', $request->bar_code);
            })
            ->when($request->product_id, function ($query) use ($request) {
                return $query->where('id', $request->product_id);
            })
            ->first();
    
        if ($product) {
            $response = [
                'success' => true,
                'product_name' => $product->product_name,
                'bar_code' => $product->bar_code,
                'cost_price' => $product->cost_price,
                'pill_amount' => $product->pill_amount,
                'image_url' => asset('storage/purchases/' . $product->image),
                'category_name' => $product->category->name,
            ];
            Log::info('Product found', ['response' => $response]); // Log the response data
            return response()->json($response);
        }
    
        $response = ['success' => false, 'message' => 'Product not found.'];
        Log::warning('Product not found', ['response' => $response]); // Log the response data
        return response()->json($response, 404);
    }
    

    public function getSupplierProducts(Request $request)
    {
        $products = BarCodeData::where('supplier_id', $request->supplier_id)
            ->where('product_name', 'like', '%' . $request->search . '%')
            ->get(['id', 'product_name']);

        if ($products->isEmpty()) {
            return response()->json(['success' => false, 'products' => []]);
        }

        return response()->json(['success' => true, 'products' => $products]);
    }
    public function expired(Request $request)
    {
        if ($request->ajax()) {
            try {
                Log::info('Expired method called', ['request' => $request->all()]);

                $now = Carbon::now();
                $nearExpiryDate = $now->copy()->addDays(7);

                $purchases = Purchase::whereDate('expiry_date', '<', $now)
                                     ->orWhere(function ($query) use ($now, $nearExpiryDate) {
                                         $query->whereDate('expiry_date', '>=', $now)
                                               ->whereDate('expiry_date', '<=', $nearExpiryDate);
                                     })
                                     ->with(['barCodeData', 'category', 'supplier'])
                                     ->get();

                $dataTable = DataTables::of($purchases)
                    ->addColumn('product', function ($purchase) {
                        $product = $purchase->barCodeData;
                        if ($product) {
                            $imageUrl = $product->image ? asset("storage/purchases/" . $product->image) : asset("default-image.png");
                            return $product->product_name . ' <img class="avatar-img" src="' . $imageUrl . '" alt="product" style="max-width: 50px;">';
                        }
                        return 'No Product';
                    })
                    ->addColumn('category', function ($purchase) {
                        return $purchase->category ? $purchase->category->name : 'No Category';
                    })
                    ->addColumn('supplier', function ($purchase) {
                        return $purchase->supplier ? $purchase->supplier->name : 'No Supplier';
                    })
                    ->addColumn('quantity', function ($purchase) {
                        return $purchase->quantity;
                    })
                    ->addColumn('expiry_date', function ($purchase) {
                        return date('d M, Y', strtotime($purchase->expiry_date));
                    })
                    ->rawColumns(['product'])
                    ->make(true);

                Log::info('Expired method response', ['response' => $dataTable->toJson()]);

                return $dataTable;
            } catch (\Exception $e) {
                Log::error('Error in expired method', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('admin.products.expired');
    }

    public function reports()
    {
        $purchases = Purchase::with(['barCodeData', 'category', 'supplier'])->get();
        return view('admin.purchases.reports', compact('purchases'));
    }
    
    
    public function generateReport(Request $request)
    {
        // Fetch purchases based on the selected date range
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
    
        $purchases = Purchase::with(['category', 'supplier', 'barCodeData'])
            ->whereBetween('created_at', [$from_date, $to_date])
            ->get();
    
        return view('admin.purchases.reports', compact('purchases'));
    }
    
    public function updateStockStatus($barCodeId)
    {
        // Calculate the total stock for the product by summing quantities from all associated purchases
        $totalStock = Purchase::where('bar_code_id', $barCodeId)
            ->sum('quantity'); // Sum the quantity of all purchases for the product

        // Fetch the product from the bar_code_data table
        $product = BarCodeData::find($barCodeId);

        if ($product) {
            // Update the product's in_stock status
            $product->in_stock = $totalStock > 0 ? 1 : 0; // Ensure it's set to 1 or 0
            $product->save();

            // If stock is 5 or below, create a notification
            if ($totalStock <= 5) {
                // Notify users with the 'super-admin' role
                $admins = User::role('super-admin')->get();
                Notification::send($admins, new LowStockNotification($product, $totalStock));
            }
        }
    }

    
    public function checkAndUpdateStockStatus($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            return;
        }

        // Check if the quantity is 0 or low stock
        if ($purchase->quantity <= 0) {
            // Update stock status if needed (e.g., mark as out of stock)
            BarCodeData::where('id', $purchase->bar_code_id)
                ->update(['in_stock' => 0]); // Make sure 'in_stock' exists as a column if using it
        } else {
            // Mark as in stock if quantity is above 0
            BarCodeData::where('id', $purchase->bar_code_id)
                ->update(['in_stock' => 1]);
        }
    }

    public function testPillAmount($productId)
    {
        $product = BarCodeData::find($productId);

        if ($product) {
            return response()->json([
                'success' => true,
                'product_name' => $product->product_name,
                'pill_amount' => $product->pill_amount,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
    }
    public function viewByInvoice($invoice_no)
    {
        $purchases = Purchase::where('invoice_no', $invoice_no)->get();
        return view('admin.purchases.view', compact('purchases'));
    }
}

