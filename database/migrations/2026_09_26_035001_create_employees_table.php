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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('position_id')->constrained()->restrictOnDelete();

            $table->string('nip')->unique();
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('nomor_telepon')->nullable();
            $table->date('tanggal_bergabung');
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
