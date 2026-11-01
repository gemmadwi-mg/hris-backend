<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->string('branch_id', 5)->primary();
            $table->string('company_id', 5);
            
            $table->string('branch_name', 50);
            $table->string('address'); // Default 255 karakter
            
            // Presisi (10, 8) & (11, 8) adalah standar terbaik untuk koordinat GPS di MySQL
            $table->double('latitude', 10, 8);
            $table->double('longitude', 11, 8);
            $table->integer('radius_meters')->comment('Dalam satuan meter (contoh: 50)');
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};