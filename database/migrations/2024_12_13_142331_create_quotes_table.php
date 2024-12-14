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
            $table->string('name'); // User's name
            $table->string('email'); // User's email
            $table->unsignedBigInteger('service_id'); // Service the user is requesting
            $table->decimal('quoted_price', 10, 2); // Price of the service
            $table->text('details')->nullable(); // Additional user details
            $table->timestamps();

            // Foreign key to services table
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
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
