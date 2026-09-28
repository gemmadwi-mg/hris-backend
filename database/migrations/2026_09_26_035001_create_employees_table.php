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
            // Relasi ke akun login (User) dan jabatan
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('position_id')->constrained('positions')->cascadeOnDelete();

            $table->string('nip')->unique(); // Nomor Induk Pegawai
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('nomor_telepon')->nullable();
            $table->date('tanggal_bergabung');

            // Data Pembayaran
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
