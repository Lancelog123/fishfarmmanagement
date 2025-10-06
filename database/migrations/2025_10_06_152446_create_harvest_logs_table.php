<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pond_id')->constrained('ponds')->onDelete('cascade');
            $table->enum('type', ['grow_out', 'fingerlings', 'breeders']);
            $table->string('species', 100);
            $table->decimal('size_inch', 5, 2)->nullable();
            $table->integer('fish_qty');
            $table->decimal('price_per_unit', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('buyer_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_logs');
    }
};
