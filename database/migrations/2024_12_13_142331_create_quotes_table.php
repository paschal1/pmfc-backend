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
    $table->string('name');
    $table->string('phone');
    $table->text('message');
    $table->string('areasize');
    $table->string('location');
    $table->string('squarefeet');
    $table->string('budget');
    $table->text('service_ids');
    $table->text('service_titles');
    $table->text('service_prices');
    $table->json('details');
    $table->json('quote');
    $table->string('status')->default('pending');
    $table->timestamps();
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
