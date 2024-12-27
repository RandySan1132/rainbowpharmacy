<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('bar_code_id')->constrained('bar_code_data');
            $table->integer('quantity');
            $table->integer('pill_amount');
            $table->integer('leftover_pills')->default(0);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('sell_price', 15, 2);
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->decimal('cost_price', 8, 2)->nullable();
            $table->integer('original_quantity')->nullable();
            $table->string('expiry_date', 191)->nullable();
            $table->string('image', 191)->nullable();
            $table->integer('original_pill_amount')->nullable();
            $table->integer('total_pill_amount')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}