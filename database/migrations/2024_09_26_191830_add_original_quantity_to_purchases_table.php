<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalQuantityToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('original_quantity')->nullable()->after('quantity');
        });
    }
    
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('original_quantity');
        });
    }
    
}
