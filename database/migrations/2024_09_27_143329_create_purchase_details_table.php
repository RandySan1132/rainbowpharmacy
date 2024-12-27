<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id'); // Foreign key to purchases table
            $table->string('bar_code');
            $table->date('expiry_date'); // Add this field if it's required here
            $table->integer('box_qty');
            $table->decimal('supplier_price', 10, 2);
            $table->decimal('total_purchase_price', 10, 2);
            $table->string('medicine_image')->nullable();
            $table->timestamps();
        
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_details');
    }
}
