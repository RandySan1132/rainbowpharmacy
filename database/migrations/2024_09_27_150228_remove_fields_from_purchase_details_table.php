<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsFromPurchaseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropColumn(['medicine_image', 'supplier_price', 'box_qty', 'expiry_date']);
        });
    }
    
    public function down()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->string('medicine_image')->nullable();
            $table->decimal('supplier_price', 10, 2);
            $table->integer('box_qty');
            $table->date('expiry_date');
        });
    }
    
}
