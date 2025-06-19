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
        Schema::table('quotes', function (Blueprint $table) {
            // Remove the 'location' column if it exists
            if (Schema::hasColumn('quotes', 'location')) {
                $table->dropColumn('location');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('quotes', 'state_id')) {
                $table->foreignId('state_id')->nullable()->constrained()->onDelete('set null')->after('message');
            }

            if (!Schema::hasColumn('quotes', 'address')) {
                $table->string('address')->nullable()->after('state_id');
            }

            if (!Schema::hasColumn('quotes', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Add back the 'location' column
            if (!Schema::hasColumn('quotes', 'location')) {
                $table->string('location')->nullable()->after('message');
            }

            // Drop the newly added columns
            if (Schema::hasColumn('quotes', 'total_price')) {
                $table->dropColumn('total_price');
            }

            if (Schema::hasColumn('quotes', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('quotes', 'state_id')) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            }
        });
    }
};
