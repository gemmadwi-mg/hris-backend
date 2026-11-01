<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'leave_type', 'year', 'quota', 'used'];

    // Accessor sisa cuti otomatis (quota - used)
    protected $appends = ['remaining'];

    public function getRemainingAttribute()
    {
        return max(0, $this->quota - $this->used);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}