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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // worker
            $table->unsignedBigInteger('pond_id')->nullable();
            $table->unsignedBigInteger('net_id')->nullable();
            $table->string('task'); // e.g., "feeding fingerlings", "harvesting"
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pond_id')->references('id')->on('ponds')->onDelete('cascade');
            $table->foreign('net_id')->references('id')->on('nets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
