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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('contact_number');
            $table->string('email')->unique();
            $table->string('address');
            $table->date('date_of_birth')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('previous_experience')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('program_duration')->default('1 year');
            $table->enum('current_skill_level', ['Beginner', 'Intermediate', 'Advanced']);
            $table->text('goals')->nullable();
            $table->string('id_proof')->nullable();
            $table->string('resume')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
