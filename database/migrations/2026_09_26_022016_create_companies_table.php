<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            // Identitas Dasar
            $table->string('company_id', 5)->primary();
            $table->string('company_name', 50);
            $table->string('email', 50)->unique();
            $table->string('phone_number', 20);
            $table->time('clock_in_time');
            $table->time('clock_out_time');

            // Kebijakan Keterlambatan
            $table->double('late_penalty')->default(50000);

            // Konfigurasi Potongan BPJS
            $table->double('bpjs_health_percent')->default(0.0100)->comment('Default 1%');
            $table->double('bpjs_jht_percent')->default(0.0200)->comment('Default 2%');
            $table->double('bpjs_jp_percent')->default(0.0100)->comment('Default 1%');
            
            // Batas Upah (Ceiling/Limit) BPJS
            $table->bigInteger('bpjs_health_salary_limit')->default(12000000);
            $table->bigInteger('bpjs_jp_salary_limit')->default(10547400);


            // Tambahkan baris ini:
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};