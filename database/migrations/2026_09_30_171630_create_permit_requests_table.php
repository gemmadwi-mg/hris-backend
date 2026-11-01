<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_requests', function (Blueprint $table) {
            $table->string('permit_request_id', 6)->primary();

            // Relasi ke Karyawan yang mengajukan izin
            $table->string('employee_id', 5);

            $table->string('permit_type', 100); // Contoh: Sick, Condolence, Marriage
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description'); // Keterangan/alasan izin

            // Path dokumen (Sangat penting untuk Surat Keterangan Sakit dari Dokter)
            $table->string('attachment', 100)->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('rejection_reason')->nullable();

            // Relasi ke HR/Manager yang menyetujui
            // Kolom yang sudah diperbarui
            $table->string('approver_id', 5)->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Constraint Relasi
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');

            // Gunakan 'set null' agar jika HR resign, history persetujuan izin tidak error
            $table->foreign('approver_id')->references('employee_id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_requests');
    }
};
