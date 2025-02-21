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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->text('service_ids')->nullable();   
            $table->text('service_titles')->nullable(); // Comma-separated service titles
            $table->text('service_prices')->nullable(); // Comma-separated service prices
            $table->json('details')->nullable();        // JSON of selected services
            $table->json('quote')->nullable();          // JSON of full quote
            $table->enum('status', ['pending', 'sent'])->default('pending');
            $table->timestamps();

            // Foreign key to services table
            //$table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
