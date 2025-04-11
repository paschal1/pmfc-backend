<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('total_price', 10, 2);
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('payment_method')->default('Bank Transfer'); // Bank Transfer, Credit Card, PayPal
            $table->string('payment_type')->default('Full Payment'); // Full Payment, Deposit
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            $table->string('payment_status')->default('Unpaid'); // Unpaid, Paid
            $table->string('tracking_number')->unique();
            $table->enum('status', [
                'order_processing', 
                'pre_production', 
                'in_production', 
                'shipped', 
                'delivered',
                'canceled'
            ])->default('order_processing');
            $table->string('transaction_id')->nullable();
            $table->date('order_date');
            $table->text('shipping_address');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
