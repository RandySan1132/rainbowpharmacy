<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use App\Models\User;
use App\Notifications\ExpiredNotification;
use App\Notifications\NearlyExpiredNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class CheckExpiredPurchases extends Command
{
    protected $signature = 'check:expired-purchases';
    protected $description = 'Check for expired and nearly expired purchases and notify users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now()->startOfDay();
        $nearlyExpiredDate = $now->copy()->addDays(7)->endOfDay();

        Log::info('Checking expired and nearly expired purchases', [
            'now' => $now->toDateString(),
            'nearly_expired_date' => $nearlyExpiredDate->toDateString(),
        ]);

        // Fetch expired purchases
        $expiredPurchases = Purchase::whereDate('expiry_date', '<', $now)->get();

        // Fetch nearly expired purchases using near_expiry_date
        $nearlyExpiredPurchases = Purchase::whereDate('near_expiry_date', '<=', $now)
                                          ->whereDate('near_expiry_date', '>=', $now->copy()->subDays(7))
                                          ->get();

        $users = User::all(); // Get all users to notify

        foreach ($expiredPurchases as $purchase) {
            foreach ($users as $user) {
                $existingNotification = DB::table('notifications')
                    ->where('type', ExpiredNotification::class)
                    ->where('notifiable_id', $user->id)
                    ->where('data->purchase_id', $purchase->id)
                    ->first();

                if (!$existingNotification) {
                    $user->notify(new ExpiredNotification($purchase));
                    Log::info('Expired notification sent', [
                        'user_id' => $user->id,
                        'purchase_id' => $purchase->id,
                    ]);
                }
            }
        }

        foreach ($nearlyExpiredPurchases as $purchase) {
            foreach ($users as $user) {
                $existingNotification = DB::table('notifications')
                    ->where('type', NearlyExpiredNotification::class)
                    ->where('notifiable_id', $user->id)
                    ->where('data->purchase_id', $purchase->id)
                    ->first();

                if (!$existingNotification) {
                    $user->notify(new NearlyExpiredNotification($purchase));
                    Log::info('Nearly expired notification sent', [
                        'user_id' => $user->id,
                        'purchase_id' => $purchase->id,
                    ]);
                }
            }
        }

        // Remove expired notifications for purchases no longer expired
        // Change from fetching purchases with expiry_date >= now to expiry_date < now
        $expiredPurchaseIds = Purchase::whereDate('expiry_date', '<', $now)->pluck('id');
        Log::info('Expired purchase IDs', ['expired_purchases' => $expiredPurchaseIds]);

        DatabaseNotification::where('type', ExpiredNotification::class)
            ->whereNotIn('data->purchase_id', $expiredPurchaseIds)
            ->delete();

        // Remove nearly expired notifications for purchases no longer nearly expired
        $activeNearlyExpiredPurchases = Purchase::whereDate('near_expiry_date', '>=', $now->copy()->subDays(7))->pluck('id');
        Log::info('Active nearly expired purchases', ['active_nearly_expired_purchases' => $activeNearlyExpiredPurchases]);

        DatabaseNotification::where('type', NearlyExpiredNotification::class)
            ->whereNotIn('data->purchase_id', $activeNearlyExpiredPurchases)
            ->delete();

        $this->info('Expired and nearly expired purchases have been checked and notifications updated.');
    }
}
