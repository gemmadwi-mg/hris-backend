<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->string('leave_request_id', 6)->primary();

            // Relasi ke Karyawan yang mengajukan cuti
            $table->string('employee_id', 5);

            $table->string('leave_type', 50); // Contoh: Annual Leave, Sick Leave, Maternity
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason'); // Keterangan/Alasan cuti

            // Path dokumen pendukung (misal: Surat Dokter)
            $table->string('attachment', 100)->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('rejection_reason')->nullable();

            // Relasi ke Karyawan (HR/Manager) yang menyetujui
            // Kolom yang sudah diperbarui
            $table->string('approver_id', 5)->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Constraint Relasi
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');

            // Gunakan 'set null' agar data cuti tidak hilang jika HR yang menyetujui suatu saat di-delete (resign)
            $table->foreign('approver_id')->references('employee_id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
