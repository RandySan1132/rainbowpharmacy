<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInStockToBarCodeDataTable extends Migration
{
    public function up()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->boolean('in_stock')->default(1); // Default to 1, meaning in stock
        });
    }

    public function down()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }
}
