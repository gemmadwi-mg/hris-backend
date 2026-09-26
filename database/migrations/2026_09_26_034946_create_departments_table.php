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
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID Primary Key
            $table->string('nama_departemen');
            $table->string('kode_departemen')->unique(); // contoh: IT, HRD, FIN
            $table->timestamps();
            $table->softDeletes(); // Standar Enterprise: Data tidak benar-benar dihapus dari DB
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
