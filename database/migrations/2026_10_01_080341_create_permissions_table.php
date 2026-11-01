<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tabel Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->string('permission_id', 5)->primary();
            $table->string('name', 100);
            $table->string('guard_name', 50)->default('web');
            $table->timestamps();
        });

        // 2. Tabel Relasi Role & Permission (Pivot)
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->string('role_id', 5);
            $table->string('permission_id', 5);

            // Foreign Keys
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('permission_id')->on('permissions')->onDelete('cascade');
            
            // Primary Key Gabungan (agar tidak ada duplikat)
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};