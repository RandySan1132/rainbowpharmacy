<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController; // Ensure this import is present
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ReceiptController; // Ensure this import is present
use App\Services\TelegramService;
use App\Models\BarCodeData;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->prefix('admin')->group(function(){
    Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('',[DashboardController::class,'Index']);
    Route::get('notification',[NotificationController::class,'markAsRead'])->name('mark-as-read');
    Route::get('/mark-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-as-read');

    Route::get('notification-read',[NotificationController::class,'read'])->name('read');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'read'])->name('read');
    Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');
    Route::post('logout',[LogoutController::class,'index'])->name('logout');

    Route::resource('users',UserController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','destroy']);
    Route::put('permission',[PermissionController::class,'update'])->name('permissions.update');
    Route::resource('roles',RoleController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class)->only(['index','store','destroy']);
    Route::put('categories',[CategoryController::class,'update'])->name('categories.update');
    Route::resource('purchases', PurchaseController::class)->except('show');
    Route::get('purchases/reports', [PurchaseController::class, 'reports'])->name('purchases.report');
    Route::post('purchases/reports', [PurchaseController::class, 'generateReport'])->name('purchases.generateReport');
    Route::get('/admin/purchases', [PurchaseController::class, 'index'])->name('admin.purchases.index');
    Route::delete('/admin/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::get('/purchases/get-barcode-data', [PurchaseController::class, 'getBarcodeData'])->name('purchases.getBarcodeData');
    Route::get('/purchases/get-supplier-products', [PurchaseController::class, 'getSupplierProducts'])->name('purchases.getSupplierProducts');
    Route::get('/expired', [PurchaseController::class, 'expired'])->name('expired');

    Route::resource('products',ProductController::class)->except('show');
    Route::get('products/outstock',[ProductController::class,'outstock'])->name('outstock');
    Route::get('products/expired',[ProductController::class,'expired'])->name('expired');
    Route::resource('sales', SaleController::class)->except(['show', 'edit', 'destroy']);
    Route::get('sales/reports',[SaleController::class,'reports'])->name('sales.report');
    Route::post('sales/reports',[SaleController::class,'generateReport']);
    Route::post('/sales/store', [App\Http\Controllers\Admin\SaleController::class, 'store'])->name('sales.store');

    // Ensure routes use 'id' as the parameter
    Route::get('sales/edit/{id}', [SaleController::class, 'edit'])->name('sales.edit');
    Route::get('sales/view/{id}', [SaleController::class, 'view'])->name('sales.view');
    Route::delete('sales/{id}', [SaleController::class, 'destroy'])->name('sales.destroy');

    Route::get('backup', [BackupController::class,'index'])->name('backup.index');
    Route::put('backup/create', [BackupController::class,'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}', [BackupController::class,'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}', [BackupController::class,'destroy'])->where('file_name', '(.*)')->name('backup.destroy');
    Route::post('backup/export', [BackupController::class, 'export'])->name('backup.export');
    Route::get('backup/manual-export', [BackupController::class, 'manualExport'])->name('backup.manualExport');
    Route::post('backup/import', [BackupController::class, 'import'])->name('backup.import');
    
    Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    Route::get('settings',[SettingController::class,'index'])->name('settings');
    Route::put('/settings', [SettingController::class, 'update'])->name('app_settings.update');

    Route::get('/reload-notifications', [App\Http\Controllers\Admin\NotificationController::class, 'reloadNotifications'])->name('reload-notifications');

    Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');

    Route::get('/admin/receipt/print/{invoiceId}', function($invoiceId) {
        Log::info('Route accessed with invoiceId: ' . $invoiceId);
        return app()->call('App\Http\Controllers\Admin\ReceiptController@printReceipt', ['invoiceId' => $invoiceId]);
    })->name('receipt.print');

    Route::get('receipt/print/{invoiceId}', [ReceiptController::class, 'printReceipt'])->name('receipt.print');

    Route::get('/admin/sales/print-receipt/{invoiceId}', [ReceiptController::class, 'printReceipt'])->name('admin.sales.print-receipt');

    Route::get('sales/bare-receipt/{id}', [ReceiptController::class, 'bareReceipt'])->name('admin.sales.bareReceipt');

    Route::post('admin/clear-log', [SettingController::class, 'clearLog'])->name('clear.log');

    Route::get('backup/storage', [App\Http\Controllers\Admin\BackupController::class, 'backupStorage'])->name('backup.storage');
});

// Add the test route outside the auth middleware group
Route::get('/test-receipt/{sale_id}', [ReceiptController::class, 'testReceipt'])->name('test.receipt');

Route::get('/test-telegram', function () {
    $product = BarCodeData::first(); // Get the first product for testing
    if ($product) {
        TelegramService::sendLowStockAlert($product, 5); // Send a low stock alert for testing
        return 'Test message sent to Telegram!';
    }
    return 'No product found for testing.';
})->name('test.telegram');

Route::middleware(['guest'])->prefix('admin')->group(function () {
    Route::get('',[DashboardController::class,'Index']);

    Route::get('login',[LoginController::class,'index'])->name('login');
    Route::post('login',[LoginController::class,'login']);

    Route::get('register',[RegisterController::class,'index'])->name('register');
    Route::post('register',[RegisterController::class,'store']);

    Route::get('forgot-password',[ForgotPasswordController::class,'index'])->name('password.request');
    Route::post('forgot-password',[ForgotPasswordController::class,'requestEmail']);
    Route::get('reset-password/{token}',[ResetPasswordController::class,'index'])->name('password.reset');
    Route::post('reset-password',[ResetPasswordController::class,'resetPassword'])->name('password.update');
});

Route::get('/', function () {
    return redirect()->route('login');
});
