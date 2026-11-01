<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Ubah kolom presensi fisik menjadi nullable
            $table->string('clock_in_coordinates')->nullable()->change();
            $table->string('clock_out_coordinates')->nullable()->change();
            $table->string('clock_in_photo')->nullable()->change();
            $table->string('clock_out_photo')->nullable()->change();
            $table->time('clock_in_time')->nullable()->change();
            $table->time('clock_out_time')->nullable()->change();
            
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('clock_in_coordinates')->nullable(false)->change();
            $table->string('clock_out_coordinates')->nullable(false)->change();
            $table->string('clock_in_photo')->nullable(false)->change();
            $table->string('clock_out_photo')->nullable(false)->change();
            $table->time('clock_in_time')->nullable(false)->change();
            $table->time('clock_out_time')->nullable(false)->change();
        });
    }
};