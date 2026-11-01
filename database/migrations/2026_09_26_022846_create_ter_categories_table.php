<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ter_categories', function (Blueprint $table) {
            // Primary key kustom (T01, T02, dst.)
            $table->string('ter_category_id', 3)->primary();
            $table->string('category_name', 20);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_categories');
    }
};