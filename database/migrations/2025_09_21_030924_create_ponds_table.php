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
        Schema::table('ponds', function (Blueprint $table) {
            // Change the 'type' column to have the new enum options
            $table->enum('type', ['growout', 'fingerlings', 'breeders'])->default('growout')->change();

            // Add a new 'category' column
            $table->enum('category', ['mud', 'concrete'])->default('mud')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ponds', function (Blueprint $table) {
            // Revert the 'type' column back to old enum options if needed
            $table->enum('type', ['grow_out', 'fingerlings', 'breeders'])->default('grow_out')->change();

            // Drop the 'category' column
            $table->dropColumn('category');
        });
    }
};
