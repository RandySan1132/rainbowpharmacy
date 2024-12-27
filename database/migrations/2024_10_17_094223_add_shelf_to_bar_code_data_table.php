<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShelfToBarCodeDataTable extends Migration
{
    public function up()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->string('shelf')->nullable();  // Adding the shelf column
        });
    }

    public function down()
    {
        Schema::table('bar_code_data', function (Blueprint $table) {
            $table->dropColumn('shelf');
        });
    }
}
