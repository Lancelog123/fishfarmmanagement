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
        Schema::create('stocking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pond_id')->constrained()->onDelete('cascade');
            $table->foreignId('net_id')->nullable()->constrained('nets')->onDelete('set null');
            $table->string('action_type'); // stock, transfer, harvest
            $table->string('species');
            $table->integer('quantity');
            $table->date('action_date');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocking_logs');
    }
};
