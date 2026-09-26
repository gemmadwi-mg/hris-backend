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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained()->restrictOnDelete();

            $table->integer('bulan'); // 1-12
            $table->integer('tahun');

            // Komponen Gaji
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0); // misal: BPJS, PPh21, Keterlambatan
            $table->decimal('total_gaji_bersih', 15, 2);

            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('tanggal_pembayaran')->nullable();

            $table->timestamps();
            // Tidak perlu softDeletes di sini, biasanya data payroll bersifat immutable (tidak bisa diubah/dihapus jika sudah 'paid')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
