<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    protected $primaryKey = 'leave_request_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'leave_request_id',
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
        'rejection_reason',
        'approver_id',
        'processed_at',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'processed_at' => 'datetime',
    ];

    // Accessor otomatis untuk menghitung total hari kerja (Appends ke JSON response)
    protected $appends = ['total_days'];

    public function getTotalDaysAttribute()
    {
        if (!$this->start_date || !$this->end_date) return 0;
        
        $start = Carbon::parse($this->start_date);
        $end   = Carbon::parse($this->end_date);
        
        // Hitung selisih hari inklusif
        return $start->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend(); // Hanya hitung hari kerja
        }, $end->addDay());
    }

    // Relasi ke Karyawan yang mengajukan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Relasi ke Approver (HR/Manager)
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'employee_id');
    }
}