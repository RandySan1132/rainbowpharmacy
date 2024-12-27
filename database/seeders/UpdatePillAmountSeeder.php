
<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePillAmountSeeder extends Seeder
{
    public function run()
    {
        DB::table('purchases')
            ->whereNull('pill_amount')
            ->update(['pill_amount' => 0]);

        DB::table('purchases')
            ->whereNull('original_pill_amount')
            ->update(['original_pill_amount' => 0]);
    }
}