<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendances';

    protected $primaryKey = 'attendance_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'attendance_id',
        'employee_id',
        'branch_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'clock_in_status',
        'clock_out_status',
        'late_duration',
        'early_leave_duration',
        'clock_in_coordinates',
        'clock_out_coordinates',
        'clock_in_photo',
        'clock_out_photo',
        'attendance_status',
    ];

    // Cast tipe data tanggal & waktu ke Carbon instance
    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    // Relasi ke Karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Relasi ke Cabang
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}