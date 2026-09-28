<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('leaves', function (Blueprint $table) {
        $table->string('leave_type')->default('Tahunan')->after('employee_id'); // Sakit, Tahunan, Penting
        $table->string('document_path')->nullable()->after('reason');
    });
}

public function down(): void
{
    Schema::table('leaves', function (Blueprint $table) {
        $table->dropColumn(['leave_type', 'document_path']);
    });
}
};
