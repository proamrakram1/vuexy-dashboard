<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_code')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('offer_id')->constrained('offers');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('real_estate_id')->constrained('real_estates');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('bank_id')->nullable()->constrained('banks');

            #Pricing
            $table->string('check_number')->nullable();
            $table->integer('customer_buyer_id');
            $table->integer('customer_seller_id');

            $table->double('vat');
            $table->double('saee_prc')->nullable();
            $table->double('saee_price')->nullable();
            $table->double('tatal_req_amount');
            $table->double('paid_amount');
            $table->double('deserved_amount')->nullable();

            $table->enum('sale_status', [1, 2]);
            $table->enum('is_first_home', [1, 2]);

            $table->string('who_receive')->nullable();
            $table->integer('who_add')->nullable();
            $table->integer('who_edit')->nullable();
            $table->integer('who_cancel')->nullable();

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
        Schema::dropIfExists('sales');
    }
};
