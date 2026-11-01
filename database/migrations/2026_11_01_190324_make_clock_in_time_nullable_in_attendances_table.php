<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Mengubah kolom jam masuk dan jam keluar agar boleh bernilai NULL
            $table->time('clock_in_time')->nullable()->change();
            $table->time('clock_out_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('clock_in_time')->nullable(false)->change();
            $table->time('clock_out_time')->nullable(false)->change();
        });
    }
};