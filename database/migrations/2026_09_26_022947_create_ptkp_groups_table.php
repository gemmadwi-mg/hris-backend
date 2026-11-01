<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptkp_groups', function (Blueprint $table) {
            $table->string('ptkp_group_id', 3)->primary(); // Contoh: G01
            $table->string('ptkp_group_name', 10)->unique(); // Contoh: TK/0, K/1
            $table->string('description', 50);
            $table->bigInteger('annual_amount'); // Ptkp Tahunan

            // Foreign key merujuk ke tabel ter_categories
            $table->string('ter_category_id', 3);
            $table->foreign('ter_category_id')
                  ->references('ter_category_id')
                  ->on('ter_categories')
                  ->onDelete('restrict'); // Cegah penghapusan kategori TER jika masih dipakai

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptkp_groups');
    }
};