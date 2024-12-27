<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductFieldsToBarCodeDataTable extends Migration
{
    public function up()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            // Add the fields that were in the products table
            $table->decimal('price', 8, 2)->nullable();        // Price field from products table
            $table->decimal('discount', 8, 2)->default(0);     // Discount field from products table
            $table->text('description')->nullable();           // Description field from products table
            // If you need a purchase relationship, you can add it like this
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->dropColumn(['price', 'discount', 'description', 'purchase_id']);
        });
    }
}
