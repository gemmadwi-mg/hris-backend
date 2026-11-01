<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'overtime_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'overtime_id', 'employee_id', 'attendance_id', 'overtime_date', 
        'duration', 'total_incentive', 'task_description', 'attachment', 
        'status', 'rejection_reason', 'approver_id', 'processed_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'attendance_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'employee_id');
    }
}