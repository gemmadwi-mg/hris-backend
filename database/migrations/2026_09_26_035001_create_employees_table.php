<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            // Primary Key
            $table->string('employee_id', 5)->primary();
            
            // Foreign Keys
            $table->string('role_id', 5)->nullable(); 
            $table->string('company_id', 5);
            $table->string('branch_id', 5)->nullable();
            $table->string('ptkp_group_id', 3)->nullable();

            // Data Pribadi
            $table->string('nik', 20)->unique();
            $table->string('full_name', 50);
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->text('address');
            $table->string('phone_number', 20)->nullable();
            
            // Autentikasi
            $table->string('email', 50)->unique();
            $table->string('password', 100);
            $table->rememberToken();
            
            // Data Pekerjaan
            $table->string('job_title', 30)->nullable();
            
            // <--- BARU: Langsung pakai nama departemen
            $table->string('department', 50)->nullable(); 
            
            $table->enum('employment_status', [
                'Permanent',
                'Contract',
                'Internship',
                'Freelance'
            ])->nullable();
            $table->date('join_date')->nullable();
            $table->integer('annual_leave_quota')->default(12);
            
            // Data Gaji & Tunjangan Bulanan Fix
            $table->double('basic_salary')->nullable();
            $table->double('position_allowance')->default(0);
            $table->double('meal_allowance')->default(0);
            $table->double('transport_allowance')->default(0);
            
            // Data Bank & Pencairan
            $table->string('bank_name', 50)->nullable(); 
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder_name', 50)->nullable();
            
            // Data Tambahan
            $table->string('bpjs_health_number', 50)->nullable();
            $table->text('face_embedding')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Definisi Constraint Relasi (Tidak ada relasi ke departemen)
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('set null');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('set null');
            $table->foreign('ptkp_group_id')->references('ptkp_group_id')->on('ptkp_groups')->onDelete('set null');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('employees');
    }
};