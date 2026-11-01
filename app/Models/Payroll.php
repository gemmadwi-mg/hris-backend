<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'payroll_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'payroll_id', 'period_month', 'period_year', 'status', 'rejection_note',
        'employee_id', 'is_transferred', 'basic_salary',

        // Nama baru
        'position_allowance', 'meal_allowance', 'transport_allowance',

        'total_overtime', 'gross_salary', 'absence_deduction', 'late_deduction',

        // Nama baru
        'bpjs_health', 'bpjs_jht', 'bpjs_jp',

        'pph21_deduction', 'calculation_details',
        'adjustment', 'adjustment_note', 'total_deductions', 'net_salary',
    ];

    protected $casts = [
        'calculation_details' => 'array',
        'is_transferred' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
