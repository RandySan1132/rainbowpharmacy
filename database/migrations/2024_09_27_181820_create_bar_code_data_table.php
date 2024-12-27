<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarCodeDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bar_code_data', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');  // Product name from purchases table
            $table->string('image')->nullable();  // Image from purchases table
            $table->string('bar_code')->unique();  // Bar code from purchase_details table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bar_code_data');
    }
}
