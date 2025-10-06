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
        Schema::create('nets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pond_id')->constrained()->onDelete('cascade');
            $table->string('identifier'); // e.g. Net A, Net B
            $table->integer('quantity')->default(0); // bilang ng fingerlings
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nets');
    }
};
