<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Auth; // Ensure you have this import at the top
use App\Notifications\LowStockNotification; // Ensure you have this import at the top
use App\Notifications\ExpiredNotification;
use App\Notifications\NearlyExpiredNotification;
use Illuminate\Notifications\DatabaseNotification;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $title = 'dashboard';
        $total_purchases = Purchase::count();
        $total_suppliers = Supplier::count();
        $total_sales = Sale::count();
        
        // Correct query to count out-of-stock products based on total quantity across purchases
        $out_of_stock = Purchase::select('bar_code_id')
            ->groupBy('bar_code_id')
            ->havingRaw('SUM(quantity) <= 0')
            ->count();

        // Configure the pie chart
        $pieChart = app()->chartjs
            ->name('pieChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Total Purchases', 'Total Sales'])
            ->datasets([
                [
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#7bb13c'],
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB', '#7bb13c'],
                    'data' => [$total_purchases, $total_sales]
                ]
            ])
            ->options([]);

        // Calculate other statistics
        $total_expired_products = Purchase::whereDate('expiry_date', '<', Carbon::now())->count();
        $latest_sales = Sale::whereDate('created_at', '=', Carbon::now())->get();
        $today_sales = Sale::whereDate('created_at', '=', Carbon::now())->sum('total_price');
        $today_purchase = Purchase::whereDate('created_at', Carbon::now())->sum('cost_price');

        // Handle month navigation
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // Use $startOfMonth and $endOfMonth instead of Carbon::now()->startOfMonth() / endOfMonth()
        $dates = [];
        $salesData = [];
        $purchaseData = [];
        for($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $salesData[] = Sale::whereDate('created_at', $date)->sum('total_price');
            $purchaseData[] = Purchase::whereDate('created_at', $date)->sum('cost_price');
            $dates[] = $date->format('d');
        }

        $barChart = app()->chartjs
            ->name('barChart')
            ->type('bar')
            ->labels($dates)
            ->datasets([
                [
                    "label" => "Daily Sales",
                    "backgroundColor" => "#36A2EB",
                    "data" => $salesData
                ],
                [
                    "label" => "Daily Purchases",
                    "backgroundColor" => "#7bb13c",
                    "data" => $purchaseData
                ]
            ])
            ->options([]);

        // Modify bestSales to filter by selected month
        $bestSales = Sale::select('bar_code_data.product_name', DB::raw('SUM(sales.quantity) as total_quantity'))
            ->join('bar_code_data', 'sales.bar_code_id', '=', 'bar_code_data.id')
            ->whereMonth('sales.created_at', $startOfMonth->month)
            ->whereYear('sales.created_at', $startOfMonth->year)
            ->groupBy('bar_code_data.product_name')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->pluck('total_quantity', 'bar_code_data.product_name');

        $bestSalesChart = app()->chartjs
            ->name('bestSalesChart')
            ->type('bar')
            ->labels($bestSales->keys()->toArray()) // Convert collection to array
            ->datasets([
                [
                    "label" => "Best Sales",
                    "backgroundColor" => "#FF6384",
                    "data" => $bestSales->values()->toArray() // Convert collection to array
                ]
            ])
            ->options([]);

        // Fetch low stock notifications
        $low_stock_notifications = DatabaseNotification::where('type', LowStockNotification::class)
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->get();

        // Fetch expired and nearly expired notifications
        $expired_notifications = DatabaseNotification::where('type', ExpiredNotification::class)
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->get();

        $nearly_expired_notifications = DatabaseNotification::where('type', NearlyExpiredNotification::class)
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->get();

        return view('admin.dashboard', compact(
            'title', 'pieChart', 'total_expired_products',
            'latest_sales', 'today_sales', 'total_purchases', 'out_of_stock', 'low_stock_notifications', 'expired_notifications', 'nearly_expired_notifications', 'today_purchase', 'barChart', 'bestSalesChart', 'currentMonth' // Pass new notifications
        ));
    }
}
