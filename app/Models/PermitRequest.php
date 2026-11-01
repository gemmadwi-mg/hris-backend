<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermitRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permit_requests';

    protected $primaryKey = 'permit_request_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'permit_request_id',
        'employee_id',
        'permit_type',
        'start_date',
        'end_date',
        'description',
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

    // Relasi ke Karyawan yang mengajukan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    // Relasi ke HRD/Manager yang menyetujui
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'employee_id');
    }
}