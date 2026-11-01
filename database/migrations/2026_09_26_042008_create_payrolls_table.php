<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->string('payroll_id', 15)->primary(); // Panjangkan sedikit untuk amannya

            // Periode & Status
            $table->integer('period_month');
            $table->integer('period_year');
            $table->enum('status', [
                'Draft', 'Submitted', 'Verified', 'Approved', 'Rejected',
            ])->default('Draft');
            $table->text('rejection_note')->nullable();

            // Relasi Karyawan
            $table->string('employee_id', 5);
            $table->boolean('is_transferred')->default(false);

            // Komponen Pendapatan (Penambah)
            $table->decimal('basic_salary', 15, 2)->default(0);

            // --- INI YANG BENAR: Tunjangan Dipecah & Full English ---
            $table->double('position_allowance')->default(0);
            $table->double('meal_allowance')->default(0);
            $table->double('transport_allowance')->default(0);
            // --------------------------------------------------------

            $table->double('total_overtime')->default(0);
            $table->double('gross_salary')->default(0);

            // Komponen Potongan (Pengurang)
            $table->double('absence_deduction')->default(0);
            $table->double('late_deduction')->default(0);

            // --- INI YANG BENAR: BPJS Dipecah & Full English ---
            $table->double('bpjs_health')->default(0);
            $table->double('bpjs_jht')->default(0);
            $table->double('bpjs_jp')->default(0);
            // ---------------------------------------------------

            $table->double('pph21_deduction')->default(0);

            // Keterangan teks detail (Untuk Frontend)
            $table->json('calculation_details')->nullable();

            // Penyesuaian Manual
            $table->double('adjustment')->default(0);
            $table->text('adjustment_note')->nullable();

            // Grand Total
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->cascadeOnDelete();
            // Tambahkan unique constraint di bagian akhir Schema::create
            $table->unique(['employee_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
