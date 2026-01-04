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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('training_program_id');
            $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
            $table->date('enrollment_date');
            $table->string('payment_method')->default('Bank Transfer')->after('enrollment_date');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->enum('payment_status', ['Pending', 'Paid', 'Failed'])->default('Pending')->after('payment_reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
