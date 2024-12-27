
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
            $table->bigIncrements('id');
            $table->string('product_name', 191)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('image', 191)->charset('utf8mb3')->collation('utf8mb3_unicode_ci')->nullable();
            $table->string('bar_code', 191)->charset('utf8mb3')->collation('utf8mb3_unicode_ci')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->decimal('discount', 8, 2)->default('0.00');
            $table->decimal('price', 8, 2)->nullable();
            $table->text('description')->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('shelf', 191)->charset('utf8mb3')->collation('utf8mb3_unicode_ci')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('cost_price', 10, 2)->default('0.00');
            $table->tinyInteger('in_stock')->default(1);
            $table->timestamp('stock_notified_at')->nullable();
            $table->integer('pill_amount')->nullable();
            $table->decimal('price_per_pill', 10, 2)->storedAs('if((`pill_amount` > 0),(`cost_price` / `pill_amount`),NULL)');
            $table->tinyInteger('sale_by_pill')->default(0);
            $table->primary('id');
            $table->unique('bar_code');
            $table->index('purchase_id');
            $table->index('supplier_id');
            $table->index('category_id');
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