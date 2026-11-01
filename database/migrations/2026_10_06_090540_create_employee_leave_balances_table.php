<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 5);
            $table->string('leave_type', 50); // e.g., 'Cuti Tahunan', 'Cuti Sakit'
            $table->year('year');             // e.g., 2026
            $table->integer('quota')->default(12); // Total jatah cuti setahun
            $table->integer('used')->default(0);  // Jumlah cuti yang sudah disetujui/terpakai
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'leave_type', 'year']); // 1 baris per jenis cuti per tahun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};