<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ter_rates', function (Blueprint $table) {
            // Primary key kustom (R001, R002, dst.)
            $table->string('ter_rate_id', 4)->primary();

            // Foreign key merujuk ke tabel ter_categories
            $table->string('ter_category_id', 3);
            $table->foreign('ter_category_id')
                  ->references('ter_category_id')
                  ->on('ter_categories')
                  ->onDelete('cascade'); // Jika kategori dihapus, tarifnya ikut terhapus

            $table->bigInteger('lower_limit'); // Batas bawah rentang gaji
            
            // Batas atas dibuat nullable() karena rentang tertinggi biasanya berbunyi "> Rp X" (tidak ada batas atas)
            $table->bigInteger('upper_limit')->nullable(); 
            
            $table->double('rate'); // Persentase tarif (misal: 0.25 untuk 0,25%, atau 5 untuk 5%)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_rates');
    }
};