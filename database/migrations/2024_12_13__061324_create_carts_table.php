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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Define user_id column
            $table->foreign('user_id') // Add foreign key constraint
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->string('session_id')->nullable(); // For guest users
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'abandoned', 'checked_out'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
