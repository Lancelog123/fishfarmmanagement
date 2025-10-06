<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');           // worker who did the transfer
            $table->unsignedBigInteger('from_pond_id');      // breeder pond source
            $table->unsignedBigInteger('to_net_id');         // target net
            $table->integer('quantity');                     // number of fingerlings
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_pond_id')->references('id')->on('ponds')->onDelete('cascade');
            $table->foreign('to_net_id')->references('id')->on('nets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_logs');
    }
};
