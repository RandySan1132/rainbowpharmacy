public function up()
{
    Schema::create('receipts', function (Blueprint $table) {
        $table->id();
        $table->string('invoice_id');
        $table->date('date');
        $table->decimal('total_amount', 10, 2);
        $table->text('receipt_details');
        $table->unsignedBigInteger('sale_id')->nullable(); // Make sale_id nullable
        $table->timestamps();
    });
}
