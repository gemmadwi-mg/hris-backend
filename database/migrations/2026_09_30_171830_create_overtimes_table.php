<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->string('overtime_id', 6)->primary();
            
            // Relasi ke karyawan yang lembur dan data absensi hariannya
            $table->string('employee_id', 5);
            $table->string('attendance_id', 20);
            
            $table->date('overtime_date');
            $table->time('duration'); // Disimpan dalam format HH:MM:SS
            
            // Tambahkan baris ini:
            $table->double('total_incentive')->nullable()->comment('Total insentif dalam rupiah');
            
            $table->text('task_description');
            
            // Dokumen pendukung (Misal: Surat Perintah Lembur / SPKL)
            $table->string('attachment', 100)->nullable(); 
            
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('rejection_reason')->nullable();
            
            // Relasi ke HR/Manager yang menyetujui
            $table->string('approver_id', 5)->nullable();
            $table->dateTime('processed_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Constraint Relasi
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('attendance_id')->references('attendance_id')->on('attendances')->onDelete('cascade');
            $table->foreign('approver_id')->references('employee_id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};