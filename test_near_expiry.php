<?php

use App\Http\Controllers\Admin\PurchaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$controller = new PurchaseController();

// Create a fake request to simulate an AJAX request
$request = Request::create('/test-near-expiry', 'GET', [], [], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

// Call the expired method
$response = $controller->expired($request);

// Log the response
Log::info('Test Near Expiry Response', ['response' => $response->getContent()]);

echo "Test Near Expiry Response logged successfully.\n";
