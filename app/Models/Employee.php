<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable implements CanResetPasswordContract
{
    use CanResetPassword, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'employees';

    // Konfigurasi Primary Key Custom String
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * 🛡️ MENGGUNAKAN $fillable SEBAGAI BENTENG KEAMANAN
     * Kolom di luar daftar ini (seperti typo 'departement') akan diabaikan secara otomatis
     * dan tidak akan menyebabkan MySQL crash dengan error 'Column not found'.
     */
    protected $fillable = [
        'employee_id',
        'full_name',
        'nik',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'phone_number',
        'address',
        'job_title',
        'department', // 👈 Nama kolom resmi di database MySQL
        'employment_status',
        'join_date',
        'annual_leave_quota',
        'role_id',
        'company_id',
        'branch_id',
        'basic_salary',
        'position_allowance',
        'meal_allowance',
        'transport_allowance',
        'bank_name',
        'account_number',
        'account_holder_name',
        'ptkp_group_id',
        'bpjs_health_number',
    ];

    /**
     * 🔒 SEMBUNYIKAN FIELD SENSITIF
     * Mencegah password ikut terkirim dalam JSON response ke Vue
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ⚙️ CASTING TIPE DATA
     */
    protected $casts = [
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
    ];

    /**
     * RELASI DATABASE
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'employee_id');
    }

    /**
     * FUNGSI CUSTOM RBAC (ROLE & PERMISSION)
     */
    public function hasPermission($permissionName)
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->permissions->contains('name', $permissionName);
    }

    public function hasRole($roleName)
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->role_name === $roleName;
    }
}