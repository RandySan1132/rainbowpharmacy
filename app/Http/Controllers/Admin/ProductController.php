<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Category;  // Import Category model
use App\Models\Supplier;  // Import Supplier model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;
use App\Models\BarCodeData;  // Ensure BarCodeData model is imported
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB; // <-- Add this line
use Spatie\Permission\Models\Role; // Ensure you have this import at the top
use App\Services\TelegramService; // Ensure you have this import at the top

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    $title = 'products';
    if ($request->ajax()) {
        Log::info("AJAX request received for products index");

        $products = BarCodeData::with(['supplier', 'category', 'purchase'])
            ->leftJoin('categories', 'bar_code_data.category_id', '=', 'categories.id')
            ->leftJoin('suppliers', 'bar_code_data.supplier_id', '=', 'suppliers.id')
            ->select('bar_code_data.*', 'categories.name as category_name', 'suppliers.name as supplier_name');

        // Apply ordering based on DataTables request
        if ($request->has('order')) {
            $order = $request->order[0];
            $columns = $request->columns;
            $column = $columns[$order['column']]['name'];
            $dir = $order['dir'];

            Log::info("Ordering column: $column, direction: $dir");

            // Verify if column exists in BarCodeData or related models
            if (in_array($column, ['product_name', 'shelf', 'cost_price', 'price', 'discount'])) {
                $products->orderBy("bar_code_data.$column", $dir);
            } elseif ($column === 'category_name') {
                $products->orderBy('categories.name', $dir);
            } elseif ($column === 'supplier_name') {
                $products->orderBy('suppliers.name', $dir);
            }
        } else {
            Log::info("No specific ordering, defaulting to product_name ascending");
            $products->orderBy('bar_code_data.product_name', 'asc');
        }

        // Apply search filter
        if ($request->has('search') && $request->search['value'] != '') {
            $search = strtolower($request->search['value']);
            Log::info("Applying search filter with value: $search");

            $products->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(bar_code_data.product_name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(categories.name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(suppliers.name) LIKE ?', ["%{$search}%"]);
            });
        }

        // Log the generated SQL query before execution
        Log::info("Generated query: " . $products->toSql());

        try {
            $dataTable = DataTables::of($products)
                ->addColumn('product', function ($product) {
                    $image = $product->image 
                        ? '<img class="avatar-img" src="' . asset("storage/purchases/" . $product->image) . '" alt="product" style="width: 40px;">' 
                        : '';
                    return $product->product_name . ' ' . $image;
                })
                ->addColumn('category', fn($product) => $product->category_name ?? 'No Category')
                ->addColumn('supplier', fn($product) => $product->supplier_name ?? 'No Supplier')
                ->addColumn('shelf', fn($product) => $product->shelf)
                ->addColumn('cost_price', fn($product) => settings('app_currency', '$') . ' ' . $product->cost_price)
                ->addColumn('price', fn($product) => settings('app_currency', '$') . ' ' . $product->price)
                ->addColumn('discount', fn($product) => $product->discount . '%')
                ->addColumn('action', function ($row) {
                    return '<a href="' . route("products.edit", $row->id) . '" class="editbtn">
                                <button class="btn btn-info"><i class="fas fa-edit"></i></button>
                            </a>
                            <a data-id="' . $row->id . '" data-route="' . route('products.destroy', $row->id) . '" 
                               href="javascript:void(0)" id="deletebtn">
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </a>';
                })
                ->rawColumns(['product', 'action'])
                ->make(true);

            Log::info("DataTable generated successfully");
            return $dataTable;
        } catch (\Exception $e) {
            Log::error("Error generating DataTable: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load data.'], 500);
        }
    }

    Log::info("Returning view for products index");
    return view('admin.products.index', compact('title'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::get();
        $categories = Category::all();  // Assuming you have a Category model
        $suppliers = Supplier::all();  // Assuming you have a Supplier model
    
        return view('admin.products.create', compact(
            'title', 'purchases', 'categories', 'suppliers'
        ));
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $this->validate($request, [
        'product_name' => 'required|max:200',
        'barcode' => 'nullable|max:50',  // Barcode is now optional
        'category_id' => 'required|exists:categories,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'cost_price' => 'required|numeric|min:0',
        'price' => 'required|numeric|min:0',
        'description' => 'nullable|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'pill_amount' => 'nullable|integer|min:0', // New validation rule for pill_amount
    ]);

    $imageName = null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('storage/purchases'), $imageName);
    }

    BarCodeData::create([
        'product_name' => $request->product_name,
        'bar_code' => $request->barcode,
        'image' => $imageName,
        'price' => $request->price,
        'cost_price' => $request->cost_price,
        'description' => $request->description,
        'purchase_id' => $request->purchase_id,
        'supplier_id' => $request->supplier_id,
        'shelf' => $request->shelf,
        'category_id' => $request->category_id,
        'discount' => $request->discount,
        'pill_amount' => $request->pill_amount, // New field
    ]);


    // Notify and redirect
    $notification = notify("Product has been added");
    return redirect()->route('products.index')->with($notification);

}

    
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
{
    $title = 'Edit Product';
    $product = BarCodeData::findOrFail($id);
    $categories = Category::all();
    $suppliers = Supplier::all();
    $medicineCategoryId = Category::where('name', 'Medicine')->first()->id;

    return view('admin.products.edit', compact('title', 'product', 'categories', 'suppliers', 'medicineCategoryId'));
}



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    $this->validate($request, [
        'product_name' => 'required|max:200',
        'bar_code' => 'nullable|max:50',
        'shelf' => 'nullable|max:191',
        'category_id' => 'required|exists:categories,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'price' => 'required|numeric|min:1',
        'cost_price' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric',
        'description' => 'nullable|max:255',
        'pill_amount' => 'nullable|integer|min:0', // New validation rule for pill_amount
    ]);

    $product = BarCodeData::findOrFail($id);

    $product->update([
        'product_name' => $request->product_name,
        'bar_code' => $request->bar_code,
        'shelf' => $request->shelf,
        'category_id' => $request->category_id,
        'supplier_id' => $request->supplier_id,
        'price' => $request->price,
        'cost_price' => $request->cost_price,
        'discount' => $request->discount,
        'description' => $request->description,
        'pill_amount' => $request->pill_amount, // New field
    ]);

    return redirect()->route('products.index')->with('success', 'Product updated successfully.');
}

    


     /**
     * Display a listing of expired resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request)
{
    if ($request->ajax()) {
        $purchases = Purchase::where(function ($query) {
            $query->where('expiry_date', '<', now())  // Expired products
                  ->orWhere('near_expiry_date', 'Yes');  // Near expiry products
        })
        ->with(['barCodeData', 'category', 'supplier'])
        ->get();

        return DataTables::of($purchases)
            ->addColumn('product', function ($purchase) {
                return $purchase->barCodeData ? $purchase->barCodeData->product_name : 'No Product';
            })
            ->addColumn('category', function ($purchase) {
                return $purchase->category ? $purchase->category->name : 'No Category';
            })
            ->addColumn('supplier', function ($purchase) {
                return $purchase->supplier ? $purchase->supplier->name : 'No Supplier';
            })
            ->addColumn('price', function ($purchase) {
                return settings('app_currency', '$') . ' ' . $purchase->cost_price;
            })
            ->addColumn('quantity', function ($purchase) {
                return $purchase->quantity;
            })
            ->addColumn('expiry_date', function ($purchase) {
                return date('d M, Y', strtotime($purchase->expiry_date));
            })
            ->rawColumns(['product'])
            ->make(true);
    }

    return view('admin.products.expired');
}

    

    /**
     * Display a listing of out of stock resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function outstock(Request $request)
{
    $title = "Out of Stock Products";

    if ($request->ajax()) {
        try {
            // Fetch products where the total quantity across all purchases is zero
            $outOfStockProducts = Purchase::select('bar_code_id', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('bar_code_id')
                ->havingRaw('SUM(quantity) = 0') // Only include products with a total quantity of zero
                ->with(['barCodeData', 'barCodeData.supplier']) // Ensure supplier relationship is loaded
                ->get();

            return DataTables::of($outOfStockProducts)
                ->addColumn('product', fn($purchase) => $purchase->barCodeData->product_name ?? 'No Product')
                ->addColumn('supplier', fn($purchase) => $purchase->barCodeData->supplier->name ?? 'No Supplier') // Correctly fetch supplier name
                ->addColumn('quantity', fn($purchase) => $purchase->total_quantity ?? 0)
                ->make(true);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('DataTables outstock error: ' . $e->getMessage());
            
            // Return a JSON response with error info
            return response()->json([
                'error' => 'An error occurred while loading data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    return view('admin.products.outstock', compact('title'));
}

    
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
{
    try {
        $product = BarCodeData::findOrFail($request->id);

        // Optional: Check if there are purchases linked to this product
        $linkedPurchases = Purchase::where('bar_code_id', $product->id)->exists();

        if ($linkedPurchases) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this product as it is linked to purchases.'
            ], 400);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()], 500);
    }
}

public function updateStockStatus($barCodeId)
{
    // Calculate the total stock for the product by summing quantities from all associated purchases
    $totalStock = Purchase::where('bar_code_id', $barCodeId)->sum('quantity');

    // Fetch the product from the bar_code_data table
    $product = BarCodeData::find($barCodeId);

    if ($product) {
        // Update the product's in_stock status
        $product->in_stock = $totalStock > 0;
        $product->save();

        // Log stock status
        Log::info("Stock status for product {$product->product_name}: {$totalStock}");

        // If stock is 5 or below and notification has not been sent recently
        if ($totalStock <= 5) {
            $shouldNotify = is_null($product->stock_notified_at) ||
                            $product->stock_notified_at->diffInMinutes(now()) > 60;

            if ($shouldNotify) {
                // Notify all super-admin users
                $admins = User::role('super-admin')->get(); // Use the existing super-admin role
                Notification::send($admins, new LowStockNotification($product, $totalStock));

                // Update the notification timestamp to prevent duplicate notifications
                $product->stock_notified_at = now();
                $product->save();

                // Log low stock notification
                Log::info("Sending low stock alert for product {$product->product_name}");
                // Send Telegram notification
                TelegramService::sendLowStockAlert($product, $totalStock);
            }
        }
    }
}




protected function checkAndUpdateStockStatus($barCodeId)
{
    // Calculate the total quantity for the product
    $totalQuantity = Purchase::where('bar_code_id', $barCodeId)->sum('quantity');

    // Update the product's in_stock status
    $product = BarCodeData::find($barCodeId);
    if ($product) {
        $product->in_stock = $totalQuantity > 0; // Mark as in stock if quantity > 0
        $product->save();

        // Log stock status
        Log::info("Stock status for product {$product->product_name}: {$totalQuantity}");

        // Notify if stock is low (5 or below)
        if ($totalQuantity > 0 && $totalQuantity <= 5) {
            Notification::send(auth()->user(), new LowStockNotification($product, $totalQuantity));

            // Log low stock notification
            Log::info("Sending low stock alert for product {$product->product_name}");
            // Send Telegram notification
            TelegramService::sendLowStockAlert($product, $totalQuantity);
        }
    }
}

}
