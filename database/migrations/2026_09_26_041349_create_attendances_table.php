<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->string('attendance_id', 20)->primary();
            
            // Foreign Keys
            $table->string('employee_id', 5);
            $table->string('branch_id', 5);
            
            $table->date('date');
            
            // Buat nullable agar aman jika terjadi skenario Clock Out tanpa Clock In
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            
            // Status Kedatangan & Kepulangan
            $table->enum('clock_in_status', ['On Time', 'Late'])->nullable();
            $table->enum('clock_out_status', ['On Time', 'Early Depart'])->nullable();
            
            // Durasi keterlambatan/pulang cepat (HH:MM:SS)
            $table->time('late_duration')->nullable();
            $table->time('early_leave_duration')->nullable();
            
            // Lokasi GPS (Dibuat nullable)
            $table->string('clock_in_coordinates', 50)->nullable();
            $table->string('clock_out_coordinates', 50)->nullable();
            
            // Path file foto selfie (Dibuat nullable)
            $table->string('clock_in_photo', 100)->nullable();
            $table->string('clock_out_photo', 100)->nullable();
            
            // Status Absensi Harian
            $table->enum('attendance_status', [
                'Present',   // Hadir
                'Invalid',   // Tidak Tepat/Anomali
                'Absent',    // Alfa
                'Permit',    // Izin
                'Leave'      // Cuti
            ]);
            
            $table->softDeletes();
            $table->timestamps();

            // Constraint Relasi
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};