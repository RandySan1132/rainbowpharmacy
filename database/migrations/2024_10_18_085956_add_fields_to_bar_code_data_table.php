<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToBarCodeDataTable extends Migration
{
    public function up()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->string('shelf')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['price', 'description', 'purchase_id', 'supplier_id', 'shelf', 'category_id']);
        });
    }
}
