<?php

use App\Models\Purchase;
use App\Models\User;
use App\Notifications\ExpiredNotification;
use App\Notifications\NearlyExpiredNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

while (true) {
    $now = Carbon::now()->startOfDay();

    // Fetch expired purchases
    $expiredPurchases = Purchase::whereDate('expiry_date', '<', $now)->get();

    // Fetch nearly expired purchases using near_expiry_date
    $nearlyExpiredPurchases = Purchase::whereDate('near_expiry_date', '<=', $now)
                                      ->whereDate('near_expiry_date', '>=', $now->copy()->subDays(7))
                                      ->get();

    $users = User::all();

    foreach ($expiredPurchases as $purchase) {
        Notification::send($users, new ExpiredNotification($purchase));
    }

    foreach ($nearlyExpiredPurchases as $purchase) {
        Notification::send($users, new NearlyExpiredNotification($purchase));
    }

    sleep(3600); // Wait for 1 hour before checking again
}
