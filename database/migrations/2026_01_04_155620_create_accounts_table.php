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
          Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('account_type', ['bank', 'paypal', 'other'])->default('bank');
            $table->string('account_name'); // Display name for the account
            $table->string('account_number')->nullable(); // Bank account number
            $table->string('bank_name')->nullable(); // Bank name
            $table->string('email')->nullable(); // For PayPal or contact
            $table->text('additional_info')->nullable(); // Account holder name, SWIFT code, etc.
            $table->boolean('is_active')->default(true); // To toggle account status
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
