<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockNotifiedAtToBarCodeDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->timestamp('stock_notified_at')->nullable()->after('in_stock');
        });
    }
    
    public function down()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->dropColumn('stock_notified_at');
        });
    }
    
}
