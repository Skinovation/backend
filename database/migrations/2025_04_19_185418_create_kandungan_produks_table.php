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
        Schema::create('kandungan_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produks_id')->constrained('produks')->onDelete('cascade');
            $table->foreignId('kandungans_id')->constrained('kandungans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kandungan_produks');
    }
};
